<?php
session_start();
header('Content-Type: application/json');
require_once 'db_connect.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access.']);
    exit;
}

$user = $_SESSION['user'];
$userId = $user['id'];

// Sanitize and validate inputs
$q1 = trim($_POST['secure_question'] ?? '');
$a1 = trim($_POST['secure_answer'] ?? '');
$q2 = trim($_POST['secure_question2'] ?? '');
$a2 = trim($_POST['secure_answer2'] ?? '');
$q3 = trim($_POST['secure_question3'] ?? '');
$a3 = trim($_POST['secure_answer3'] ?? '');

if (!$q1 || !$a1 || !$q2 || !$a2 || !$q3 || !$a3) {
    echo json_encode(['success' => false, 'error' => 'All questions and answers are required.']);
    exit;
}

// Hash the answers
$ha1 = password_hash($a1, PASSWORD_DEFAULT);
$ha2 = password_hash($a2, PASSWORD_DEFAULT);
$ha3 = password_hash($a3, PASSWORD_DEFAULT);

// Update user record
$stmt = $conn->prepare("UPDATE users SET secure_question = ?, secure_answer = ?, secure_question2 = ?, secure_answer2 = ?, secure_question3 = ?, secure_answer3 = ? WHERE id = ?");
$stmt->bind_param('sssssss', $q1, $ha1, $q2, $ha2, $q3, $ha3, $userId);

if ($stmt->execute()) {
    // Update session user object so login check doesn't trigger again
    $_SESSION['user']['secure_question'] = $q1;
    
    $base = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/CEDULA';
    $redirect = $base . '/php/auth/dashboard.php';
    if ($user['role'] === 'admin') $redirect = $base . '/php/admin/index.php';
    elseif ($user['role'] === 'superadmin') $redirect = $base . '/php/superadmin/index.php';

    echo json_encode(['success' => true, 'redirect' => $redirect]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>
