<?php
/**
 * Superadmin Requests List API (AMORA-style port)
 * Lists all registration + block requests with pagination, search, and stats.
 * Superadmin only.
 */
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('superadmin');

$statusFilter = $_GET['status'] ?? 'all';
$search       = trim($_GET['search'] ?? '');
$page         = max(1, (int)($_GET['page'] ?? 1));
$limit        = max(5, min(50, (int)($_GET['limit'] ?? 10)));
$offset       = ($page - 1) * $limit;

// Base UNION query: block/unblock requests + registration requests
$baseSql = "
SELECT
    r.id                                                        AS request_id,
    r.request_type,
    r.status,
    r.reason,
    r.created_at,
    COALESCE(CONCAT(req.firstName,' ',req.lastName),'System')   AS requester_name,
    COALESCE(req.username,'system')                             AS requester_username,
    COALESCE(req.role,'')                                       AS requester_role,
    COALESCE(CONCAT(tgt.firstName,' ',tgt.lastName),'Deleted')  AS target_name,
    COALESCE(tgt.username,'deleted')                            AS target_username,
    tgt.id                                                      AS target_id,
    COALESCE(tgt.is_blocked, 0)                                 AS target_blocked,
    COALESCE(tgt.status,'')                                     AS target_status
FROM user_block_requests r
LEFT JOIN users req ON r.requester_id = req.id
LEFT JOIN users tgt ON r.target_id    = tgt.id
WHERE 1=1
";

$params = [];
$types  = '';

if ($statusFilter !== 'all') {
    $baseSql .= " AND r.status = ?";
    $params[] = $statusFilter;
    $types   .= 's';
}

if (!empty($search)) {
    $baseSql .= " AND (CONCAT(tgt.firstName,' ',tgt.lastName) LIKE ? OR tgt.username LIKE ? OR CONCAT(req.firstName,' ',req.lastName) LIKE ?)";
    $term     = "%$search%";
    $params   = array_merge($params, [$term, $term, $term]);
    $types   .= 'sss';
}

$baseSql .= " ORDER BY FIELD(r.status,'pending','approved','rejected'), r.created_at DESC";

// Count total matching rows
$countSql  = "SELECT COUNT(*) AS total FROM ($baseSql) AS sub";
$countStmt = $conn->prepare($countSql);
if (!empty($params)) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$totalFiltered = (int)$countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();

$totalPages = max(1, (int)ceil($totalFiltered / $limit));

// Fetch page
$pageSql  = $baseSql . " LIMIT ? OFFSET ?";
$pParams  = array_merge($params, [$limit, $offset]);
$pTypes   = $types . 'ii';
$stmt     = $conn->prepare($pageSql);
if (!empty($pParams)) $stmt->bind_param($pTypes, ...$pParams);
$stmt->execute();
$result   = $stmt->get_result();

$requests = [];
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}
$stmt->close();

// Global summary stats (all statuses, ignoring filter)
$statsResult = $conn->query("
    SELECT
        COUNT(*)                                               AS total,
        SUM(CASE WHEN status = 'pending'  THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected,
        SUM(CASE WHEN request_type = 'registration' THEN 1 ELSE 0 END) AS registration_requests,
        SUM(CASE WHEN request_type IN ('block','unblock') THEN 1 ELSE 0 END) AS block_requests
    FROM user_block_requests
");
$counts = $statsResult->fetch_assoc();

echo json_encode([
    'success'  => true,
    'requests' => $requests,
    'counts'   => [
        'total'                  => (int)$counts['total'],
        'totalFiltered'          => $totalFiltered,
        'pending'                => (int)$counts['pending'],
        'approved'               => (int)$counts['approved'],
        'rejected'               => (int)$counts['rejected'],
        'registration_requests'  => (int)$counts['registration_requests'],
        'block_requests'         => (int)$counts['block_requests'],
        'totalPages'             => $totalPages,
        'currentPage'            => $page,
    ],
]);

$conn->close();
?>
