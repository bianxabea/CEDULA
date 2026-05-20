<?php
require 'database/db_connect.php';

// Add the missing column to user_block_requests
$sql = "ALTER TABLE user_block_requests ADD COLUMN request_type ENUM('block', 'unblock') DEFAULT 'block' AFTER target_id";

if ($conn->query($sql) === TRUE) {
    echo "Successfully altered table user_block_requests to add request_type column.";
}
else {
    echo "Error altering table: " . $conn->error;
}
$conn->close();
?>
