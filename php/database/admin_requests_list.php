<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

$admin_id = $_SESSION['user']['id'];

// Parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

$where_clauses = ["(ubr.requester_id = ? OR ubr.request_type = 'registration')"];
$params = [$admin_id];
$types = 's';

if ($search !== '') {
    $where_clauses[] = "(u.firstName LIKE ? OR u.lastName LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
    $search_val = "%$search%";
    $params = array_merge($params, [$search_val, $search_val, $search_val, $search_val]);
    $types .= 'ssss';
}

if ($status !== '') {
    $where_clauses[] = "ubr.status = ?";
    $params[] = $status;
    $types .= 's';
}

$where_sql = "WHERE " . implode(" AND ", $where_clauses);

// Get Total Count
$count_query = "SELECT COUNT(*) as total
                FROM user_block_requests ubr
                JOIN users u ON ubr.target_id = u.id
                $where_sql";
$stmt = $conn->prepare($count_query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$total_requests = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$total_pages = ceil($total_requests / $limit);

// Get Paginated Data
$query = "SELECT ubr.*, u.firstName, u.lastName, u.username, u.email
          FROM user_block_requests ubr
          JOIN users u ON ubr.target_id = u.id
          $where_sql
          ORDER BY ubr.created_at DESC
          LIMIT ? OFFSET ?";
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
    'requests' => $list,
    'pagination' => [
        'current_page' => $page,
        'total_pages' => $total_pages,
        'total_requests' => $total_requests,
        'limit' => $limit
    ]
]);
?>
