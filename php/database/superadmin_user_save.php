<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('superadmin');

// Get input values
$id = trim($_POST['id'] ?? '');
$customId = trim($_POST['custom_id'] ?? '');

// When editing, both 'id' and 'custom_id' are set to the user's ID.
// Use custom_id as fallback if the hidden 'id' field arrives empty.
if ($id === '' && $customId !== '') {
    // Check whether this custom_id belongs to an existing user (= edit mode)
    $chk = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $chk->bind_param('s', $customId);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) {
        $id = $customId;
    }
    $chk->close();
}
$firstName = trim($_POST['firstName'] ?? '');
$lastName = trim($_POST['lastName'] ?? '');
$middleInitial = trim($_POST['middleInitial'] ?? '');
$extension = trim($_POST['extension'] ?? '');
$sex = trim($_POST['sex'] ?? '');
$birthdate = trim($_POST['birthdate'] ?? '');
$purok = trim($_POST['purok'] ?? '');
$barangay = trim($_POST['barangay'] ?? '');
$city = trim($_POST['city'] ?? '');
$province = trim($_POST['province'] ?? '');
$zipCode = trim($_POST['zipCode'] ?? '');
$country = trim($_POST['country'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$role = trim($_POST['role'] ?? 'consumer');
$password = trim($_POST['password'] ?? '');

// Security Questions (Only for consumers)
$secure_question = $_POST['secure_question'] ?? null;
$secure_answer = $_POST['secure_answer'] ?? null;
$secure_question2 = $_POST['secure_question2'] ?? null;
$secure_answer2 = $_POST['secure_answer2'] ?? null;
$secure_question3 = $_POST['secure_question3'] ?? null;
$secure_answer3 = $_POST['secure_answer3'] ?? null;

if (!$firstName || !$lastName || !$username || !$email || !$sex || !$birthdate) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

// Calculate Age
$age = date_diff(date_create($birthdate), date_create('today'))->y;

// Check duplicate username (exclude self when editing)
$sql = "SELECT id, username FROM users WHERE username = ?";
if ($id) $sql .= " AND id != ?";
$stmt = $conn->prepare($sql);
if ($id) $stmt->bind_param('ss', $username, $id);
else     $stmt->bind_param('s', $username);
$stmt->execute();
$dupResult = $stmt->get_result();
if ($dupResult->num_rows > 0) {
    $dupRow = $dupResult->fetch_assoc();
    echo json_encode([
        'success' => false,
        'error' => "This username is already taken by user ID: {$dupRow['id']}."
    ]);
    $stmt->close(); exit;
}
$stmt->close();

// Check duplicate email (exclude self when editing)
$sql = "SELECT id, username FROM users WHERE email = ?";
if ($id) $sql .= " AND id != ?";
$stmt = $conn->prepare($sql);
if ($id) $stmt->bind_param('ss', $email, $id);
else     $stmt->bind_param('s', $email);
$stmt->execute();
$dupResult = $stmt->get_result();
if ($dupResult->num_rows > 0) {
    $dupRow = $dupResult->fetch_assoc();
    echo json_encode([
        'success' => false,
        'error' => "This email is already used by user \"{$dupRow['username']}\" (ID: {$dupRow['id']})."
    ]);
    $stmt->close(); exit;
}
$stmt->close();

if ($id) {
    // UPDATE USER
    $types = "ssssssisssssssss"; // 7th param ($age) is integer
    $params = [
        $firstName, $lastName, $middleInitial, $extension, $sex, $birthdate, $age,
        $purok, $barangay, $city, $province, $zipCode, $country,
        $username, $email, $role
    ];

    $sql = "UPDATE users SET firstName=?, lastName=?, middleInitial=?, extension=?, sex=?, birthdate=?, age=?,
            purok=?, barangay=?, city=?, province=?, zipCode=?, country=?,
            username=?, email=?, role=?";

    if ($password) {
        $sql .= ", password=?";
        $types .= "s";
        $params[] = password_hash($password, PASSWORD_DEFAULT);
    }

    // Update security questions only if role is consumer and provided
    if ($role === 'consumer' && $secure_question && $secure_answer) {
        $sql .= ", secure_question=?, secure_answer=?, secure_question2=?, secure_answer2=?, secure_question3=?, secure_answer3=?";
        $types .= "ssssss";
        $params[] = $secure_question;
        // Normalize: trim + lowercase before hashing so forgot-password
        // verification is case-insensitive (matches forgot_password.php logic)
        $params[] = password_hash(strtolower(trim($secure_answer)), PASSWORD_DEFAULT);
        $params[] = $secure_question2;
        $params[] = password_hash(strtolower(trim($secure_answer2)), PASSWORD_DEFAULT);
        $params[] = $secure_question3;
        $params[] = password_hash(strtolower(trim($secure_answer3)), PASSWORD_DEFAULT);
    }

    $sql .= " WHERE id=?";
    $types .= "s";
    $params[] = $id;

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
}
else {
    // CREATE USER
    if (!$password) {
        echo json_encode(['success' => false, 'error' => 'Password required for new user']);
        exit;
    }

    // Use provided ID or generate one
    $newId = $customId ? $customId : sprintf('%04d-%04d', rand(0, 9999), rand(0, 9999));

    // Check ID uniqueness if needed (omitted for brevity, assuming low collision or error catch)

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    // Normalize: trim + lowercase before hashing so forgot-password
    // verification is case-insensitive (matches forgot_password.php logic)
    $ans1 = ($role === 'consumer' && $secure_answer) ? password_hash(strtolower(trim($secure_answer)), PASSWORD_DEFAULT) : null;
    $ans2 = ($role === 'consumer' && $secure_answer2) ? password_hash(strtolower(trim($secure_answer2)), PASSWORD_DEFAULT) : null;
    $ans3 = ($role === 'consumer' && $secure_answer3) ? password_hash(strtolower(trim($secure_answer3)), PASSWORD_DEFAULT) : null;

    // Ensure questions are null if not consumer
    if ($role !== 'consumer') {
        $secure_question = $secure_question2 = $secure_question3 = null;
    }

    $status = 'approved';
    $sql = "INSERT INTO users (
                id, firstName, lastName, middleInitial, extension, sex, birthdate, age,
                purok, barangay, city, province, zipCode, country,
                username, email, password, role, status,
                secure_question, secure_answer, secure_question2, secure_answer2, secure_question3, secure_answer3
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        'sssssssisssssssssssssssss',
        $newId, $firstName, $lastName, $middleInitial, $extension, $sex, $birthdate, $age,
        $purok, $barangay, $city, $province, $zipCode, $country,
        $username, $email, $hashedPassword, $role, $status,
        $secure_question, $ans1, $secure_question2, $ans2, $secure_question3, $ans3
    );
}

if ($stmt->execute()) {
    $superadminSwap = false;
    // Single Superadmin logic for both new creations and edits
    if ($role === 'superadmin' && $id !== $_SESSION['user']['id']) {
        // Self-block the creator for security swap
        $creatorId = $_SESSION['user']['id'];
        $blockStmt = $conn->prepare("UPDATE users SET is_blocked = 1 WHERE id = ?");
        $blockStmt->bind_param('s', $creatorId);
        $blockStmt->execute();
        $blockStmt->close();
        $superadminSwap = true;
    }
    echo json_encode(['success' => true, 'superadmin_swap' => $superadminSwap]);
}
else {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
}
$stmt->close();
$conn->close();
?>
