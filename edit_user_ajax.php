<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (
    !isset($data['id'], $data['field'], $data['value']) ||
    !in_array($data['field'], ['username', 'email'])
) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

include 'config.php';
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$id = (int)$data['id'];
$field = $data['field'];
$value = trim($data['value']);

if ($field === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid email address']);
    exit;
}
if ($field === 'username' && $value === '') {
    echo json_encode(['success' => false, 'error' => 'Username cannot be empty']);
    exit;
}

$check = $conn->prepare("SELECT id FROM users WHERE $field = ? AND id != ?");
$check->bind_param("si", $value, $id);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => ucfirst($field) . ' already in use']);
    $check->close();
    exit;
}
$check->close();

$stmt = $conn->prepare("UPDATE users SET $field = ? WHERE id = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'SQL error']);
    exit;
}

$stmt->bind_param("si", $value, $id);
$success = $stmt->execute();
$stmt->close();

echo json_encode(['success' => $success]);
exit;
?>
