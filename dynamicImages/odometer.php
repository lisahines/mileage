<?php
include( "../config.php" );
$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD);
if(!$db){
  die("Could not connect to Database");
}else{
  $db_selected = mysqli_select_db($db,DB_NAME);
}
if(!$db_selected){
  die("Could not select Database");
}
$q = "SELECT * FROM fills WHERE date_time > '" ;
$q .= ( date( "Y" ) - 1 ) . date( "-m-j" ) . " 00:00:00' ORDER BY date_time DESC";
$result = mysqli_query($db, $q);
if ($result){
  $history=array();
  $i = 0;
  while($row_array = mysqli_fetch_array($result, MYSQLI_ASSOC)){
    $i++;
    $history[$row_array["id"]] = $row_array;
  }
}
foreach ($history as $key => $entry){
  if(isset($prev_key)){
    if( $history[$prev_key]['price'] < 5.0 ) {
        $history[$prev_key]['price'] = 
            $history[$prev_key]['price'] * $history[$prev_key]['volume'];
    }
    $distance = $old_odometer-$entry['odometer'];
    $history[$prev_key]['metric']=100*$history[$prev_key]['volume']/$distance;
    $history[$prev_key]['imperial']=235.21459/$history[$prev_key]['metric'];
    $history[$prev_key]['unit_cost']=$history[$prev_key]['price']/$distance;
    $history[$prev_key]['fuel_cost']=$history[$prev_key]['price']/$history[$prev_key]['volume'];
    $history[$prev_key]['distance']=$distance;
  }
  $old_odometer = $entry['odometer'];
  $prev_key = $key;
}
$history[$prev_key]['metric']='N/A';
$history[$prev_key]['imperial']='N/A';
$history[$prev_key]['unit_cost']="N/A";
$history[$prev_key]['distance']="N/A";
$history[$prev_key]['fuel_cost']=$history[$prev_key]['price']/$history[$prev_key]['volume'];

foreach( $history as $key => $entry ) {
  if( $entry['imperial'] > 35.0 | $entry['imperial'] < 15.0 ) {
      unset( $history[$key] );
  }
}

$x=array();
$y=array();
foreach($history as $entry){
  $y[]=$entry['odometer'];
  $x[]=strtotime($entry['date_time']);
}


if(!isset($_GET['draw'])){
  echo "<pre style='text-align:left'>".print_r($history,true)."</pre>";
}else{
  //set chart parameters
  $size=array('w'=>350,'h'=>325);//image size
  $m=array('l'=>45,'r'=>25,'t'=>30,'b'=>30);//margins
  $m['b']+=1;//account for line pixel shift
  $m['r']+=1;
  $or = array("x"=>$m['l'],"y"=>$size['h']-$m['b']);//origin
  $tr = array("x"=>$size['w']-$m['r'],"y"=>$m['t']);//top-right corner of graph
  $div=array('h'=>6,'v'=>6);
  $max_x_divisions = 6;
  $point_dia=6;
  //crate the image object
  $img = imagecreate($size['w'],$size['h']);
  //set the background color (the first color set is automatically set to the bg color
  $background = imagecolorallocate($img, 255, 255, 255);
  //grab a bunch of other colors we might need
  $black = imagecolorallocate($img, 0, 0, 0);
  $white = imagecolorallocate($img, 255, 255, 255);
  $red   = imagecolorallocate($img, 255, 0, 0);
  $green = imagecolorallocate($img, 0, 255, 0);
  $blue  = imagecolorallocate($img, 0, 0, 255);
  //draw a border
  imagerectangle($img, 0, 0, $size['w']-1, $size['h']-1, $black);
  //draw axis
  imageline ($img, $or["x"], $tr['y'], $or["x"],$or["y"],$black);//vertical
  imageline ($img, $or["x"], $or['y'], $tr["x"],$or["y"],$black);//horizontal
  //convert x-y to pixel points
  $large_num = 1e10;
  $bounds=array("min_x"=>$large_num, "max_x"=>-1*$large_num,"min_y"=>$large_num, "max_y"=>-1*$large_num);
  foreach ($x as $lx){
    if($lx>$bounds['max_x']){$bounds['max_x']=$lx;}
    if($lx<$bounds['min_x']){$bounds['min_x']=$lx;}
  }
  foreach ($y as $ly){
    if($ly>$bounds['max_y']){$bounds['max_y']=$ly;}
    if($ly<$bounds['min_y']){$bounds['min_y']=$ly;}
  }
  $x_range = $bounds['max_x']-$bounds['min_x'];
  $y_range = $bounds['max_y']-$bounds['min_y'];
  $buffer = 0.05;
  $day = 60*60*24; //number of seconds in a day
  $g_bounds = array(
    'max_x'=>$bounds['max_x']-( $bounds['max_x'] % $day ) + $day
   ,'min_x'=>$bounds['min_x']-( $bounds['min_x'] % $day )
   ,'max_y'=>$bounds['max_y']+$buffer*$y_range
   ,'min_y'=>$bounds['min_y']-$buffer*$y_range
  );
  $xg_range = $g_bounds['max_x']-$g_bounds['min_x'];
  $days = intval($xg_range/$day); //number of days in the range
  $yg_range = $g_bounds['max_y']-$g_bounds['min_y'];

  $px = array();
  $py = array();
  $ox = $g_bounds['min_x'];
  $oy = $g_bounds['min_y'];
  $x_scale = ($tr['x']-$or['x'])/$xg_range;
  $y_scale = ($or['y']-$tr['y'])/$yg_range;
  foreach ($x as $lx){
    $px[]=$or['x']+($lx-$ox)*$x_scale;
  }
  foreach ($y as $ly){
    $py[]=$or['y']-($ly-$oy)*$y_scale;
  }
  //draw dash marks
  //horizontal line spacing
  //number of seconds per division
  $gx_space = ceil($days/$max_x_divisions)*$day;
  //adjusted space for scale
  $space = $gx_space*$x_scale;
  $old_year = 0;
  for($i=0;$i<=$div['h'];$i++){
    $time_value = intval($g_bounds['min_x']+($i)*$gx_space);
    $year = date("Y", $time_value );
    $day = date("M j", $time_value );
    imageline ($img, $or['x']+$i*$space, $or['y'], $or['x']+$i*$space, $or['y']-3,$black);//horizontal
    imagestring(
        $img,
        2,
        $or['x'] + $i * $space - 20,
        $or['y'] + 2,
        $day,
        $black);
    if( $old_year !== $year ) {
        imagestring(
            $img
            ,2
            ,$or['x'] + $i * $space - 25
            ,$or['y'] + 12
            ,$year
            ,$black);
    }
    $old_year = $year;
  }
  
  //Y axis label
  $space = ($or['y']-$tr['y'])/$div['v'];//vertical tick mark spacing
  imagestring($img,2,5,5
       ,"km"
       ,$black);
  $old_first_part = "";
  for($i=0;$i<=$div['v'];$i++){
    $odometer_label = (string)intval($g_bounds['max_y']-($i)*$yg_range/$div['v']);
    $first_part = substr( $odometer_label, 0, 2 );
    $second_part = substr( $odometer_label, 2 );
    
    imageline ($img, $or['x'],$tr['y']+$i*$space, $or['x']+3,$tr['y']+$i*$space,$black);//horizontal
    imagestring( $img
        ,2
        ,$or['x'] - 25
        ,$tr['y'] + $i * $space - 8
        ,$second_part
        ,$black);
    if( $first_part !== $old_first_part ) {
        imagestring( $img
            ,2
            ,$or['x'] - 40
            ,$tr['y'] + $i * $space - 10
            ,$first_part
            ,$black);
    }
    $old_first_part = $first_part;
  }
  //echo "<pre style='text-align:left'>".print_r($px,true)."</pre>";
  //echo "<pre style='text-align:left'>".print_r($py,true)."</pre>";
  //Plot the points
  for($i=0;$i<count($px);$i++){
    if($i!=count($px)-1){
      imageline ($img, $px[$i+1],$py[$i+1], $px[$i],$py[$i],$black);//horizontal
    }
    imagefilledellipse($img, $px[$i], $py[$i],$point_dia, $point_dia, $black);
    //imagestring($img,2,$px[$i],$py[$i],"(".$x[$i].",".$y[$i].")",$black);
  }
  //ImageString($im,2,$x2,$y2,$nt[month],$graph_color);
  //imageline ($im,$x1, $y1,$x2,$y2,$text_color); // Drawing the line between two points
  //imagefilledrectangle($img, 20, 20, 60, 60, $red);
  //imagefilledellipse($img, 90, 40, 40, 40, $blue);
  //imagefilledellipse($img, 150, 40, 70, 40, $green);
  //imagefilledpolygon($img, $corners, 3, $white);

  header ("Content-type: image/png");
  imagepng($img);
  imagedestroy($img);
}
?>
