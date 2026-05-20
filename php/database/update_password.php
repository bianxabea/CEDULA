<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$user_id = $_SESSION['user']['id'];
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';

if (!$current_password || !$new_password) {
    echo json_encode(['success' => false, 'error' => 'All fields required']);
    exit;
}

// Verify current password
$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param('s', $user_id);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    if (!password_verify($current_password, $row['password'])) {
        echo json_encode(['success' => false, 'error' => 'Current password incorrect']);
        exit;
    }
}
else {
    echo json_encode(['success' => false, 'error' => 'User not found']);
    exit;
}
$stmt->close();

// Update password
$hash = password_hash($new_password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->bind_param('ss', $hash, $user_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
}
else {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
$stmt->close();
$conn->close();
?>
