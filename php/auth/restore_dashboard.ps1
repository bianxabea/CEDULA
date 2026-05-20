$content = @'
<?php
/**
 * Single dashboard for all roles (AMORA-style).
 * Uses layout: navbar, role-based sidebar, footer.
 * Consumer: aggregated stats (total orders, pending, latest order, favorites).
 */
require_once __DIR__ . '/../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout_action'])) {
    session_unset();
    session_destroy();
    header('Location: ' . getBaseUrl() . '/php/auth/login.php');
    exit;
}

$basePath = getBasePath(__FILE__);
$baseUrl = getBaseUrl();
$currentPage = 'dashboard';
$pageTitle = 'Dashboard';

// Consumer dashboard stats (aggregation)
$consumerStats = null;
if ($userRole === 'consumer' && isset($user['id'])) {
    try {
        require_once __DIR__ . '/../database/db_connect.php';
        $uid = $user['id'];
        $consumerStats = [
            'total_orders' => 0,
            'pending_orders' => 0,
            'total_spent' => 0,
            'favorites_count' => 0,
            'latest_order' => null,
        ];
        $stmt = $conn->prepare("SELECT COUNT(*) AS n FROM orders WHERE user_id = ?");
    $stmt->bind_param('s', $uid);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $consumerStats['total_orders'] = (int) ($row['n'] ?? 0);
    $stmt->close();

    $stmt = $conn->prepare("SELECT COUNT(*) AS n FROM orders WHERE user_id = ? AND status NOT IN ('delivered','cancelled')");
    $stmt->bind_param('s', $uid);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $consumerStats['pending_orders'] = (int) ($row['n'] ?? 0);
    $stmt->close();

    $stmt = $conn->prepare("SELECT COALESCE(SUM(total_amount), 0) AS total FROM orders WHERE user_id = ? AND status = 'delivered'");
    $stmt->bind_param('s', $uid);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $consumerStats['total_spent'] = (float) ($row['total'] ?? 0);
    $stmt->close();

    $stmt = $conn->prepare("SELECT COUNT(*) AS n FROM user_favorites WHERE user_id = ?");
    $stmt->bind_param('s', $uid);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $consumerStats['favorites_count'] = (int) ($row['n'] ?? 0);
    $stmt->close();

    $stmt = $conn->prepare("
        SELECT o.id, o.status, o.total_amount, o.created_at, r.name AS restaurant_name
        FROM orders o
        JOIN restaurants r ON r.id = o.restaurant_id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC
        LIMIT 1
    ");
    $stmt->bind_param('s', $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $consumerStats['latest_order'] = $res->fetch_assoc();
        $consumerStats['latest_order']['total_amount'] = (float) ($consumerStats['latest_order']['total_amount'] ?? 0);
    }
    $stmt->close();
    $conn->close();
    } catch (Exception $e) {
        $consumerStats = null;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - FoodGrab</title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/serve_asset.php?file=design-system.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/serve_asset.php?file=dashboard.css">
    <?php if ($userRole === 'admin'): ?>
        <link rel="stylesheet" href="<?php echo $basePath; ?>css/serve_asset.php?file=admin.css">
    <?php elseif ($userRole === 'superadmin'): ?>
        <link rel="stylesheet" href="<?php echo $basePath; ?>css/serve_asset.php?file=superadmin.css">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="<?php
    if ($userRole === 'superadmin') echo 'superadmin-v2';
    elseif ($userRole === 'admin') echo 'admin-theme-v2';
    else echo 'consumer-theme-v2';
?>">

    <?php
    $showSidebarToggle = true;
    include __DIR__ . '/../includes/layout/navbar.php';
    ?>
    <div class="dashboard-container">
        <div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>
        <?php include __DIR__ . '/../includes/layout/sidebar.php'; ?>
        <main class="dashboard-main">
            <!-- Island Header -->
            <div class="island-header reveal-up">
                <div class="island-welcome">
                    <h1 class="page-title">Welcome back, <?php echo htmlspecialchars($user['firstName']); ?>!</h1>
                    <p class="page-subtitle" style="margin-bottom: 0;">
                        <?php
                            if ($userRole === 'consumer') echo 'Ready for a fresh slice of Pizza Crust Delight?';
                            elseif ($userRole === 'admin') echo 'Business is looking good today.';
                            else echo 'System status: All systems operational.';
                        ?>
                    </p>
                </div>
                <div class="island-actions">
                    <?php if ($userRole === 'consumer'): ?>
                        <a href="<?php echo $baseUrl; ?>/php/forms/order_food.php" class="btn-primary" style="padding: 1rem 2rem;">
                            <i class="fa-solid fa-pizza-slice" style="margin-right: 8px;"></i> Start Order
                        </a>
                    <?php elseif ($userRole === 'admin'): ?>
                        <a href="<?php echo $baseUrl; ?>/php/admin/orders.php" class="btn-primary">
                            <i class="fa-solid fa-bell" style="margin-right: 8px;"></i> Pending Orders
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bento-grid">
                <?php if ($userRole === 'consumer'): ?>
                    <!-- Stats Section in Bento -->
                    <div class="bento-item span-2 stat-card reveal-up" style="animation-delay: 0.1s;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                            <h3 class="section-title" style="margin: 0;">Your Velocity</h3>
                            <div class="stat-icon-circle" style="width: 40px; height: 40px; background: var(--primary-light); color: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-receipt"></i>
                            </div>
                        </div>
                        <div class="stat-value" style="font-size: 3rem;"><?php echo (int) $consumerStats['total_orders']; ?></div>
                        <div class="stat-label">Lifetime Orders</div>
                    </div>

                    <div class="bento-item stat-card reveal-up" style="animation-delay: 0.2s;">
                        <h3 class="stat-label">Spent</h3>
                        <div class="stat-value" style="font-size: 1.75rem; margin-top: 0.5rem;">₱<?php echo number_format($consumerStats['total_spent'], 0); ?></div>
                    </div>

                    <div class="bento-item stat-card reveal-up" style="animation-delay: 0.3s;">
                        <h3 class="stat-label">Favorites</h3>
                        <div class="stat-value" style="font-size: 1.75rem; margin-top: 0.5rem;"><?php echo (int) $consumerStats['favorites_count']; ?></div>
                    </div>

                    <!-- Large Action Card -->
                    <div class="bento-item bento-action-card span-2 row-2 reveal-up" style="animation-delay: 0.4s;">
                        <div style="margin-bottom: auto;">
                            <h3>Ready to Explore?</h3>
                            <p>Discover new flavors and special offers from our partners.</p>
                        </div>
                        <a href="<?php echo $baseUrl; ?>/php/forms/order_food.php" class="btn-white">Browse Menu</a>
                        <img src="<?php echo $baseUrl; ?>/images/pizza_hero.png" style="position: absolute; bottom: -20px; right: -20px; width: 150px; opacity: 0.3; transform: rotate(15deg); pointer-events: none;">
                    </div>

                    <!-- Latest Order Bento Item -->
                    <div class="bento-item span-2 reveal-up" style="animation-delay: 0.5s;">
                        <h3 class="section-title">Active Order</h3>
                        <?php if (!empty($consumerStats['latest_order'])): $lo = $consumerStats['latest_order']; ?>
                            <div style="margin-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-weight: 700; font-size: 1.1rem;"><?php echo htmlspecialchars($lo['restaurant_name']); ?></div>
                                    <div style="font-size: 0.85rem; color: var(--text-muted);">ID: #<?php echo $lo['id']; ?></div>
                                </div>
                                <div class="order-status-badge order-status-<?php echo htmlspecialchars($lo['status']); ?> pulse-primary">
                                    <?php echo htmlspecialchars($lo['status']); ?>
                                </div>
                            </div>
                            <a href="<?php echo $baseUrl; ?>/php/forms/track_order.php?order_id=<?php echo (int) $lo['id']; ?>" style="margin-top: 1.5rem; display: block; color: var(--primary-color); font-weight: 700; text-decoration: none;">Track Session <i class="fa-solid fa-arrow-right"></i></a>
                        <?php else: ?>
                            <p style="color: var(--text-muted); margin-top: 1rem;">No active hunger detected.</p>
                        <?php endif; ?>
                    </div>
                <?php elseif ($userRole === 'admin'): ?>
                    <!-- Admin Bento Items -->
                    <div class="bento-item span-2 reveal-up" style="animation-delay: 0.1s;">
                        <h3 class="section-title">Sales Analytics</h3>
                        <div style="font-size: 2.5rem; margin: 1rem 0;">₱0</div>
                        <div class="stat-label">Total Volume Today</div>
                    </div>

                    <div class="bento-item reveal-up" style="animation-delay: 0.2s;">
                        <h3>Active Stores</h3>
                        <div class="stat-value">2</div>
                        <a href="<?php echo $baseUrl; ?>/php/admin/restaurants.php" style="margin-top: auto; color: var(--adm-accent); text-decoration: none; font-weight: 600;">Manage <i class="fa-solid fa-chevron-right"></i></a>
                    </div>

                    <div class="bento-item reveal-up" style="animation-delay: 0.3s;">
                        <h3>Inventory</h3>
                        <div class="stat-value">7</div>
                        <a href="<?php echo $baseUrl; ?>/php/admin/menu.php" style="margin-top: auto; color: var(--adm-accent); text-decoration: none; font-weight: 600;">Items <i class="fa-solid fa-chevron-right"></i></a>
                    </div>

                    <div class="bento-item span-4 reveal-up" style="animation-delay: 0.4s;">
                        <h3>Store Performance Overview</h3>
                        <p style="color: var(--text-muted);">Real-time metrics will appear here once ordering sessions begin.</p>
                    </div>

                <?php elseif ($userRole === 'superadmin'): ?>
                    <!-- Superadmin Bento Items -->
                    <div class="bento-item span-4 reveal-up">
                        <h3 class="section-title">System Command Center</h3>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; margin-top: 2rem;">
                            <a href="<?php echo $baseUrl; ?>/php/superadmin/index.php" class="card-link" style="text-align: center; padding: 2rem; border: 1px solid var(--border-light); border-radius: 20px;">
                                <i class="fa-solid fa-users-gear" style="font-size: 2rem; color: var(--sa-accent); margin-bottom: 1rem;"></i>
                                <div style="font-weight: 700;">Identity Management</div>
                            </a>
                            <a href="<?php echo $baseUrl; ?>/php/superadmin/logs.php" class="card-link" style="text-align: center; padding: 2rem; border: 1px solid var(--border-light); border-radius: 20px;">
                                <i class="fa-solid fa-scroll" style="font-size: 2rem; color: var(--sa-accent); margin-bottom: 1rem;"></i>
                                <div style="font-weight: 700;">Audit Logs</div>
                            </a>
                            <a href="<?php echo $baseUrl; ?>/php/admin/index.php" class="card-link" style="text-align: center; padding: 2rem; border: 1px solid var(--border-light); border-radius: 20px;">
                                <i class="fa-solid fa-shield-halved" style="font-size: 2rem; color: var(--sa-accent); margin-bottom: 1rem;"></i>
                                <div style="font-weight: 700;">Security Controls</div>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <?php include __DIR__ . '/../includes/layout/footer.php'; ?>
    </div>
    <script>
(function() {
    var toggle = document.getElementById('sidebarToggle');
    var overlay = document.getElementById('sidebarOverlay');
    if (toggle && overlay) {
        toggle.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-open');
            overlay.classList.toggle('is-open', document.body.classList.contains('sidebar-open'));
            overlay.setAttribute('aria-hidden', document.body.classList.contains('sidebar-open') ? 'false' : 'true');
        });
        overlay.addEventListener('click', function() {
            document.body.classList.remove('sidebar-open');
            overlay.classList.remove('is-open');
            overlay.setAttribute('aria-hidden', 'true');
        });
    }
})();
    </script>
</body>
</html>
'@
$content | Set-Content -Path 'c:\xampp\htdocs\CEDULA\php\auth\dashboard.php' -Encoding utf8
