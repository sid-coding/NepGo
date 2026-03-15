<?php
// Starting the session so we can keep the user logged in as they browse
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Connecting to the database
require_once 'config.php';

$error = '';

// Checking if the login form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Making sure the user didn't leave any fields empty
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        // Looking for the user in the database using their email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifying if the user exists and the password is correct
        if ($user && password_verify($password, $user['password'])) {
            // Only let them in if an admin has approved their account
            if ($user['is_approved'] == 1) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                header('Location: user-dashboard');
                exit;
            } else {
                $error = "Your account is pending approval by an admin.";
            }
        } else {
            // Show this if the email or password doesn't match
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

    <!-- Showing any login errors here -->
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
