<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

// Parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

$where_clauses = ["role = 'consumer'"];
$params = [];
$types = '';

if ($search !== '') {
    $where_clauses[] = "(firstName LIKE ? OR lastName LIKE ? OR username LIKE ? OR email LIKE ?)";
    $search_val = "%$search%";
    $params = array_merge($params, [$search_val, $search_val, $search_val, $search_val]);
    $types .= 'ssss';
}

if ($status !== '') {
    $is_blocked = ($status === 'blocked') ? 1 : 0;
    $where_clauses[] = "is_blocked = ?";
    $params[] = $is_blocked;
    $types .= 'i';
}

$where_sql = "WHERE " . implode(" AND ", $where_clauses);

// Get Total Count
$count_query = "SELECT COUNT(*) as total FROM users $where_sql";
$stmt = $conn->prepare($count_query);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_users = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$total_pages = ceil($total_users / $limit);

// Get Paginated Data
$query = "SELECT id, firstName, lastName, middleInitial, extension, username, email, is_blocked, birthdate, sex, purok, barangay, city, province, zipCode, country, secure_question, secure_question2, secure_question3 FROM users $where_sql ORDER BY username LIMIT ? OFFSET ?";
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
    'consumers' => $list,
    'pagination' => [
        'current_page' => $page,
        'total_pages' => $total_pages,
        'total_users' => $total_users,
        'limit' => $limit
    ]
]);
?>
