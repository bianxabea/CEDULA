<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');

// Fetch Stats (Global stats as requested)
require_once __DIR__ . '/../database/db_connect.php';

$stats = [
    'restaurants' => 0,
    'menu_items' => 0,
    'orders_total' => 0,
    'orders_pending' => 0,
    'items_sold' => 0
];

// Restaurants
$res = $conn->query("SELECT COUNT(*) as n FROM restaurants");
if ($row = $res->fetch_assoc())
    $stats['restaurants'] = $row['n'];

// Menu Items
$res = $conn->query("SELECT COUNT(*) as n FROM menu_items");
if ($row = $res->fetch_assoc())
    $stats['menu_items'] = $row['n'];

// Orders
$res = $conn->query("SELECT COUNT(*) as n FROM orders");
if ($row = $res->fetch_assoc())
    $stats['orders_total'] = $row['n'];

// Pending Orders
$res = $conn->query("SELECT COUNT(*) as n FROM orders WHERE status = 'pending'");
if ($row = $res->fetch_assoc())
    $stats['orders_pending'] = $row['n'];

// Items Sold
$res = $conn->query("SELECT SUM(quantity) as n FROM order_items");
if ($row = $res->fetch_assoc())
    $stats['items_sold'] = $row['n'] ?? 0;

// Fetch My Recent Requests (Limit 5)
$myRequests = $conn->query("SELECT acr.*
                            FROM admin_creation_requests acr
                            WHERE acr.requested_by = " . $_SESSION['user']['id'] . "
                            ORDER BY acr.created_at DESC
                            LIMIT 5");

// Fetch Recent Orders (Limit 5)
$recentOrders = $conn->query("SELECT o.id, o.total_amount, o.status, o.created_at, u.username
                              FROM orders o
                              JOIN users u ON o.user_id = u.id
                              ORDER BY o.created_at DESC
                              LIMIT 5");

$pageTitle = 'Admin Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Pizza Crust Delight</title>
    <link rel="stylesheet" href="../../css/design-system.css">
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/serve_asset.php?file=admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: var(--bg-card); padding: 1.5rem; border-radius: 12px; box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 1rem; transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-2px); }
        .stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white; }
        .bg-blue { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .bg-green { background: linear-gradient(135deg, #10b981, #059669); }
        .bg-orange { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .bg-purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .stat-info h3 { margin: 0; font-size: 1.75rem; font-weight: 800; color: var(--text-heading); letter-spacing: -0.5px; }
        .stat-info p { margin: 0; color: var(--text-muted); font-size: 0.85rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }

        .widget-card { background: var(--bg-card); padding: 1.5rem; border-radius: 12px; box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
        .section-title { margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--text-heading); display: flex; align-items: center; }
        .widget-icon { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; background: #eff6ff; border-radius: 8px; color: var(--primary-color); margin-right: 0.75rem; font-size: 1rem; }
        .view-all { font-size: 0.85rem; color: var(--primary-color); font-weight: 600; text-decoration: none; }
        .view-all:hover { text-decoration: underline; }
    </style>
</head>
<body class="dashboard-layout">
    <?php include __DIR__ . '/../includes/layout/navbar.php'; ?>

    <?php $currentPage = 'admin_dashboard';
include __DIR__ . '/../includes/layout/sidebar.php'; ?>

    <main class="page-content">
        <!-- Island Header -->
            <div class="island-header reveal-up">
                <div class="island-welcome">
                    <h1 class="page-title">Admin Dashboard</h1>
                    <p class="page-subtitle" style="margin-bottom: 0;">Store operations and order fulfillment.</p>
                </div>
                <div class="island-actions">
                    <a href="orders.php" class="btn-primary" style="padding: 1rem 2rem;">
                        <i class="fa-solid fa-receipt" style="margin-right: 8px;"></i> View Orders
                    </a>
                </div>
            </div>

            <div class="bento-grid">
                <!-- Vitals Bento Items -->
                <div class="bento-item stat-card reveal-up">
                    <div class="stat-icon bg-blue"><i class="fa-solid fa-store"></i></div>
                    <div class="stat-info">
                        <h3 class="stat-value" style="font-size: 2rem;"><?php echo $stats['restaurants']; ?></h3>
                        <p class="stat-label">Restaurants</p>
                    </div>
                </div>
                <div class="bento-item stat-card reveal-up" style="animation-delay: 0.1s;">
                    <div class="stat-icon bg-green"><i class="fa-solid fa-utensils"></i></div>
                    <div class="stat-info">
                        <h3 class="stat-value" style="font-size: 2rem;"><?php echo $stats['menu_items']; ?></h3>
                        <p class="stat-label">Menu Items</p>
                    </div>
                </div>
                <div class="bento-item stat-card reveal-up" style="animation-delay: 0.2s;">
                    <div class="stat-icon bg-orange"><i class="fa-solid fa-receipt"></i></div>
                    <div class="stat-info">
                        <h3 class="stat-value" style="font-size: 2rem;"><?php echo $stats['orders_total']; ?></h3>
                        <p class="stat-label">Total Orders</p>
                    </div>
                </div>
                <div class="bento-item stat-card reveal-up" style="animation-delay: 0.3s;">
                    <div class="stat-icon bg-purple"><i class="fa-solid fa-box-open"></i></div>
                    <div class="stat-info">
                        <h3 class="stat-value" style="font-size: 2rem;"><?php echo $stats['items_sold']; ?></h3>
                        <p class="stat-label">Items Sold</p>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="bento-item span-2 row-2 reveal-up" style="animation-delay: 0.4s;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <h2 class="section-title" style="margin:0;">Recent Orders</h2>
                        <a href="orders.php" style="font-size: 0.85rem; color: var(--primary-color); text-decoration: none; font-weight: 600;">View All <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                    <?php if ($recentOrders && $recentOrders->num_rows > 0): ?>
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.95rem;">
                            <tbody>
                                <?php while ($o = $recentOrders->fetch_assoc()): ?>
                                    <tr style="border-bottom: 1px dashed var(--border-color);">
                                        <td style="padding: 1rem 0;">
                                            <div style="font-weight: 700; color: var(--text-heading); font-family: monospace;">#<?php echo str_pad($o['id'], 6, '0', STR_PAD_LEFT); ?></div>
                                            <div style="font-size: 0.85rem; color: var(--text-muted);"><?php echo htmlspecialchars($o['username']); ?></div>
                                        </td>
                                        <td style="padding: 1rem 0; font-weight: 800; color: #16a34a;">
                                            ₱<?php echo number_format($o['total_amount'], 2); ?>
                                        </td>
                                        <td style="padding: 1rem 0; text-align: right;">
                                            <?php
        $sClass = 'status-pending';
        $sColor = '#d97706';
        $sBg = 'rgba(245, 158, 11, 0.15)';
        if ($o['status'] === 'completed' || $o['status'] === 'delivered') {
            $sColor = '#10b981';
            $sBg = 'rgba(16, 185, 129, 0.15)';
        }
        if ($o['status'] === 'cancelled') {
            $sColor = '#ef4444';
            $sBg = 'rgba(239, 68, 68, 0.15)';
        }
?>
                                            <span style="background: <?php echo $sBg; ?>; color: <?php echo $sColor; ?>; padding: 4px 10px; border-radius: 99px; font-size: 0.75rem; font-weight: 800;"><?php echo ucfirst($o['status']); ?></span>
                                        </td>
                                    </tr>
                                <?php
    endwhile; ?>
                            </tbody>
                        </table>
                    <?php
else: ?>
                        <div class="empty-state" style="border: none; padding: 1rem;"><p class="muted">No recent orders.</p></div>
                    <?php
endif; ?>
                </div>

                <!-- My Pending Requests -->
                <div class="bento-item span-2 row-2 reveal-up" style="animation-delay: 0.5s;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <h2 class="section-title" style="margin:0;">My Pending Requests</h2>
                        <a href="requests.php" style="font-size: 0.85rem; color: var(--primary-color); text-decoration: none; font-weight: 600;">View All <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                    <?php if ($myRequests && $myRequests->num_rows > 0): ?>
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <?php while ($r = $myRequests->fetch_assoc()): ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 1rem; border-bottom: 1px dashed var(--border-color);">
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <div style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: rgba(59, 130, 246, 0.15); color: #3b82f6;">
                                            <i class="fa-solid fa-user-clock"></i>
                                        </div>
                                        <div>
                                            <div style="font-weight: 700; color: var(--text-heading);"><?php echo htmlspecialchars($r['target_username']); ?></div>
                                            <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo date('M j, Y', strtotime($r['created_at'])); ?></div>
                                        </div>
                                    </div>
                                    <?php
        $statusClass = 'status-pending';
        if ($r['status'] === 'approved')
            $statusClass = 'status-ok';
        if ($r['status'] === 'rejected')
            $statusClass = 'status-trash';
?>
                                    <span class="status-badge no-dot <?php echo $statusClass; ?>"><?php echo ucfirst($r['status']); ?></span>
                                </div>
                            <?php
    endwhile; ?>
                        </div>
                    <?php
else: ?>
                        <div class="empty-state" style="border: none; padding: 1rem;"><p class="muted">No pending requests.</p></div>
                    <?php
endif; ?>
                </div>
            </div>
    </main>

    <?php include __DIR__ . '/../includes/layout/footer.php'; ?>
</body>
</html>
