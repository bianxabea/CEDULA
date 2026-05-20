<?php
/**
 * Superadmin Request Action API (AMORA-style port)
 * Approve or reject block/unblock and registration requests.
 * Superadmin only.
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

$requestId = trim($_POST['request_id'] ?? '');
$action    = trim($_POST['action'] ?? ''); // 'approve' or 'reject'

if (empty($requestId) || !in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'error' => 'request_id and action (approve|reject) are required.']);
    exit;
}

$currentUser = $_SESSION['user'] ?? [];
$actedBy     = $currentUser['id'] ?? 'superadmin';

// Fetch the request
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

if ($request['status'] !== 'pending') {
    echo json_encode(['success' => false, 'error' => 'This request has already been ' . $request['status'] . '.']);
    exit;
}

$newStatus = ($action === 'approve') ? 'approved' : 'rejected';
$targetId  = $request['target_id'];
$reqType   = $request['request_type'];

$conn->begin_transaction();
try {
    // 1. Update request status
    $upd = $conn->prepare("UPDATE user_block_requests SET status = ? WHERE id = ?");
    $upd->bind_param('si', $newStatus, $requestId);
    $upd->execute();
    $upd->close();

    if ($action === 'approve') {
        if ($reqType === 'registration') {
            // AMORA+CEDULA dual-schema: set both is_blocked=0 AND status='registered'
            $upd2 = $conn->prepare("UPDATE users SET is_blocked = 0, status = 'approved' WHERE id = ?");;
            $upd2->bind_param('s', $targetId);
            $upd2->execute();
            $upd2->close();
        } else {
            // block or unblock
            $newIsBlocked = ($reqType === 'block') ? 1 : 0;
            if ($newIsBlocked === 0) {
                $upd2 = $conn->prepare("UPDATE users SET is_blocked = 0, status = 'approved' WHERE id = ?");
                
                // Check if target is superadmin for swap logic
                $roleStmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
                $roleStmt->bind_param('s', $targetId);
                $roleStmt->execute();
                $targetRole = $roleStmt->get_result()->fetch_assoc()['role'] ?? '';
                $roleStmt->close();

                if ($targetRole === 'superadmin') {
                    $currId = $_SESSION['user']['id'];
                    $blockCurr = $conn->prepare("UPDATE users SET is_blocked = 1 WHERE id = ?");
                    $blockCurr->bind_param('s', $currId);
                    $blockCurr->execute();
                    $blockCurr->close();
                    $superadminSwap = true;
                }
            } else {
                $upd2 = $conn->prepare("UPDATE users SET is_blocked = 1 WHERE id = ?");
            }
            $upd2->bind_param('s', $targetId);
            $upd2->execute();
            $upd2->close();
        }
    } elseif ($action === 'reject' && $reqType === 'registration') {
        // Rejected registrations: mark user as rejected (do NOT delete — keep audit trail)
        $upd2 = $conn->prepare("UPDATE users SET status = 'rejected' WHERE id = ?");
        $upd2->bind_param('s', $targetId);
        $upd2->execute();
        $upd2->close();
    }

    $conn->commit();
    $verb = ucfirst($reqType) . ' request ' . $newStatus . ' successfully.';
    echo json_encode([
        'success' => true, 
        'message' => $verb,
        'superadmin_swap' => $superadminSwap ?? false
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>
