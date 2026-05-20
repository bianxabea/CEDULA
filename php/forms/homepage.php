<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/serve_asset.php?file=design-system.css">
    <link rel="stylesheet" href="../../css/serve_asset.php?file=homepage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <title>Pizza Crust Delight - Order Pizza Online</title>
</head>

<body class="public-layout">
    <!-- Premium Background Blobs -->
    <div class="blob-container">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
    </div>

    <?php include __DIR__ . '/../includes/layout/navbar.php'; ?>

    <main class="homepage-main">
        <div class="hero-text reveal-up">
            <div class="badge-tag">HANDCRAFTED TASTE</div>
            <h1>Handcrafted <br><span>Perfection</span></h1>
            <p>Experience the ultimate slice. Wood-fired crust, fresh premium ingredients, and cinematic flavor delivered blazingly fast to your doorstep.</p>

            <div class="hero-features">
                <div class="feature-item">
                    <i class="fa-solid fa-bolt"></i>
                    <span>15m Delivery</span>
                </div>
                <div class="feature-item">
                    <i class="fa-solid fa-award"></i>
                    <span>Top Rated</span>
                </div>
                <div class="feature-item">
                    <i class="fa-solid fa-leaf"></i>
                    <span>Fresh Prep</span>
                </div>
            </div>

            <div class="hero-buttons">
                <a href="../forms/login.php" class="btn btn-primary" style="font-size: 1.1rem; padding: 12px 24px;">Order Now</a>
                <a href="../forms/signup.php" class="btn btn-secondary" style="font-size: 1.1rem; padding: 12px 24px;">Join the Club</a>
            </div>
        </div>

        <div class="hero-image reveal-fade">
            <div class="floating-card stat-1 glass-card float-animation">
                <div class="stat-icon">⭐</div>
                <div class="stat-info">
                    <span class="stat-value">5.0</span>
                    <span class="stat-label">Rating</span>
                </div>
            </div>

            <div class="floating-card stat-2 glass-card float-animation" style="animation-delay: -3s;">
                <div class="stat-icon">🍕</div>
                <div class="stat-info">
                    <span class="stat-value">10k+</span>
                    <span class="stat-label">Orders</span>
                </div>
            </div>

            <img src="../../images/pizza_hero.png" alt="Stunning hyper-realistic pepperoni pizza" class="float-animation" style="animation-duration: 8s;">
        </div>
    </main>

    <?php include __DIR__ . '/../includes/layout/footer.php'; ?>

</body>

</html>