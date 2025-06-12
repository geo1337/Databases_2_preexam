<?php
include 'config.php';
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    exit("DB connection error");
}

// Query 1: Login success/failure count
$loginResult = $conn->query("
    SELECT success, COUNT(*) as count
    FROM login_tracking_table
    GROUP BY success
");

$loginData = [ 'success' => 0, 'failure' => 0 ];
while ($row = $loginResult->fetch_assoc()) {
    if ($row['success'] == 1) {
        $loginData['success'] = (int)$row['count'];
    } else {
        $loginData['failure'] = (int)$row['count'];
    }
}

// Query 2: Device types
$deviceResult = $conn->query("
    SELECT device_type, COUNT(*) as count
    FROM login_tracking_table
    GROUP BY device_type
");

$deviceData = [];
while ($row = $deviceResult->fetch_assoc()) {
    $deviceData[$row['device_type']] = (int)$row['count'];
}

echo json_encode([
    'logins' => $loginData,
    'devices' => $deviceData
]);
?>
