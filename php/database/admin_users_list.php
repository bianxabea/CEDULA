<?php
/**
 * List users for admin (consumers only; admin can request delete).
 */
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole(['admin', 'superadmin']);

$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = min(50, max(1, (int)($_GET['per_page'] ?? 10)));
$offset = ($page - 1) * $perPage;

$where = "1=1";
$params = [];
$types = '';
if ($search !== '') {
    $where = "(id LIKE ? OR firstName LIKE ? OR lastName LIKE ? OR username LIKE ? OR email LIKE ?)";
    $p = '%' . $search . '%';
    $params = [$p, $p, $p, $p, $p];
    $types = 'sssss';
}

$countSql = "SELECT COUNT(*) AS total FROM users WHERE $where";
if ($params) {
    $stmt = $conn->prepare($countSql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total = (int) $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
} else {
    $total = (int) $conn->query($countSql)->fetch_assoc()['total'];
}

$sql = "SELECT id, firstName, lastName, username, email, role FROM users WHERE $where ORDER BY role, username LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$types .= 'ii';
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
$stmt->close();
$conn->close();

echo json_encode([
    'success' => true,
    'users' => $users,
    'pagination' => [
        'page' => $page,
        'per_page' => $perPage,
        'total' => $total,
        'total_pages' => (int) ceil($total / $perPage),
    ],
]);
