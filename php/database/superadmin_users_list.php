<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('superadmin');

// Parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role = isset($_GET['role']) ? trim($_GET['role']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

$where_clauses = [];
$params = [];
$types = '';

if ($search !== '') {
    $where_clauses[] = "(firstName LIKE ? OR lastName LIKE ? OR username LIKE ? OR email LIKE ?)";
    $search_val = "%$search%";
    $params = array_merge($params, [$search_val, $search_val, $search_val, $search_val]);
    $types .= 'ssss';
}

if ($role !== '') {
    $where_clauses[] = "role = ?";
    $params[] = $role;
    $types .= 's';
}

if ($status !== '') {
    $is_blocked = ($status === 'blocked') ? 1 : 0;
    $where_clauses[] = "is_blocked = ?";
    $params[] = $is_blocked;
    $types .= 'i';
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Get Total Count
$count_query = "SELECT COUNT(*) as total FROM users $where_sql";
if ($types) {
    $stmt = $conn->prepare($count_query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total_users = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
}
else {
    $total_users = $conn->query($count_query)->fetch_assoc()['total'];
}

$total_pages = ceil($total_users / $limit);

// Get Paginated Data
$query = "SELECT id, firstName, lastName, middleInitial, extension, username, email, role, is_blocked, birthdate, sex, purok, barangay, city, province, zipCode, country, secure_question, secure_question2, secure_question3 FROM users $where_sql ORDER BY role, username LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$new_types = $types . 'ii';
$new_params = array_merge($params, [$limit, $offset]);
$stmt->bind_param($new_types, ...$new_params);
$stmt->execute();
$result = $stmt->get_result();

$list = [];
while ($row = $result->fetch_assoc()) {
    $list[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode([
    'success' => true,
    'users' => $list,
    'pagination' => [
        'current_page' => $page,
        'total_pages' => $total_pages,
        'total_users' => $total_users,
        'limit' => $limit
    ]
]);
