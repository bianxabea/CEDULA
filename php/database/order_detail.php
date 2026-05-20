<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('consumer');
$user_id = $_SESSION['user']['id'];
$order_id = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;
if ($order_id <= 0) {
    echo json_encode(['success' => false]);
    exit;
}

$stmt = $conn->prepare("
    SELECT o.id, o.restaurant_id, o.status, o.total_amount, o.delivery_address, o.notes, o.created_at, r.name AS restaurant_name
    FROM orders o
    JOIN restaurants r ON r.id = o.restaurant_id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->bind_param('is', $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();
if (!$order) {
    echo json_encode(['success' => false]);
    exit;
}

$order['total_amount'] = (float) $order['total_amount'];

$stmt2 = $conn->prepare("
    SELECT oi.id, oi.menu_item_id, oi.quantity, oi.unit_price, oi.subtotal, m.name AS item_name
    FROM order_items oi
    JOIN menu_items m ON m.id = oi.menu_item_id
    WHERE oi.order_id = ?
");
$stmt2->bind_param('i', $order_id);
$stmt2->execute();
$itemsResult = $stmt2->get_result();
$items = [];
while ($row = $itemsResult->fetch_assoc()) {
    $row['unit_price'] = (float) $row['unit_price'];
    $row['subtotal'] = (float) $row['subtotal'];
    $items[] = $row;
}
$stmt2->close();
$conn->close();

$order['items'] = $items;
echo json_encode(['success' => true, 'order' => $order]);
