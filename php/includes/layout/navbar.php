<?php
/**
 * Navbar: fixed at top, full width. Expects $basePath, $baseUrl (or getBaseUrl()).
 * Shows cart icon (with badge) for consumers when $userRole === 'consumer'.
 */
if (!isset($basePath)) {
    $basePath = isset($base) ? rtrim($base, '/') . '/' : '../../';
}
if (!function_exists('getBaseUrl') && !isset($baseUrl)) {
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/CEDULA';
}
if (!isset($baseUrl)) {
    $baseUrl = getBaseUrl();
}
$navUserRole = $userRole ?? ($_SESSION['user']['role'] ?? '');
$showCart = ($navUserRole === 'consumer');
?>
<header class="navbar" role="banner">
    <a href="<?php echo htmlspecialchars($baseUrl); ?>/php/auth/dashboard.php" class="navbar-brand">
        <i class="fa-solid fa-pizza-slice" style="color: var(--primary-color);"></i>
        Pizza Crust Delight
    </a>

    <nav class="nav-links">
        <?php if ($navUserRole || isset($_SESSION['user'])): ?>
            <?php if ($showCart): ?>
            <a href="<?php echo htmlspecialchars($baseUrl); ?>/php/forms/cart.php" class="nav-cart-link" id="navCartLink" title="Cart">
                <i class="fa-solid fa-cart-shopping" aria-hidden="true"></i>
                <span class="nav-cart-count" id="navbarCartCount">0</span>
            </a>
            <?php
    endif; ?>

            <?php if (!empty($showSidebarToggle)): ?>
            <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Open menu" aria-expanded="false" style="background:transparent; border:none; color:var(--text-main); font-size:1.5rem; cursor:pointer; margin-right:10px;">
                <i class="fa-solid fa-bars" aria-hidden="true"></i>
            </button>
            <?php
    endif; ?>

            <a href="<?php echo htmlspecialchars($baseUrl); ?>/php/auth/logout.php" class="nav-link text-muted" style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-right-from-bracket"></i> Log Out
            </a>
        <?php
else: ?>
            <?php $currentPage = basename($_SERVER['SCRIPT_NAME']); ?>
            <?php if ($currentPage !== 'homepage.php'): ?>
                <a href="<?php echo htmlspecialchars($baseUrl); ?>/php/forms/homepage.php" class="nav-link">Home</a>
            <?php
    endif; ?>
            <?php if ($currentPage !== 'login.php'): ?>
                <a href="<?php echo htmlspecialchars($baseUrl); ?>/php/forms/login.php" class="nav-link">Login</a>
            <?php
    endif; ?>
            <?php if ($currentPage !== 'signup.php'): ?>
                <a href="<?php echo htmlspecialchars($baseUrl); ?>/php/forms/signup.php" class="btn btn-primary">Register</a>
            <?php
    endif; ?>
        <?php
endif; ?>
    </nav>
</header>
<?php if ($showCart): ?>
<script>
(function() {
    function updateNavCartBadge() {
        var el = document.getElementById('navbarCartCount');
        if (!el) return;
        fetch('<?php echo htmlspecialchars($baseUrl); ?>/php/database/cart_get.php')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var count = 0;
                if (data.success && data.items) {
                    data.items.forEach(function(i) { count += (i.quantity || 0); });
                }
                el.textContent = count;
                el.classList.toggle('nav-cart-count--zero', count === 0);
            })
            .catch(function() { el.textContent = '0'; el.classList.add('nav-cart-count--zero'); });
    }
    updateNavCartBadge();
    window.updateNavCartBadge = updateNavCartBadge;
})();
</script>
<?php
endif; ?>
