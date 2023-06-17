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
