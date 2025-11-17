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

    // Fetch the average water level per day for the last 7 days
    $stmt = $pdo->query(
        "SELECT DATE(received_at) as date, AVG(data) as avg_water_level 
         FROM sensor_data 
         WHERE received_at >= NOW() - INTERVAL 7 DAY 
         GROUP BY DATE(received_at) 
         ORDER BY date ASC"
    );
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($data);
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Database connection failed: ' . $e->getMessage()
    ]);
}
?>
