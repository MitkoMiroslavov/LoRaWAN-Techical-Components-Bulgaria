<?php
// Database connection details
$host = 'localhost';
$dbname = 'loRaDataDB';
$username = 'root';
$password = '#@Pr0ducti0n#123*Kris';

// Connect to the database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch the latest water level data
    $stmt = $pdo->query("SELECT data, received_at FROM sensor_data ORDER BY received_at DESC LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Return data as JSON
        echo json_encode([
            'waterLevel' => (int)$row['data'],
            'lastUpdated' => $row['received_at']
        ]);
    } else {
        // No data found
        echo json_encode([
            'error' => 'No data found'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Database connection failed: ' . $e->getMessage()
    ]);
}
?>
