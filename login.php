<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $target = ($_SESSION['username'] === 'admin') ? 'admin-dashboard' : 'user-dashboard';
    header("Location: $target");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['is_approved'] == 1) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                // Use JS replace to remove login page from history
                echo "<script>window.location.replace('user-dashboard');</script>";
                exit;
            } else {
                $error = "Your account is pending approval by an admin.";
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – NepalGo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=2.1">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="content-container" style="max-width: 450px; margin-top: 4rem;">
    <div class="section-header">
        <h1>Welcome Back</h1>
        <p>Login to your account to add new routes or manage buses.</p>
    </div>

    <?php if ($error !== ''): ?>
        <div class="status-alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="management-area-box" style="padding: 2.5rem;">
        <form method="POST">
            <div class="form-field-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-input-control" placeholder="yourname@nepalgo.com" required autofocus>
            </div>
            <div class="form-field-group">
                <label>Password</label>
                <input type="password" name="password" class="form-input-control" required>
            </div>
            <button type="submit" class="action-btn primary-btn" style="width: 100%; margin-top: 1rem;">Login</button>
        </form>
        <div style="text-align: center; margin-top: 2rem; border-top: 1px solid #eee; padding-top: 1.5rem;">
            <p style="color: var(--text-muted); font-size: 0.95rem;">Don't have an account?</p>
            <a href="signup" class="action-btn" style="color: var(--primary-color); font-weight: 700; display: inline-block; margin-top: 0.5rem;">Create a Contributor Account</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
<script src="script.js"></script>
</body>
</html>
