<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
    header('Location: ' . getBaseUrl() . '/php/auth/dashboard.php');
    exit;
}
requireRole('consumer');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['logout_action'])) {
        session_unset();
        session_destroy();
        header("Location: " . getBaseUrl() . "/php/forms/login.php");
        exit;
    }
}

$base = getBaseUrl();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pizza Crust Delight Dashboard</title>
    <link rel="stylesheet" href="../../css/serve_asset.php?file=design-system.css">
    <link rel="stylesheet" href="../../css/serve_asset.php?file=dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <div class="dashboard-container">

        <header class="dashboard-header">
            <div class="navbar-left">
                <img src="../../images/logo.svg" alt="Pizza Crust Delight logo" class="logo">
                <span class="navbar-text">Pizza Crust Delight <span class="navbar-subtext">Online Food Delivery</span></span>
            </div>
            <div class="navbar-right">
                <form action="" method="POST">
                    <input type="hidden" name="logout_action" value="1">
                    <button type="submit" class="nav-link">Log Out</button>
                </form>
            </div>
        </header>

        <aside class="dashboard-sidebar">
            <div class="profile-section">
                <?php
if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
    // Use a more descriptive alt text
    echo '<img src="../../images/profile.png" alt="User Profile Picture" class="profile">';
    echo '<h2>' . htmlspecialchars($user['firstName']) . ' ' . htmlspecialchars($user['lastName']) . '</h2>';
    echo '<p>' . htmlspecialchars($user['email']) . '</p>';
}
?>
            </div>
            <nav class="sidebar-menu">
                <a href="order_food.php">Order Food</a>
                <a href="order_history.php">Order History</a>
                <a href="track_order.php">Track Order</a>
                <a href="favorites.php">Favorites</a>
                <a href="payment_methods.php">Payment Methods</a>
                <a href="change_password.php">Change My Password</a>
            </nav>
        </aside>

        <main class="dashboard-main">
            <h1 class="page-title">What are you craving today?</h1>
            <p class="page-subtitle">Choose an option below to order food, track your orders, or manage your account.</p>
            <div class="card-container">
                <a href="order_food.php" class="card card-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L26.6 53.3c7-15 21-25.3 39.4-25.3H510c18.4 0 32.4 10.3 39.4 25.3l16.4 178.3c7 7 10 15 10 24zM176 256c-17.7 0-32 14.3-32 32s14.3 32 32 32H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H176zM32 480h32c17.7 0 32-14.3 32-32s-14.3-32-32-32H32c-17.7 0-32 14.3-32 32s14.3 32 32 32z"/></svg>
                    <h3>Order Food</h3>
                    <p>Browse menus from top rated restaurants near you.</p>
                </a>
                <a href="order_history.php" class="card card-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M64 0C28.7 0 0 28.7 0 64V448c0 35.3 28.7 64 64 64H320c35.3 0 64-28.7 64-64V64c0-35.3-28.7-64-64-64H64zM96 64H288c17.7 0 32 14.3 32 32v32c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V96c0-17.7 14.3-32 32-32zM64 224c0-17.7 14.3-32 32-32H288c17.7 0 32 14.3 32 32s-14.3 32-32 32H96c-17.7 0-32-14.3-32-32zM32 352v64c0 17.7 14.3 32 32 32H288c17.7 0 32-14.3 32-32v-64c0-17.7-14.3-32-32-32H64c-17.7 0-32 14.3-32 32z"/></svg>
                    <h3>My Orders</h3>
                    <p>Track current deliveries and view past meals.</p>
                </a>
                <a href="favorites.php" class="card card-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M47.6 300.4L228.3 469.1c7.5 7 17.4 10.9 27.7 10.9s20.2-3.9 27.7-10.9L464.4 300.4c30.4-28.3 47.6-68 47.6-109.5v-5.8c0-69.9-50.5-129.5-119.4-141C347 36.5 300.6 51.4 268 84L256 96 244 84c-32.6-32.6-79-47.5-124.6-39.9C50.5 55.6 0 115.2 0 185.1v5.8c0 41.5 17.2 81.2 47.6 109.5z"/></svg>
                    <h3>Favorites</h3>
                    <p>Quickly re-order your saved meals and restaurants.</p>
                </a>
                <a href="payment_methods.php" class="card card-link">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M64 32C28.7 32 0 60.7 0 96v320c0 35.3 28.7 64 64 64h448c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64zm90.7 96c7.6 0 14.2 5.4 15.7 12.9l23.2 116c1.4 7 7.9 12.2 15.1 12.2H288c.4 0 .7 0 1.1 0h76.5c.4 0 .7 0 1.1 0h29.2c7.1 0 13.7-5.2 15.1-12.2l23.2-116c1.5-7.5 8.1-12.9 15.7-12.9h58.9c8.8 0 16 7.2 16 16s-7.2 16-16 16h-50.8l-21.2 106H432c.4 0 .7 0 1.1 0h-76.5c-.4 0-.7 0-1.1 0H258.8l-21.2-106H272c8.8 0 16-7.2 16-16s-7.2-16-16-16H154.7z"/></svg>
                    <h3>Payment Methods</h3>
                    <p>Manage your payment options for checkout.</p>
                </a>
            </div>

        </main>

        <?php include __DIR__ . '/../includes/layout/footer.php'; ?>

    </div> </body>
</html>