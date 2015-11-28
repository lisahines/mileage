<?php
  error_reporting(E_ALL); 
  include( "./config.php" );
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
 echo "<pre>".print_r($_POST, true)."</pre>";
 if(isset ($_POST['newCarName'])){
    //get form values
    $new_car_name = $_POST['newCarName'];
    $q = "INSERT INTO cars (id, name) VALUES (0, '".$new_car_name."');";
    echo "<pre>".print_r($q, true)."</pre>";
     $result = mysqli_query($db, $q);
    $car_id = mysqli_insert_id($db);
	$new_url = "../.?car=".$car_id;
	header('Location: '.$new_url);
	//echo "<pre>".print_r($new_url, true)."</pre>";

 } 










?>
<!-- writes html -->
<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Mobile Milage App - Switch Vehicles</title>
<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
	<script>
		function clear(obj) {
			obj.value = '';
		}
	</script>
	<style>
		strong {
			 
		}
	</style>
    </head>
    <body><div class="container">
<?php
  echo "<h1>Switch Vehicles</h1>";
  if(!$_GET['car']) {
	echo "Please choose a car:";
  }
  else {
    $car_name_query = "SELECT name FROM cars WHERE cars.id=" . $_GET['car'] . ";";
    
    $car_name = mysqli_query($db, $car_name_query);
    // echo "<pre>".print_r($car_name, true)."</pre>";
    $car_name_array = mysqli_fetch_array($car_name, MYSQLI_ASSOC);
    //echo "<pre>".print_r($car_name_array, true)."</pre>";
    echo "<div>";
    if($car_name_array['name']) {
	echo "Current vehicle is <strong>".$car_name_array['name'].".</strong><br> ";
    }
   //don't print anything if an invalid car is selected 
    	echo "Switch to:<br>";
    
    	echo "</div>";
  }
  $car_list_query = "SELECT name, id FROM cars";
  $q = mysqli_query($db, $car_list_query);
  echo "<form method='post' action='' onchange='this.form.submit();'>";
  echo "<select class='form-control' name='carName'>";
  while($listy = mysqli_fetch_array($q, MYSQLI_ASSOC)){
	  echo "<option><a href='../?car=".$listy['id']."'>".$listy['name']."</a></option>";
   }
   echo "</select>";
   echo "</form>";
	echo "<div>";
	echo "<p><strong>or</strong></p>";
	echo "<div class='form-group form-inline'>";
	echo "<form action='' method='post'>";
      echo "<input type='text' name='newCarName' placeholder='New vehicle' /> ";
 
 echo "<input class='btn btn-primary' type='submit' value='Add vehicle'>";
	echo "</form>";
  echo "</div>";
 
/*
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
	echo "</div>"; //end column div 
	echo "<div class='col-md-4'>"; //start economy column
  $newestEntry = reset($history);
  echo "<p>Economy</p>\r\n";
  echo "<img src='" . HTTP_SITE_ROOT . "dynamicImages/economyMetric.php?draw=true&date=".date("dmy",strtotime($newestEntry['date_time']))."' alt='L/100km' />";

	echo "</div>"; //end column div 
	echo "<div class='col-md-4'>"; //start economy column
  echo "<p>Economy</p>\r\n";
  echo "<img src='" . HTTP_SITE_ROOT . "dynamicImages/economyImperial.php?draw=true&date=".date("dmy",strtotime($newestEntry['date_time']))."' alt='mpg' />";
	echo "</div>"; //end column div 
	echo "<div class='col-md-4'>"; //start economy column
  echo "<p>Odometer</p>\r\n";
  echo "<img src='" . HTTP_SITE_ROOT . "dynamicImages/odometer.php?draw=true&date=".date("dmy",strtotime($newestEntry['date_time']))."' alt='odometer' />";
  //echo "<pre style='text-align:left'>".print_r($history,true)."</pre>";

*/






//echo "</div>";//end .column div
//echo "</div>";//end .row div
echo "</div>";//end .container


?>
    </body>
</html>
<?php
mysqli_close($db);
?>
