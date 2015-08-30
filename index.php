<?php
  include( "./config.php" );
  
   //checking for valid data?
   $checker = array(
    'odometer'=>array(
      0=>array('regex'=>'/(^\d+\.?\d*$)|(^\.\d+$)/','error'=>'Odometer must be a number')
    ),
    'volume'=>array(
      0=>array('regex'=>'/(^\d+\.?\d*$)|(^\.\d+$)/','error'=>'Volume must be a number')
    ),
    'price'=>array(
      0=>array('regex'=>'/(^\d+\.?\d*$)|(^\.\d+$)/','error'=>'Price must be a number')
    )
  );
   //connecting to database - are constants from config.php?
  $db = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD);
  if(!$db){
    die("Could not connect to Database");
  }else{
    $db_selected = mysqli_select_db($db,DB_NAME);
  }
  if(!$db_selected){
    die("Could not select Database");
  }
  //insert new entries
  if(isset ($_POST['form_submit'])){
    //get form values
    $default_odometer = $_POST['odometer'];
    $default_volume = $_POST['volume'];
    $default_price = $_POST['price'];
    //check for errors
    $errors = array();
    foreach ($checker as $field => $checks){
      foreach ($checks as $check){
        if(!preg_match($check['regex'],$_POST[$field])){
          $errors[]=$check['error'];
        }
      }
    }
    if($errors){
      echo "<p style='color:red;'>\r\n";
      foreach($errors as $error){
        echo "* ".$error."<br/>\r\n";
      }
      echo "</p>\r\n";
    }else{
      $q = "INSERT INTO fills (id, date_time, odometer, volume, price) VALUES "
        ."(0, NOW(), ".$_POST['odometer'].", ".$_POST['volume'].", ".$_POST['price'].")";
      $result = mysqli_query($db, $q);
      if($result){
        echo "<p>Successful Update</p>";
      }else{
        echo "<p>Failed Insert</p>";
      }
    }
  }
  //load the historical data
  $q = "SELECT * FROM fills ORDER BY date_time DESC";
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
  //set the array of the most recent entry
  $current_stats = reset($history);
  //set defaults if not posted
  if(!isset($_POST['form_submit'])){
    $default_odometer = (string)(intval($current_stats['odometer'])+500);
    $default_odometer = substr($default_odometer,0,count($default_odometer)-4);
    $default_volume = "5";
    $default_price = "";
  }
  $current_stats = reset($history);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Mobile Milage App</title>
    </head>
    <body>
<?php
  echo "<h1>Mileage</h1>\r\n";
  echo "<p>"
    .sprintf("%0.2f",$current_stats['metric'])." L/100km<br/>"
    .sprintf("%0.2f",$current_stats['imperial'])." mpg<br/>"
    .sprintf("%0.3f",$current_stats['unit_cost'])." \$/km<br/>"
    .sprintf("\$%0.2f",$current_stats['fuel_cost'])."/L<br/>"
    ."As of ".date("l, F j, Y",strtotime($current_stats['date_time']))."<br/>"
    .$current_stats['odometer']." km<br/>"
    ."</p>";
  echo "<form action='' method='post'>\r\n";
    echo "<table>\r\n";
    echo "\t<tr>\r\n";
      echo "\t\t<td>Odometer:</td><td><input type='text' name='odometer' value='$default_odometer'/></td>\r\n";
    echo "\t</tr><tr>\r\n";
      echo "\t\t<td>Volume:</td><td><input type='text' name='volume' value='$default_volume'/></td>\r\n";
    echo "\t</tr><tr>\r\n";
      echo "\t\t<td>Price:</td><td><input type='text' name='price' value='$default_price'/></td>\r\n";
    echo "\t</tr><tr>\r\n";
      echo "\t\t<td></td><td><input type='submit' name='form_submit' value='Store Entry'/></td>\r\n";
    echo "\t</tr>\r\n";
    echo "</table>";
  echo "</form>\r\n";

  if(false && isset($history)){
    echo "<table>\r\n";
    echo "\t<tr>\r\n";
      echo "\t\t<th>Date</th><th>Odometer</th><th>Volume</th><th>Price</th><th>L/100km</th><th>mpg</th><th>\$/km</th><th>\$/L</th><th>km</th>\r\n";
    echo "\t</tr>\r\n";

    foreach($history as $entry){
      echo "\t<tr>\r\n";
        echo "\t\t<td>"
          .date("M j",strtotime($entry['date_time']))."</td><td>"
          .$entry['odometer']."</td><td>"
          .$entry['volume']."</td><td>"
          .$entry['price']."</td><td>"
          .sprintf("%0.2f",$entry['metric'])."</td><td>"
          .sprintf("%0.2f",$entry['imperial'])."</td><td>"
          .sprintf("%0.3f",$entry['unit_cost'])."</td><td>"
          .sprintf("%0.2f",$entry['fuel_cost'])."</td><td>"
          .$entry['distance']."</td>\r\n";
      echo "\t</tr>\r\n";
    }
  }
  $newestEntry = reset($history);
  echo "<p>Economy</p>\r\n";
  echo "<img src='" . HTTP_SITE_ROOT . "dynamicImages/economyMetric.php?draw=true&date=".date("dmy",strtotime($newestEntry['date_time']))."' alt='L/100km' />";
  echo "<p>Economy</p>\r\n";
  echo "<img src='" . HTTP_SITE_ROOT . "dynamicImages/economyImperial.php?draw=true&date=".date("dmy",strtotime($newestEntry['date_time']))."' alt='mpg' />";
  echo "<p>Odometer</p>\r\n";
  echo "<img src='" . HTTP_SITE_ROOT . "dynamicImages/odometer.php?draw=true&date=".date("dmy",strtotime($newestEntry['date_time']))."' alt='odometer' />";
  //echo "<pre style='text-align:left'>".print_r($history,true)."</pre>";












?>
    </body>
</html>
<?php
mysqli_close($db);
?>
