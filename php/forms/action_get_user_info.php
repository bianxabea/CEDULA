<?php
session_start();
header('Content-Type: application/json');
require_once '../database/db_connect.php';

if (!isset($_POST['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit;
}

$user_id = trim($_POST['user_id']);

$stmt = $conn->prepare("SELECT id, firstName, lastName, username, email, is_blocked, status FROM users WHERE id = ?");
$stmt->bind_param('s', $user_id);
$stmt->execute();
$res = $stmt->get_result();

if ($user = $res->fetch_assoc()) {
    // No masking required per standardization update

    // Check account status
    if ($user['status'] === 'pending') {
        echo json_encode(['success' => false, 'message' => 'Your account is pending approval.']);
        exit;
    }
    if ($user['status'] === 'rejected') {
        echo json_encode(['success' => false, 'message' => 'Your registration was rejected.']);
        exit;
    }
    if ($user['is_blocked'] == 1) {
        echo json_encode(['success' => false, 'message' => 'Your account is blocked. Contact administrator.']);
        exit;
    }

    $_SESSION['reset_user_id'] = $user['id'];
    
    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $user['id'],
            'name' => $user['firstName'] . ' ' . $user['lastName'],
            'username' => $user['username'],
            'email' => $user['email']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'ID not found in our system']);
}
$stmt->close();
$conn->close();
?>
