<?php
/**
 * Admin - Manage Users (AMORA-style resource page)
 * List users; request deletion (approval flow).
 */
require_once __DIR__ . '/../../../../includes/auth_check.php';
require_once __DIR__ . '/../../../../includes/path_helper.php';

if (($user['role'] ?? '') !== 'admin' && ($user['role'] ?? '') !== 'superadmin') {
    header('Location: ' . getBaseUrl() . '/php/auth/dashboard.php');
    exit;
}

$basePath = getBasePath(__FILE__);
$currentPage = 'users';
$pageTitle = 'Manage Users';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - Pizza Crust Delight</title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/serve_asset.php?file=design-system.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/serve_asset.php?file=dashboard.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/serve_asset.php?file=management.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include __DIR__ . '/../../../../includes/layout/navbar.php'; ?>
    <div class="dashboard-container">
        <?php include __DIR__ . '/../../../../includes/layout/sidebar.php'; ?>
        <main class="dashboard-main">
            <div class="page-header">
                <h1><i class="fa-solid fa-users"></i> Manage Users</h1>
                <p>View users and submit deletion requests for superadmin review</p>
            </div>
            <div class="users-toolbar">
                <div class="search-container">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search by ID, name, email, username..." value="">
                </div>
                <div class="toolbar-actions">
                    <a href="../approvals.php" class="btn-secondary" style="padding:0.5rem 1rem;border-radius:8px;text-decoration:none;background:var(--primary-color);color:#fff;font-weight:600;">
                        <i class="fa-solid fa-clipboard-list"></i> My Requests
                    </a>
                    <span class="users-count" id="usersCount">Loading...</span>
                </div>
            </div>
            <div id="users-container" class="users-container">
                <div class="loading-state">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    <p>Loading users...</p>
                </div>
            </div>
            <div id="pagination" class="pagination"></div>
        </main>
    </div>
    <?php include __DIR__ . '/../../../../includes/layout/notification_modal.php'; ?>
    <?php include __DIR__ . '/../../../../includes/layout/request_delete_modal.php'; ?>
    <script>
        window.API_BASE = '<?php echo getBaseUrl(); ?>/php/database/';
        var BASE_PATH = '<?php echo $basePath; ?>';
    </script>
    <script src="<?php echo $basePath; ?>js/serve_asset.php?file=admin_users_management.js"></script>
    <?php include __DIR__ . '/../../../../includes/layout/footer.php'; ?>
</body>
</html>
