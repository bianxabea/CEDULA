<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
requireRole('superadmin');

// Fetch Stats
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

// Fetch Latest Pending Requests (Limit 5)
$requests = $conn->query("SELECT acr.*, u.firstName as requester_name
                          FROM admin_creation_requests acr
                          LEFT JOIN users u ON acr.requested_by = u.id
                          WHERE acr.status = 'pending'
                          ORDER BY acr.created_at DESC
                          LIMIT 5");

// Fetch Latest Logs (Limit 5)
$logs = $conn->query("SELECT l.*, u.firstName, u.lastName, u.username, u.role
                      FROM login_logs l
                      JOIN users u ON l.user_id = u.id
                      ORDER BY l.log_time DESC
                      LIMIT 5");

$pageTitle = 'Superadmin Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Pizza Crust Delight</title>
    <link rel="stylesheet" href="../../css/design-system.css">
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/superadmin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: var(--bg-card); padding: 1.5rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 1rem; }
        .stat-icon { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white; }
        .bg-blue { background: #3b82f6; }
        .bg-green { background: #10b981; }
        .bg-orange { background: #f59e0b; }
        .bg-purple { background: #8b5cf6; }
        .stat-info h3 { margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--text-heading); }
        .stat-info p { margin: 0; color: var(--text-muted); font-size: 0.9rem; }
    </style>
</head>
<body class="dashboard-layout superadmin-v2">
    <?php include __DIR__ . '/../includes/layout/navbar.php'; ?>

    <!-- Sidebar -->
    <?php $currentPage = 'superadmin_dashboard';
include __DIR__ . '/../includes/layout/sidebar.php'; ?>

    <main class="page-content">
            <div class="sa-dashboard-layout">

                <header style="margin-bottom: 0.5rem;">
                    <h1 class="page-title" style="font-size: 1.75rem; margin-bottom: 0.25rem;">Overview</h1>
                    <p class="page-subtitle text-muted" style="margin: 0; font-size: 0.95rem;">Monitor your ecosystem vitals and security feed.</p>
                </header>

                <!-- Statistics Cards Grid -->
                <section class="sa-stats-grid">
                    <div class="sa-stat-card">
                        <div class="sa-stat-info" style="flex: 1;">
                            <p>Total Nodes</p>
                            <h3><?php echo number_format($stats['restaurants']); ?></h3>
                        </div>
                        <div class="sa-stat-icon">
                            <i class="fa-solid fa-store"></i>
                        </div>
                    </div>

                    <div class="sa-stat-card">
                        <div class="sa-stat-info" style="flex: 1;">
                            <p>Menu Items</p>
                            <h3><?php echo number_format($stats['menu_items']); ?></h3>
                        </div>
                        <div class="sa-stat-icon">
                            <i class="fa-solid fa-utensils"></i>
                        </div>
                    </div>

                    <div class="sa-stat-card">
                        <div class="sa-stat-info" style="flex: 1;">
                            <p>Total Transactions</p>
                            <h3><?php echo number_format($stats['orders_total']); ?></h3>
                        </div>
                        <div class="sa-stat-icon">
                            <i class="fa-solid fa-receipt"></i>
                        </div>
                    </div>

                    <div class="sa-stat-card">
                        <div class="sa-stat-info" style="flex: 1;">
                            <p>Volume Sold</p>
                            <h3><?php echo number_format($stats['items_sold']); ?></h3>
                        </div>
                        <div class="sa-stat-icon">
                            <i class="fa-solid fa-box-open"></i>
                        </div>
                    </div>
                </section>

                <!-- Content Grid (Tables & Charts) -->
                <section class="sa-content-grid">
                    <div style="display: flex; flex-direction: column; gap: 2rem;">

                        <!-- Recent Activity Table -->
                        <article class="sa-box">
                            <header class="sa-box-header">
                                <h2>Pending Access Requests</h2>
                                <a href="requests.php" class="btn-secondary" style="font-size: 0.75rem; padding: 4px 12px;">View All</a>
                            </header>
                            <div class="sa-box-content no-pad">
                                <?php if ($requests->num_rows > 0): ?>
                                    <table class="sa-table">
                                        <thead>
                                            <tr>
                                                <th>Target User</th>
                                                <th>Email</th>
                                                <th>Role Req</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($r = $requests->fetch_assoc()): ?>
                                                <tr>
                                                    <td style="font-weight: 500;"><?php echo htmlspecialchars($r['target_username']); ?></td>
                                                    <td class="text-muted"><?php echo htmlspecialchars($r['target_email']); ?></td>
                                                    <td><span class="status-badge status-pending"><?php echo ucfirst($r['target_role']); ?></span></td>
                                                    <td><a href="requests.php" class="btn-secondary" style="padding: 4px 8px; font-size: 0.75rem;">Review</a></td>
                                                </tr>
                                            <?php
    endwhile; ?>
                                        </tbody>
                                    </table>
                                <?php
else: ?>
                                    <div style="padding: 2rem; text-align: center; color: var(--sa-text-muted);">
                                        <i class="fa-solid fa-check-circle" style="font-size: 2rem; color: var(--sa-success); margin-bottom: 1rem;"></i>
                                        <p style="margin:0; font-weight: 500;">No pending requests.</p>
                                    </div>
                                <?php
endif; ?>
                            </div>
                        </article>

                        <!-- Security Logs Table -->
                        <article class="sa-box">
                            <header class="sa-box-header">
                                <h2>Recent Security Logs</h2>
                                <a href="logs.php" class="btn-secondary" style="font-size: 0.75rem; padding: 4px 12px;">Full Audit</a>
                            </header>
                            <div class="sa-box-content no-pad">
                                <?php if ($logs->num_rows > 0): ?>
                                    <table class="sa-table">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Event Time</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($l = $logs->fetch_assoc()): ?>
                                                <tr>
                                                    <td style="font-weight: 500;">
                                                        <div style="display: flex; align-items: center; gap: 8px;">
                                                            <div style="width: 24px; height: 24px; border-radius: 4px; background: <?php echo $l['action'] === 'login' ? 'var(--sa-success)' : 'var(--sa-danger)'; ?>; color: white; display: flex; align-items: center; justify-content: center; font-size: 0.65rem;">
                                                                <i class="fa-solid <?php echo $l['action'] === 'login' ? 'fa-arrow-right-to-bracket' : 'fa-arrow-right-from-bracket'; ?>"></i>
                                                            </div>
                                                            <?php echo htmlspecialchars($l['username']); ?>
                                                        </div>
                                                    </td>
                                                    <td class="text-muted" style="font-size: 0.8rem;"><?php echo date('M j, Y h:i A', strtotime($l['log_time'])); ?></td>
                                                    <td>
                                                        <span style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: <?php echo $l['action'] === 'login' ? 'var(--sa-success)' : 'var(--sa-danger)'; ?>;">
                                                            <?php echo $l['action']; ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php
    endwhile; ?>
                                        </tbody>
                                    </table>
                                <?php
else: ?>
                                    <div style="padding: 2rem; text-align: center; color: var(--sa-text-muted);">
                                        <p style="margin:0; font-weight: 500;">No security logs found.</p>
                                    </div>
                                <?php
endif; ?>
                            </div>
                        </article>

                    </div>

                    <!-- Right Column -->
                    <aside style="display: flex; flex-direction: column; gap: 2rem;">
                        <article class="sa-box">
                            <header class="sa-box-header">
                                <h2>System Health Metrics</h2>
                            </header>
                            <div class="sa-box-content">
                                <!-- Styled Chart Placeholder -->
                                <div class="sa-chart-placeholder">
                                    <div style="text-align: center;">
                                        <i class="fa-solid fa-chart-line" style="font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.9;"></i>
                                        <h3 style="margin: 0; font-size: 1.1rem; color: #fff;">Traffic Overview</h3>
                                        <p style="margin: 0; font-size: 0.85rem; opacity: 0.75; margin-top: 4px;">Data visualization ready</p>
                                    </div>
                                </div>
                            </div>
                        </article>

                        <!-- Quick Actions -->
                        <article class="sa-box">
                            <header class="sa-box-header">
                                <h2>Quick Actions</h2>
                            </header>
                            <div class="sa-box-content" style="display: flex; flex-direction: column; gap: 0.75rem;">
                                <a href="users.php" class="btn-primary" style="text-align: center; text-decoration: none; display: block;"><i class="fa-solid fa-user-plus" style="margin-right: 8px;"></i> Manually Add User</a>
                                <a href="logs.php" class="btn-secondary" style="width: 100%; text-align: center; text-decoration: none; display: block;"><i class="fa-solid fa-download" style="margin-right: 8px;"></i> Export Login Logs</a>
                            </div>
                        </article>
                    </aside>
                </section>

            </div>
    </main>
    <?php include __DIR__ . '/../includes/layout/footer.php'; ?>
</body>
</html>
