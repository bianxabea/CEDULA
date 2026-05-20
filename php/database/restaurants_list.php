<?php
/**
 * Returns list of active restaurants (JSON).
 * No auth required for browsing; cart/checkout require login.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';

$sql = "SELECT id, name, description, address, image_path FROM restaurants WHERE is_active = 1 ORDER BY name";
$result = $conn->query($sql);
$list = [];
while ($row = $result->fetch_assoc()) {
    $list[] = $row;
}
echo json_encode(['success' => true, 'restaurants' => $list]);
$conn->close();
