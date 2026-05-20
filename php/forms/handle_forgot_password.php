<?php
session_start();
include('../database/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Check if the ID session variable exists
    if (!isset($_SESSION['reset_user_id'])) {
        $_SESSION['login_error'] = "Session expired. Please start the reset process again.";
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['reset_user_id'];
    $user_answer = trim($_POST['secure_answer']);

    // 2. Fetch the HASHED answer from DB using ID
    $stmt = $conn->prepare("SELECT secure_answer FROM users WHERE id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($db_hashed_answer);
        $stmt->fetch();

        // 3. Verify the hash (using password_verify)
        if (password_verify($user_answer, $db_hashed_answer)) {
            // SUCCESS: Answer matches

            // Set flag to allow access to reset page
            $_SESSION['allow_password_reset'] = true;

            // Redirect to CHANGE_PASSWORD.PHP
            header("Location: change_password.php");
            exit();
        } else {
            // FAILURE: Wrong answer
            $_SESSION['login_error'] = "Incorrect security answer.";
            header("Location: login.php");
            exit();
        }
    } else {
        // ID not found
        $_SESSION['login_error'] = "User record not found.";
        header("Location: login.php");
        exit();
    }

    $stmt->close();
} else {
    // Direct access not allowed
    header("Location: login.php");
    exit();
}
?>