<?php
require './db_connect.php';

// We must set the content type to JSON for the fetch() response
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $field = $_POST['field'] ?? '';
    $value = $_POST['value'] ?? '';

    // A whitelist of fields we are allowed to check
    // This is a crucial security step!
    $allowed_fields = ['id', 'username', 'email'];

    if (!in_array($field, $allowed_fields)) {
        // If the field is not allowed, send an error
        echo json_encode(['error' => 'Invalid validation field.']);
        exit;
    }

    if (empty($value)) {
        echo json_encode(['exists' => false]);
        exit;
    }

    // When editing a user we must ignore that user's own row,
    // otherwise their unchanged email/username is flagged as "taken."
    $excludeId = trim($_POST['exclude_id'] ?? '');

    if ($excludeId !== '') {
        $sql = "SELECT 1 FROM users WHERE $field = ? AND id != ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        if (!$stmt) { echo json_encode(['error' => 'Database prepare error.']); exit; }
        $stmt->bind_param('ss', $value, $excludeId);
    } else {
        $sql = "SELECT 1 FROM users WHERE $field = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        if (!$stmt) { echo json_encode(['error' => 'Database prepare error.']); exit; }
        $stmt->bind_param('s', $value);
    }

    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // An entry was found, it exists
        echo json_encode(['exists' => true]);
    } else {
        // No entry found, it is unique
        echo json_encode(['exists' => false]);
    }

    $stmt->close();
    $conn->close();
} else {
    // Not a POST request
    echo json_encode(['error' => 'Invalid request method.']);
    exit;
}
?>