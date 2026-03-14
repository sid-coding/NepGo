<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="main-navigation">
    <div class="nav-content-wrapper">
        <div class="logo-brand">
            <a href="index.php">NepalGo</a>
        </div>
        <button class="mobile-menu-toggle" onclick="toggleMenu()" aria-label="Toggle navigation">
            ☰
        </button>
        <ul class="nav-menu-list" id="navLinks">
            <li><a href="index.php" class="nav-link-item">Home</a></li>
            <li><a href="search.php" class="nav-link-item">Search</a></li>
            <li><a href="fleet.php" class="nav-link-item">Buses</a></li>
            <li><a href="about.php" class="nav-link-item">About</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="user-dashboard" class="nav-link-item">Dashboard</a></li>
                <li><a href="logout.php" class="nav-link-item">Logout</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
