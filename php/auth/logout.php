<?php
session_start();
require_once __DIR__ . '/../database/db_connect.php';

// Log logout if user is logged in
if (isset($_SESSION['user']['id'])) {
    require_once __DIR__ . '/../includes/auth.php';
    logUserAction($_SESSION['user']['id'], 'logout');
}

session_unset();
session_destroy();

// Redirect to login
$base = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/CEDULA';
header('Location: ' . $base . '/php/auth/login.php');
exit;
?>
