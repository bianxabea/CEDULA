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
$currentPage = 'order_food';
$user = $_SESSION['user'];
$delivery_address = trim(
    ($user['purok'] ?? '') . ', ' .
    ($user['barangay'] ?? '') . ', ' .
    ($user['city'] ?? '') . ', ' .
    ($user['province'] ?? '') . ' ' .
    ($user['zipCode'] ?? '') . ', ' .
    ($user['country'] ?? '')
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Food - Pizza Crust Delight</title>
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/serve_asset.php?file=design-system.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/serve_asset.php?file=dashboard.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/serve_asset.php?file=order_food.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>
<body class="dashboard-layout consumer-theme-v2">

    <?php $showSidebarToggle = true;
include __DIR__ . '/../includes/layout/navbar.php'; ?>

    <?php include __DIR__ . '/../includes/layout/sidebar.php'; ?>

    <main class="page-content" style="min-height: calc(100vh - 70px);">
            <h1 class="page-title">Order Food</h1>
            <p class="page-subtitle">Add items to your cart.</p>
            <div id="restaurantsView" class="view" style="display:none;">
                <p class="muted" style="margin-bottom: 1.5rem;">Select a restaurant below to browse its menu.</p>
                <div id="restaurantsList" class="restaurants-grid"></div>
            </div>
            <div id="menuView" class="view" style="display:none;">
                <button type="button" class="back-btn" id="backToRestaurants">&larr; Back to restaurants</button>
                <h2 id="menuRestaurantName" style="margin-bottom: 1rem;"></h2>
                <div id="menuList" class="menu-grid"></div>
            </div>
            <div id="checkoutView" class="view" style="display:none;">
                <button type="button" class="back-btn" id="backToMenu">&larr; Back to menu</button>
                <h2>Checkout</h2>
                <form id="checkoutForm" class="checkout-form">
                    <div class="form-group">
                        <label for="delivery_address">Delivery address</label>
                        <textarea id="delivery_address" name="delivery_address" rows="3" required><?php echo htmlspecialchars($delivery_address); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="notes">Order notes (optional)</label>
                        <input type="text" id="notes" name="notes" placeholder="e.g. No onions">
                    </div>
                    <div class="form-group">
                        <label>Payment method</label>
                        <div id="paymentMethodsList"></div>
                        <p class="muted small">Or pay Cash on Delivery.</p>
                    </div>
                    <div class="cart-summary">
                        <p><strong>Total: ₱<span id="checkoutTotal">0</span></strong></p>
                        <button type="submit" class="submitBtn">Place Order</button>
                    </div>
                </form>
            </div>
    </main>
    <?php include __DIR__ . '/../includes/layout/footer.php'; ?>
    <script>
        window.BASE_URL = '<?php echo $baseUrl; ?>';
        window.USER_ID = '<?php echo htmlspecialchars($user['id']); ?>';
    </script>
    <script src="<?php echo $basePath; ?>js/serve_asset.php?file=order_food.js"></script>
</body>
</html>
