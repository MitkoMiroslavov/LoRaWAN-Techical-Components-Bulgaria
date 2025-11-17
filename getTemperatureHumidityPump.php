<?php
// Database connection details
$host = 'localhost';
$dbname = 'loRaDataDB';
$username = 'root';
$password = '#@Pr0ducti0n#123*Kris';

try {
    // Connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch the latest temperature and humidity data
    $tempHumidityStmt = $pdo->query("SELECT temperature AS lastTemp, humidity AS lastHumidity, received_at AS tempHumidityUpdated FROM DHT_Data ORDER BY received_at DESC LIMIT 1");
    $tempHumidityRow = $tempHumidityStmt->fetch(PDO::FETCH_ASSOC);

    // Fetch the latest pump status data
    $pumpStatusStmt = $pdo->query("SELECT status AS pumpStatus, received_at AS statusUpdated FROM Microphone_Data ORDER BY received_at DESC LIMIT 1");
    $pumpStatusRow = $pumpStatusStmt->fetch(PDO::FETCH_ASSOC);

    // Calculate average temperature and humidity for the last 24 records
    $avgTempHumidityStmt = $pdo->query("SELECT AVG(temperature) AS avgTemp, AVG(humidity) AS avgHumidity FROM (SELECT temperature, humidity FROM DHT_Data ORDER BY received_at DESC LIMIT 12) AS recentData");
    
    $avgTempHumidityRow = $avgTempHumidityStmt->fetch(PDO::FETCH_ASSOC);

    if ($tempHumidityRow && $pumpStatusRow && $avgTempHumidityRow) {
        // Return data as JSON
        echo json_encode([
            'lastTemp' => isset($tempHumidityRow['lastTemp']) ? (float)$tempHumidityRow['lastTemp'] : null,
            'lastHumidity' => isset($tempHumidityRow['lastHumidity']) ? (float)$tempHumidityRow['lastHumidity'] : null,
            'avgTemp' => isset($avgTempHumidityRow['avgTemp']) ? round((float)$avgTempHumidityRow['avgTemp'], 2) : null,
            'avgHumidity' => isset($avgTempHumidityRow['avgHumidity']) ? round((float)$avgTempHumidityRow['avgHumidity'], 2) : null,
            'pumpStatus' => isset($pumpStatusRow['pumpStatus']) ? (int)$pumpStatusRow['pumpStatus'] : null,
            'statusUpdated' => isset($pumpStatusRow['statusUpdated']) ? $pumpStatusRow['statusUpdated'] : null
        ]);
    } else {
        // No data found
        echo json_encode([
            'error' => 'No data found in one or more queries.'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Database connection failed: ' . $e->getMessage()
    ]);
}
?>


