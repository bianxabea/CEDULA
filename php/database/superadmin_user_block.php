<?php
/**
 * Superadmin User Block/Unblock API (AMORA-style port)
 * Directly block or unblock a user. Superadmin only.
 * Prevents blocking self, other superadmins, or already-blocked users.
 */
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('superadmin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

$targetId = trim($_POST['user_id'] ?? $_POST['target_id'] ?? '');
$action   = trim($_POST['action'] ?? '');

if (empty($targetId) || !in_array($action, ['block', 'unblock'])) {
    echo json_encode(['success' => false, 'error' => 'user_id and action (block|unblock) are required.']);
    exit;
}

$currentUser = $_SESSION['user'] ?? [];

// Prevent blocking self
if ($targetId === ($currentUser['id'] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'You cannot block/unblock yourself.']);
    exit;
}

// Verify target exists and get their role + current block state
$checkStmt = $conn->prepare("SELECT id, role, is_blocked FROM users WHERE id = ?");
$checkStmt->bind_param('s', $targetId);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'User not found.']);
    $checkStmt->close();
    exit;
}

$targetUser = $checkResult->fetch_assoc();
$checkStmt->close();



$newBlockedVal = ($action === 'block') ? 1 : 0;

if ((int)$targetUser['is_blocked'] === $newBlockedVal) {
    $state = $action === 'block' ? 'already blocked' : 'already unblocked';
    echo json_encode(['success' => false, 'error' => "User is $state."]);
    exit;
}

if ($action === 'unblock') {
    $stmt = $conn->prepare("UPDATE users SET is_blocked = 0, status = 'approved' WHERE id = ?");
} else {
    $stmt = $conn->prepare("UPDATE users SET is_blocked = 1 WHERE id = ?");
}
$stmt->bind_param('s', $targetId);

$superadminSwap = false;
if ($action === 'unblock' && $targetUser['role'] === 'superadmin') {
    $currId = $_SESSION['user']['id'];
    $blockStmt = $conn->prepare("UPDATE users SET is_blocked = 1 WHERE id = ?");
    $blockStmt->bind_param('s', $currId);
    $blockStmt->execute();
    $blockStmt->close();
    $superadminSwap = true;
}

if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'User ' . ($action === 'block' ? 'blocked' : 'unblocked') . ' successfully.',
        'superadmin_swap' => $superadminSwap
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database error updating user.']);
}
$stmt->close();
$conn->close();
?>
