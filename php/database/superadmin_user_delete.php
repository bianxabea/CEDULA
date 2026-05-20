<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('superadmin');

$user_id = trim($_POST['user_id'] ?? '');

if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

// Prevent deleting self
if ($user_id === $_SESSION['user']['id']) {
    echo json_encode(['success' => false, 'error' => 'Cannot delete yourself']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param('s', $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
}
else {
    echo json_encode(['success' => false, 'error' => 'Database error or user has active dependencies']);
}
$stmt->close();
$conn->close();
?>
