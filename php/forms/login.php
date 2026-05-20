<?php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: ' . (function_exists('getBaseUrl') ? getBaseUrl() : 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/CEDULA') . '/php/auth/dashboard.php');
    exit;
}
require_once __DIR__ . '/../includes/auth.php';
header('Location: ' . getBaseUrl() . '/php/auth/login.php');
exit;
?>
