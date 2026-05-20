<?php
/**
 * Role-based sidebar (Standardized).
 * Expects $basePath, $currentPage, $user / $userRole.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($user)) {
    $user = $_SESSION['user'] ?? null;
}
$userRole = $user['role'] ?? 'consumer';

// Ensure we have a base URL
if (!function_exists('getBaseUrl')) {
    function getBaseUrl()
    {
        return 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/CEDULA';
    }
}
$baseUrl = getBaseUrl();

// Helper to check active state
function isActive($page, $current)
{
    return $current === $page ? 'active' : '';
}
// Default current page if not set
$currentPage = $currentPage ?? '';
?>
<?php
$fullName = trim(
    htmlspecialchars($user['firstName'] ?? 'Guest') . ' ' .
    htmlspecialchars($user['middleInitial'] ?? '') . ' ' .
    htmlspecialchars($user['lastName'] ?? '')
);
$initials = '';
if (!empty($user['firstName']))
    $initials .= strtoupper($user['firstName'][0]);
if (!empty($user['lastName']))
    $initials .= strtoupper($user['lastName'][0]);
if (!$initials)
    $initials = 'G';

$roleBadgeColors = [
    'superadmin' => 'background:#ede9fe; color:#7c3aed;',
    'admin' => 'background:#fef3c7; color:#d97706;',
    'consumer' => 'background:#dbeafe; color:#2563eb;',
];
$roleIcon = [
    'superadmin' => 'fa-crown',
    'admin' => 'fa-shield-halved',
    'consumer' => 'fa-user',
];
$badgeStyle = $roleBadgeColors[$userRole] ?? 'background:#f3f4f6; color:#6b7280;';
$icon = $roleIcon[$userRole] ?? 'fa-user';
?>
<aside class="sidebar" style="background-color: #e92a4aff;">
    <div class="sidebar-profile">
        <div class="avatar">
            <?php echo $initials; ?>
        </div>
        <div style="text-align: center;">
            <p class="font-bold text-main" style="margin: 0; color: #ffffff;"><?php echo $fullName; ?></p>
            <span class="status-badge" style="margin-top: 8px; <?php echo $badgeStyle; ?>">
                <i class="fa-solid <?php echo $icon; ?>" style="margin-right: 4px;"></i>
                <?php echo ucfirst($userRole); ?>
            </span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <?php if ($userRole === 'superadmin'): ?>
            <a href="<?php echo $baseUrl; ?>/php/superadmin/index.php" class="sidebar-link <?php echo isActive('superadmin_dashboard', $currentPage); ?>">
                <i class="fa-solid fa-chart-line"></i> Dashboard
            </a>
            <a href="<?php echo $baseUrl; ?>/php/superadmin/users.php" class="sidebar-link <?php echo isActive('superadmin_users', $currentPage); ?>">
                <i class="fa-solid fa-users"></i> Users & Roles
            </a>
            <a href="<?php echo $baseUrl; ?>/php/superadmin/requests.php" class="sidebar-link <?php echo isActive('superadmin_requests', $currentPage); ?>">
                <i class="fa-solid fa-user-shield"></i> Requests
            </a>
            <a href="<?php echo $baseUrl; ?>/php/superadmin/logs.php" class="sidebar-link <?php echo isActive('superadmin_logs', $currentPage); ?>">
                <i class="fa-solid fa-list"></i> Logs
            </a>

            <div style="border-top: 1px solid rgba(255,255,255,0.1); margin: 16px 0; padding-top: 16px;">
                <p style="padding: 0 16px; margin-bottom: 8px; font-size: 0.75rem; color: #ffffffff; text-transform: uppercase;">Store Management</p>
                <a href="<?php echo $baseUrl; ?>/php/admin/restaurants.php" class="sidebar-link <?php echo isActive('admin_restaurants', $currentPage); ?>">
                    <i class="fa-solid fa-store"></i> Stores
                </a>
                <a href="<?php echo $baseUrl; ?>/php/admin/menu.php" class="sidebar-link <?php echo isActive('admin_menu', $currentPage); ?>">
                    <i class="fa-solid fa-utensils"></i> Menu Items
                </a>
                <a href="<?php echo $baseUrl; ?>/php/admin/orders.php" class="sidebar-link <?php echo isActive('admin_orders', $currentPage); ?>">
                    <i class="fa-solid fa-receipt"></i> Order Management
                </a>
            </div>
            <a href="<?php echo $baseUrl; ?>/php/forms/profile.php" class="sidebar-link <?php echo isActive('profile', $currentPage); ?>">
                <i class="fa-solid fa-user"></i> My Profile
            </a>

        <?php
elseif ($userRole === 'admin'): ?>
            <a href="<?php echo $baseUrl; ?>/php/admin/index.php" class="sidebar-link <?php echo isActive('admin_dashboard', $currentPage); ?>">
                <i class="fa-solid fa-chart-line"></i> Dashboard
            </a>
            <a href="<?php echo $baseUrl; ?>/php/admin/consumers.php" class="sidebar-link <?php echo isActive('admin_consumers', $currentPage); ?>">
                <i class="fa-solid fa-users"></i> Manage Consumers
            </a>
            <a href="<?php echo $baseUrl; ?>/php/admin/admin_requests.php" class="sidebar-link <?php echo isActive('admin_requests', $currentPage); ?>">
                <i class="fa-solid fa-file-contract"></i> Requests
            </a>
            <a href="<?php echo $baseUrl; ?>/php/admin/orders.php" class="sidebar-link <?php echo isActive('admin_orders', $currentPage); ?>">
                <i class="fa-solid fa-receipt"></i> Manage Orders
            </a>
            <a href="<?php echo $baseUrl; ?>/php/admin/restaurants.php" class="sidebar-link <?php echo isActive('admin_restaurants', $currentPage); ?>">
                <i class="fa-solid fa-store"></i> Manage Stores
            </a>
            <a href="<?php echo $baseUrl; ?>/php/admin/menu.php" class="sidebar-link <?php echo isActive('admin_menu', $currentPage); ?>">
                <i class="fa-solid fa-utensils"></i> Manage Menu Items
            </a>

            <a href="<?php echo $baseUrl; ?>/php/forms/profile.php" class="sidebar-link <?php echo isActive('profile', $currentPage); ?>">
                <i class="fa-solid fa-user"></i> My Profile
            </a>

        <?php
else: ?>
            <a href="<?php echo $baseUrl; ?>/php/auth/dashboard.php" class="sidebar-link <?php echo isActive('consumer_dashboard', $currentPage);
    echo isActive('dashboard', $currentPage); ?>">
                <i class="fa-solid fa-chart-line"></i> Dashboard
            </a>
            <a href="<?php echo $baseUrl; ?>/php/forms/order_food.php" class="sidebar-link <?php echo isActive('order_food', $currentPage); ?>">
                <i class="fa-solid fa-utensils"></i> Order Pizza
            </a>
            <a href="<?php echo $baseUrl; ?>/php/forms/cart.php" class="sidebar-link <?php echo isActive('cart', $currentPage); ?>">
                <i class="fa-solid fa-cart-shopping"></i> My Cart
            </a>
            <a href="<?php echo $baseUrl; ?>/php/forms/order_history.php" class="sidebar-link <?php echo isActive('order_history', $currentPage); ?>">
                <i class="fa-solid fa-receipt"></i> Order History
            </a>
            <a href="<?php echo $baseUrl; ?>/php/forms/favorites.php" class="sidebar-link <?php echo isActive('favorites', $currentPage); ?>">
                <i class="fa-solid fa-heart"></i> Favorites
            </a>
            <a href="<?php echo $baseUrl; ?>/php/forms/payment_methods.php" class="sidebar-link <?php echo isActive('payment_methods', $currentPage); ?>">
                <i class="fa-solid fa-credit-card"></i> Payment Methods
            </a>
            <a href="<?php echo $baseUrl; ?>/php/forms/profile.php" class="sidebar-link <?php echo isActive('profile', $currentPage); ?>">
                <i class="fa-solid fa-user"></i> My Profile
            </a>
        <?php
endif; ?>
    </nav>
</aside>
