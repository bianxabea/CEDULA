<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('consumer');

$user_id = $_SESSION['user']['id'];

$sql = "SELECT ci.id, ci.menu_item_id, ci.quantity, m.name, m.price, m.image_path, r.name as restaurant_name, r.id as restaurant_id
        FROM cart c
        JOIN cart_items ci ON c.id = ci.cart_id
        JOIN menu_items m ON ci.menu_item_id = m.id
        JOIN restaurants r ON m.restaurant_id = r.id
        WHERE c.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$total = 0;
while ($row = $result->fetch_assoc()) {
    $row['subtotal'] = $row['price'] * $row['quantity'];
    $total += $row['subtotal'];
    $items[] = $row;
}
$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'items' => $items, 'total' => $total]);
?>
