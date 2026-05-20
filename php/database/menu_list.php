<?php
/**
 * Returns menu items for a restaurant (JSON).
 * GET restaurant_id required.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';

$restaurant_id = isset($_GET['restaurant_id']) ? (int) $_GET['restaurant_id'] : 0;
if ($restaurant_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid restaurant']);
    exit;
}

$stmt = $conn->prepare("SELECT id, restaurant_id, name, description, price, image_path, is_available FROM menu_items WHERE restaurant_id = ? ORDER BY name");
$stmt->bind_param('i', $restaurant_id);
$stmt->execute();
$result = $stmt->get_result();
$list = [];
while ($row = $result->fetch_assoc()) {
    $row['price'] = (float) $row['price'];
    $list[] = $row;
}
$stmt->close();
$conn->close();
echo json_encode(['success' => true, 'menu' => $list]);
