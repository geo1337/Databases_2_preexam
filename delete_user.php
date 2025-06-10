<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

include 'config.php';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);

    // Check if user exists
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($userToDelete);
    $stmt->fetch();
    $stmt->close();

    if (!$userToDelete) {
        $conn->close();
        header("Location: dashboard.php?error=not_found");
        exit;
    }

    // Prevent deleting yourself
    if (strtolower($userToDelete) === strtolower($_SESSION['username'])) {
        $conn->close();
        header("Location: dashboard.php?error=self_delete");
        exit;
    }

    // Proceed with deletion
    $deleteStmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $deleteStmt->bind_param("i", $id);
    if ($deleteStmt->execute()) {
        $deleteStmt->close();
        $conn->close();
        header("Location: dashboard.php?deleted=1");
        exit;
    } else {
        $deleteStmt->close();
        $conn->close();
        header("Location: dashboard.php?error=delete_failed");
        exit;
    }
} else {
    $conn->close();
    header("Location: dashboard.php?error=invalid_id");
    exit;
}
?>