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

// Check current user's role
$currentUser = $_SESSION['username'];
$roleStmt = $conn->prepare("
    SELECT r.role_name 
    FROM users u
    JOIN roles r ON u.role_id = r.id
    WHERE u.username = ?
");
$roleStmt->bind_param("s", $currentUser);
$roleStmt->execute();
$roleStmt->bind_result($currentRole);
$roleStmt->fetch();
$roleStmt->close();

if ($currentRole !== 'admin') {
    $conn->close();
    echo "<script>alert('Access denied: only admins can delete users!'); window.location.href = 'dashboard.php';</script>";
    exit;
}


// Only proceed if ID is valid
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
    if (strtolower($userToDelete) === strtolower($currentUser)) {
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
