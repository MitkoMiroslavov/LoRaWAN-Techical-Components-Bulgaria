<?php 
$servername = "localhost"; 
$username = "root";    
$password = "#@Pr0ducti0n#123*Kris";    
$dbname = "loRaDataDB"; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get data from POST request
if (isset($_POST['data'])) {
    $data = $_POST['data'];

    if (strpos($data, ',') !== false) {
        // Data is comma-separated; split and process
        $parts = explode(',', $data);
        if (count($parts) === 3) {
            $temperature = $conn->real_escape_string(trim($parts[0]));
            $humidity = $conn->real_escape_string(trim($parts[1]));
            $sound_state = $conn->real_escape_string(trim($parts[2]));

            // Insert temperature and humidity into `sensor_data` table
            $sql_sensor = "INSERT INTO DHT_Data (temperature, humidity) 
                           VALUES ('$temperature', '$humidity')";

            if ($conn->query($sql_sensor) === TRUE) {
                echo "Sensor data inserted successfully.";
            } else {
                echo "Error: " . $sql_sensor . "<br>" . $conn->error;
            }

            // Insert sound state into `microphone_data` table
            $sql_microphone = "INSERT INTO Microphone_Data (status) 
                               VALUES ('$sound_state')";

            if ($conn->query($sql_microphone) === TRUE) {
                echo "Microphone data inserted successfully.";
            } else {
                echo "Error: " . $sql_microphone . "<br>" . $conn->error;
            }
        } else {
            echo "Invalid data format.";
        }
    } else {
        // Data is a single value (distance)
        $distance = $conn->real_escape_string(trim($data));

        // Insert distance into `sensor_data` table
        $sql_distance = "INSERT INTO sensor_data (data) VALUES ('$distance')";

        if ($conn->query($sql_distance) === TRUE) {
            echo "Distance data inserted successfully.";
        } else {
            echo "Error: " . $sql_distance . "<br>" . $conn->error;
        }
    }
} else {
    echo "No data received.";
}

$conn->close();
?>
