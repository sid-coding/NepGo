<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NepalGo – Smart Bus Finder</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=1.7">
</head>
<body>

<?php include 'navbar.php'; ?>

<!-- Hero Section -->
<header class="home-banner-section">
    <div class="banner-content">
        <h1 class="banner-title">Find Your Way Around Kathmandu</h1>
        <p class="banner-subtitle">The smartest way to find public transport routes. Enter your location and destination to get started.</p>

        <div class="bus-search-area">
            <form class="search-form-wrapper" action="search.php" method="GET">
                <div class="form-input-group">
                    <input type="text" name="from" class="search-input-field" placeholder="Starting point (Kalanki)" autocomplete="off" spellcheck="false" required>
                </div>
                <div class="form-input-group">
                    <input type="text" name="to" class="search-input-field" placeholder="Destination (Ratnapark)" autocomplete="off" spellcheck="false" required>
                </div>
                <button type="submit" class="search-submit-button">Find Bus</button>
            </form>
        </div>
    </div>
</header>

<!-- Features Section -->
<section class="services-section">
    <div class="services-grid-layout">
        <div class="service-info-box">
            <h3 class="service-title">Extensive Routes</h3>
            <p class="service-description">Access a comprehensive network of bus routes covering major areas of Kathmandu.</p>
        </div>
        <div class="service-info-box">
            <h3 class="service-title">Fastest Paths</h3>
            <p class="service-description">Find the quickest routes with the fewest stops to save you time.</p>
        </div>
        <div class="service-info-box">
            <h3 class="service-title">Easy to Use</h3>
            <p class="service-description">Designed for daily commuters. Simple, intuitive, and mobile-friendly.</p>
        </div>
        <div class="service-info-box">
            <h3 class="service-title">Fare Estimation</h3>
            <p class="service-description">Get an instant estimate of your travel cost based on distance and local rates.</p>
        </div>
    </div>
</section>

<!-- Contribution CTA -->
<section style="background-color: #EEF2FF; padding: 5rem 1.5rem; text-align: center;">
    <div style="max-width: 800px; margin: 0 auto;">
        <h2 style="font-size: 2.25rem; color: #312E81; margin-bottom: 1rem;">Know a route we missed?</h2>
        <p style="font-size: 1.125rem; color: #4338CA; margin-bottom: 2.5rem; opacity: 0.8;">Help your fellow commuters by suggesting new bus routes. Become a NepalGo contributor today!</p>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="login" class="action-btn primary-btn" style="padding: 1rem 2.5rem; font-size: 1.1rem; border-radius: 999px;">Get Started</a>
        <?php else: ?>
            <a href="user-dashboard" class="action-btn primary-btn" style="padding: 1rem 2.5rem; font-size: 1.1rem; border-radius: 999px;">Go to Dashboard</a>
        <?php endif; ?>
    </div>
</section>

<?php include 'footer.php'; ?>

<script src="script.js"></script>
</body>
</html>
