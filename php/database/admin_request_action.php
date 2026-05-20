<?php
/**
 * Admin Request Action API (AMORA-style port)
 * Approve or reject REGISTRATION requests ONLY.
 * Admins CANNOT approve block/unblock requests — superadmin only.
 */
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

$requestId = trim($_POST['request_id'] ?? '');
$action    = trim($_POST['action'] ?? ''); // 'approve' or 'reject'

if (empty($requestId) || !in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'error' => 'request_id and action (approve|reject) are required.']);
    exit;
}

$currentUser = $_SESSION['user'] ?? [];

// Fetch request and verify it's a registration type
$reqStmt = $conn->prepare("SELECT * FROM user_block_requests WHERE id = ?");
$reqStmt->bind_param('i', $requestId);
$reqStmt->execute();
$reqResult = $reqStmt->get_result();

if ($reqResult->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Request not found.']);
    $reqStmt->close();
    exit;
}

$request = $reqResult->fetch_assoc();
$reqStmt->close();

// AMORA rule: Admins can ONLY handle registration requests
if ($request['request_type'] !== 'registration') {
    echo json_encode(['success' => false, 'error' => 'Admins can only process registration requests. Block/unblock requests require superadmin approval.']);
    exit;
}

if ($request['status'] !== 'pending') {
    echo json_encode(['success' => false, 'error' => 'This request has already been ' . $request['status'] . '.']);
    exit;
}

$newStatus = ($action === 'approve') ? 'approved' : 'rejected';
$targetId  = $request['target_id'];

$conn->begin_transaction();
try {
    // Update request row
    $upd = $conn->prepare("UPDATE user_block_requests SET status = ? WHERE id = ?");
    $upd->bind_param('si', $newStatus, $requestId);
    $upd->execute();
    $upd->close();

    if ($action === 'approve') {
        // AMORA+CEDULA dual-schema: activate both flags
        $upd2 = $conn->prepare("UPDATE users SET is_blocked = 0, status = 'approved' WHERE id = ?");
        $upd2->bind_param('s', $targetId);
        $upd2->execute();
        $upd2->close();
        $msg = 'Registration approved. User can now log in.';
    } else {
        // Reject: mark user as rejected but keep the record for audit trail
        $upd2 = $conn->prepare("UPDATE users SET status = 'rejected' WHERE id = ?");
        $upd2->bind_param('s', $targetId);
        $upd2->execute();
        $upd2->close();
        $msg = 'Registration rejected.';
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => $msg]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>
