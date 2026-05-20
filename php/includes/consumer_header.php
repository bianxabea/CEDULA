<?php
/** Consumer dashboard layout: header + sidebar. Expects $base, $current_page. */
?>
<header class="dashboard-header">
    <div class="navbar-left">
        <a href="dashboard.php" style="display:flex;align-items:center;gap:0.75rem;text-decoration:none;color:inherit;">
            <img src="../../images/logo.svg" alt="Pizza Crust Delight logo" class="logo">
            <span class="navbar-text">Pizza Crust Delight <span class="navbar-subtext">Online Food Delivery</span></span>
        </a>
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
        <?php if (isset($_SESSION['user'])) {
    $u = $_SESSION['user'];
    echo '<img src="../../images/profile.png" alt="Profile" class="profile">';
    echo '<h2>' . htmlspecialchars($u['firstName'] . ' ' . $u['lastName']) . '</h2>';
    echo '<p>' . htmlspecialchars($u['email']) . '</p>';
}?>
    </div>
    <nav class="sidebar-menu">
        <a href="order_food.php" class="<?php echo($current_page ?? '') === 'order_food' ? 'active' : ''; ?>">Order Food</a>
        <a href="order_history.php" class="<?php echo($current_page ?? '') === 'order_history' ? 'active' : ''; ?>">Order History</a>
        <a href="track_order.php" class="<?php echo($current_page ?? '') === 'track_order' ? 'active' : ''; ?>">Track Order</a>
        <a href="favorites.php" class="<?php echo($current_page ?? '') === 'favorites' ? 'active' : ''; ?>">Favorites</a>
        <a href="payment_methods.php" class="<?php echo($current_page ?? '') === 'payment_methods' ? 'active' : ''; ?>">Payment Methods</a>
        <a href="change_password.php">Change My Password</a>
    </nav>
</aside>
