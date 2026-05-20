<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

$target_id = trim($_POST['target_id'] ?? '');
$reason = trim($_POST['reason'] ?? '');
$request_type = trim($_POST['request_type'] ?? 'block');

if (!$target_id || !$reason) {
    echo json_encode(['success' => false, 'error' => 'Target ID and Reason required']);
    exit;
}

if (!in_array($request_type, ['block', 'unblock'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request type']);
    exit;
}

$requester_id = $_SESSION['user']['id'];

// Check for existing pending request for this target OF THE SAME TYPE
$check = $conn->prepare("SELECT id FROM user_block_requests WHERE target_id = ? AND status = 'pending' AND request_type = ?");
$check->bind_param('ss', $target_id, $request_type);
$check->execute();
$existing = $check->get_result();
if ($existing->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => "A pending $request_type request already exists for this user."]);
    $check->close();
    $conn->close();
    exit;
}
$check->close();

// Insert request
$stmt = $conn->prepare("INSERT INTO user_block_requests (requester_id, target_id, request_type, reason, status) VALUES (?, ?, ?, ?, 'pending')");
$stmt->bind_param('ssss', $requester_id, $target_id, $request_type, $reason);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
}
else {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
$stmt->close();
$conn->close();
?>
