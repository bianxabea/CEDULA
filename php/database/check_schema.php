<?php
require_once 'db_connect.php';
$res = $conn->query("DESCRIBE user_block_requests");
$cols = [];
while ($row = $res->fetch_assoc()) {
    $cols[] = $row;
}
echo json_encode($cols, JSON_PRETTY_PRINT);
?>
