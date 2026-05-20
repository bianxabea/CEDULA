<?php
session_start();
include('../database/db_connect.php'); //db_connection.php for your MySQL connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];

    // Query to check if username exists and fetch the secure question
    $query = "SELECT secure_question FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // If user exists, fetch the secure question
        $stmt->bind_result($secure_question);
        $stmt->fetch();

        // Store the username in the session
        $_SESSION['username'] = $username;

        echo json_encode(['exists' => true, 'secure_question' => $secure_question]);
    } else {
        echo json_encode(['exists' => false]);
    }
}
?>
