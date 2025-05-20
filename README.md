# sim808 gps tracker and display it on your own website
In this project we use sim808 and arduino uno to build a gps tracker that user can see the tracking log on a website.
Sim808 will send locations as Longitude and latitude to php scripct.

The website part was done by php, html and javascripct.

In following i will show you how to do the each part.

## arduino
For the hardware part i use arduino uno and [sim808-module](https://www.amazon.de/TECNOIOT-SIM808-Module-Development-Antenna/dp/B083M5VBQT) , this also works on other arduino board but you need to make some changes in the code and wiring.
You can power up the sim808 with 5-26V, 2A, 5.5mm adapter. also you can use 3.7 18650 li-lon battery.
I personally use 20000 powerbank with 5V, 2.4A output, for both arduino uno and sim808 module.
### wiring
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/Reza-shojaei/sim808_gps_tracker/main/arduino%20codes/wiring/SIM808-Module-circuit3.jpg">
  <source media="(prefers-color-scheme: light)" srcset="https://raw.githubusercontent.com/Reza-shojaei/sim808_gps_tracker/main/arduino%20codes/wiring/SIM808-Module-circuit3.jpg">
  <img alt="Shows an illustrated sun in light mode and a moon with stars in dark mode." src="https://raw.githubusercontent.com/Reza-shojaei/sim808_gps_tracker/main/arduino%20codes/wiring/SIM808-Module-circuit3.jpg">
</picture>

I upload two arduino code, That both work fine and both of them can send the location to php secript every 10S feel free to use either of them but here is one example.
### arduino code 1
```
#include<SoftwareSerial.h> 
#include <DFRobot_SIM808.h>
// define rx and tx pin
#define rxPin 11   
#define txPin 10

 SoftwareSerial mySerial(txPin, rxPin);
 DFRobot_SIM808 sim808(&mySerial);
 // create char to store our lat and lng
 char wlat[12];
 char wlon[12];
 
void setup(){
mySerial.begin(9600);
Serial.begin(9600); 
Serial.println("Starting...");
 while(!sim808.init()) {
      delay(1000);
      Serial.print("Sim808 init error\r\n");
  }

  //************* Turn on the GPS power************

if( sim808.attachGPS())
{
  Serial.println("Open the GPS power success");
}
else 
{
    Serial.println("Open the GPS power failure");

}
}
 void loop(){
   // if we get latitude and longitude we goes to the next line 
if (sim808.getGPS()) {     
   // showing latitude and longitude softwareserial
   Serial.print("latitude :");
   Serial.println(sim808.GPSdata.lat,6);

  Serial.print("longitude :");
  Serial.println(sim808.GPSdata.lon,6);

  // store latitude and longitude on float called la and lo
    float la = sim808.GPSdata.lat;
     float lo = sim808.GPSdata.lon;
     delay(1000);

     //goes to void SubmitHttpRequest

     SubmitHttpRequest(la,lo);
       }
  }

 void SubmitHttpRequest(float la,float lo)
 {
  // convert float to string like : dtostrf(floatValue, minStringWidth, numAfterDecimal, charBuf_to_store_string)
  dtostrf( la,6, 6, wlat); 
  dtostrf( lo,6, 6, wlon); 
  //sending AT command to start connecting internet 
   Serial.println("AT");
delay(100);
 ShowSerialData();
    // set our connection type to GPRS
 mySerial.println("AT+SAPBR=3,1,\"CONTYPE\",\"GPRS\"");
 delay(500);
  ShowSerialData();
    // define our simcard APN name
 mySerial.println("AT+SAPBR=3,1,\"APN\",\"your apn service\"");
 delay(500);
  ShowSerialData();
    // START gprs connection
 mySerial.println("AT+SAPBR=1,1");
 delay(2500);
 ShowSerialData();
//  start HTTP  mode 
mySerial.println("AT+HTTPINIT"); 
delay(100); 
 ShowSerialData();
//ready for HTTP connection
mySerial.println("AT+HTTPPARA=\"CID\",1");
delay(500); 
 ShowSerialData();
// send HTTP post request to our PHP script in our web service Along with lat and lng
mySerial.print("AT+HTTPPARA=\"URL\",\"http://your web site .com/gpsdata.php?");
mySerial.print("&lat=");  // This is the value name
mySerial.print(wlat); // value of...
mySerial.print("&lng=");  // This is the value name
mySerial.print(wlon); // value of...
 // close that script
 mySerial.println("\"");
 delay(500);
 ShowSerialData();
//send HTTP POST request
mySerial.println("AT+HTTPACTION=1");//submit the request 
delay(2000);
 ShowSerialData();

mySerial.println("");
delay(100);
 }
  void ShowSerialData()
 {
   while(mySerial.available()!=0)
      {
        Serial.write(mySerial.read());
     }

  } 

```
you can also read more about [DFRobot_sim808 library](https://github.com/DFRobot/DFRobot_SIM808)


## webpage
for webpage i use three code, one for connecting to database, one for receiving data from arduino and last one for display location on map.

### connecting to database
```
<?php 
define('DB_HOST', ''); 
define('DB_USERNAME', ''); 
define('DB_PASSWORD', ''); 
define('DB_NAME', '');

date_default_timezone_set('Asia/Tehran');

// Connect with the database 
$db = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME); 
 
// Display error if failed to connect 
if ($db->connect_errno) { 
    echo "Connection to database is failed: ".$db->connect_error;
    exit();
}
```

### receiving data from arduino

```
<?php 

require 'config.php';

$lat = $_GET['lat'];
$lng = $_GET['lng'];

echo $lat;
echo "<br>";
echo $lng;


$sql = "INSERT INTO your_table_name (lat,lng,created_date) 
	VALUES('".$lat."','".$lng."','".date("Y-m-d H:i:s")."')";

if($db->query($sql) === FALSE)
	{ echo "Error: " . $sql . "<br>" . $db->error; }

echo "<br>";
echo $db->insert_id;
```

### display location on map
some of the features in this page is that you can see your tracking history by filtering date and time also you can use time step to change the interval between reading each data from database, with this feature you see data every 1 minute or 5 minute insted of every 10 second
#### example of tracking page

<picture>
  <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/Reza-shojaei/sim808_gps_tracker/main/website%20codes/Captureexample.PNG">
  <source media="(prefers-color-scheme: light)" srcset="https://raw.githubusercontent.com/Reza-shojaei/sim808_gps_tracker/main/website%20codes/Captureexample.PNG">
  <img alt="Shows an illustrated sun in light mode and a moon with stars in dark mode." src="https://raw.githubusercontent.com/Reza-shojaei/sim808_gps_tracker/main/website%20codes/Captureexample.PNG">
	
  <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/Reza-shojaei/sim808_gps_tracker/main/website%20codes/Picture1.png">
  <source media="(prefers-color-scheme: light)" srcset="https://raw.githubusercontent.com/Reza-shojaei/sim808_gps_tracker/main/website%20codes/Picture1.png">
  <img alt="Description of the image" src="https://raw.githubusercontent.com/Reza-shojaei/sim808_gps_tracker/main/website%20codes/Picture1.png">

</picture>

 

#### tracking page code

```
<?php
// Set the default value to 10s
$selected_value = "1";

// Check if the form has been submitted and update the selected value accordingly
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selected_value = $_POST["factor"];
    $_SESSION["selected_value"] = $selected_value;
} elseif (isset($_SESSION["selected_value"])) {
    $selected_value = $_SESSION["selected_value"];
}             

// Include the config file with database credentials
require_once('config.php');

// Get the start and end times from the form if they were submitted
$start_time = isset($_POST['start_time']) ? $_POST['start_time'] : null;
$end_time = isset($_POST['end_time']) ? $_POST['end_time'] : null;

// Set default dividing factor


// Select GPS locations within the specified time range from the database
if ($start_time && $end_time) {
    $sql = "SELECT * FROM your_table_name WHERE created_date BETWEEN '$start_time' AND '$end_time' AND id % $selected_value = 0 ORDER BY created_date DESC LIMIT 50";
} else {
    $sql = "SELECT * FROM your_table_name ORDER BY created_date DESC LIMIT 1";
}
$result = $result = $db->query($sql);

// Check for errors
if (!$result) { 
    	die("Error executing query: " . mysqli_error($db));
	}

// Fetch all the rows as an associative array
$rows = $result->fetch_all(MYSQLI_ASSOC);

// Close the database connection
$db->close();

 
// Create an array to hold all the location coordinates
$coordinates = array();

// Get the first and last location
$first_location = end($rows);
$last_location = reset($rows);

// Loop through each location and add its coordinates to the coordinates array
foreach ($rows as $location) {
$coordinates[] = [$location['lat'], $location['lng']];
}

// Create a JavaScript variable to hold the coordinates array
$js_coordinates = json_encode($coordinates);
//echo $js_coordinates;
$midmap = json_decode($js_coordinates, true);

if (empty($midmap)) {
    echo "<div style='color: red; font-weight: bold; text-align: center; margin-top: 25px; font-size: 24px;'>There is no data for the specified date and time</div>";

} else {
    $middleIndex = (int)(count($midmap) / 2);
    if (count($midmap) % 2 == 0) {
        $middleIndex -= 1; // choose the element before the middle
    }
    $middleData = $midmap[$middleIndex];
    $string = implode(',', $middleData);
    echo $string;
}



//if (empty($middleData)){
  //  echo "There isn't any data in that date and time" ;
//}
//else {

//}
?>
<!DOCTYPE html>
<html>
<head>
    <title>your tracking history</title>
    <!-- Include Leaflet CSS and JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <!-- Set the size of the map container -->
    <style type="text/css">
            h1 {
          font-size: 28px;
          font-weight: bold;
          text-align: center;
          margin-bottom: 20px;
          text-transform: uppercase;
          color: #4286f4;
        }
        #mapid { height: 510px; }
        .card {
          margin: 0 auto;
          margin-top: 40px;
          padding: 20px;
          box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.2);
          border-radius: 10px;
          width: 80%;
          max-width: 1400px;
          background-color: #bab4c5;
          text-align: center;
        }
        
        form {
            padding: 20px;
            margin-bottom: 20px;
            background-color: #4286f4;
            border: 2px solid #ddd;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        label {
            font-family: Arial, sans-serif;
            font-size: 16px;
            margin-right: 10px;
            text-align: center;
        }

        input[type="datetime-local"], button {
            font-family: Arial, sans-serif;
            font-size: 16px;
            padding: 5px 10px;
            border: none;
            background-color: #fff;
            border: 2px solid #ddd;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        button {
            background-color: #4CAF50;
            color: #fff;
            cursor: pointer;
        }

        button:hover {
            background-color: #3e8e41;
        }
        
        body {
        background-color: #736191; /* replace with your desired background color */
    }
    </style>
</head>
<body>
     <div class="card">
        <div class="card-body">
    <h1>Here you can see your tracking history by selecting start and end points</h1>
    <!-- Create a form to select start and end times -->
        	<form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
		<label for="factor">Select time step:</label>
		<select name="factor" id="factor">
                        <option value="1"<?php if ($selected_value == "1") echo " selected"; ?>>10s</option>
                        <option value="6"<?php if ($selected_value == "6") echo " selected"; ?>>1min</option>
                        <option value="30"<?php if ($selected_value == "30") echo " selected"; ?>>5min</option>
		</select>
        <label for="start_time">Start Time:</label>
        <input type="datetime-local" id="start_time" name="start_time" value="<?php echo $start_time; ?>">
        <label for="end_time">End Time:</label>
        <input type="datetime-local" id="end_time" name="end_time" value="<?php echo $end_time; ?>">
        <button type="submit">Filter</button>
        
    </form>
    <br>
    <!-- Create a container for the map with a specified ID -->
    <div id="mapid"></div>
    <script type="text/javascript">

        var map = L.map('mapid').setView([<?php echo $string; ?>], 11.5);

        // Create a new TileLayer with the OpenStreetMap tile server URL
        var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(map);
            // Create a new Polyline with the location coordinates and add it to the map
    var polyline = L.polyline(<?php echo $js_coordinates; ?>, {color: 'red'}).addTo(map);

	// Loop through each location and add a marker to the map
	<?php foreach ($rows as $location): ?>
		var marker = L.marker([<?php echo $location['lat']; ?>, <?php echo $location['lng']; ?>]).addTo(map);
               marker.bindPopup("<?php echo $location['created_date']; ?>");
	<?php ?>
        
        
        // Add a label to the start point marker
    var startLabel = L.marker([<?php echo $first_location['lat']; ?>, <?php echo $first_location['lng']; ?>]).addTo(map);
    startLabel.bindPopup("Start: <?php echo date('Y-m-d H:i:s', strtotime($first_location['created_date'])); ?>");

    // Add a label to the end point marker
    var endLabel = L.marker([<?php echo $last_location['lat']; ?>, <?php echo $last_location['lng']; ?>]).addTo(map);
    endLabel.bindPopup("End: <?php echo date('Y-m-d H:i:s', strtotime($last_location['created_date'])); ?>");
    <?php endforeach; ?>
    
    
</script>
</body>
</html>
```


if you have any question you can contact me through [email](mailto:shojaeereza11@gmail.com) or [telegram](https://t.me/Shreza9)
shojaeereza11@gmail.com
