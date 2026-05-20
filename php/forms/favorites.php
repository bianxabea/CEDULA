<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireRole('consumer');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout_action'])) {
    session_unset();
    session_destroy();
    header('Location: ' . getBaseUrl() . '/php/auth/login.php');
    exit;
}
$basePath = getBasePath(__FILE__);
$baseUrl = getBaseUrl();
$currentPage = 'favorites';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorites - Pizza Crust Delight</title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/serve_asset.php?file=design-system.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/serve_asset.php?file=dashboard.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/serve_asset.php?file=order_food.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/serve_asset.php?file=favorites.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="dashboard-layout consumer-theme-v2">
    <?php $showSidebarToggle = true;
include __DIR__ . '/../includes/layout/navbar.php'; ?>

    <?php include __DIR__ . '/../includes/layout/sidebar.php'; ?>

    <main class="page-content">
            <h1 class="page-title">Favorites</h1>
            <p class="page-subtitle">Your saved items. Search below or reorder quickly with Order again.</p>
            <div class="favorites-toolbar">
                <div class="favorites-search" role="search">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <input type="text" id="favoritesSearch" placeholder="Search by item or restaurant..." autocomplete="off" aria-label="Search favorites">
                </div>
            </div>
            <div id="favoritesList" class="menu-grid favorites-grid"></div>
    </main>
    <?php include __DIR__ . '/../includes/layout/footer.php'; ?>
    <script>window.BASE_URL = '<?php echo $baseUrl; ?>';</script>
    <script src="<?php echo $basePath; ?>js/serve_asset.php?file=favorites.js"></script>
</body>
</html>
