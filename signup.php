<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $error = "Please fill all required fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Username or Email is already taken.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, is_approved) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashedPassword, 1])) {
                $success = "Registration successful! You can now login to add routes.";
            } else {
                $error = "Something went wrong. Please try again later.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up – NepalGo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=2.1">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="content-container" style="max-width: 450px; margin-top: 4rem;">
    <div class="section-header">
        <h1>Join as a Contributor</h1>
        <p>Create an account to suggest new bus routes and help improve public transport.</p>
    </div>

    <?php if ($error !== ''): ?>
        <div class="status-alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success !== ''): ?>
        <div class="status-alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="management-area-box" style="padding: 2.5rem;">
        <form method="POST">
            <div class="form-field-group">
                <label>Username</label>
                <input type="text" name="username" class="form-input-control" placeholder="Choose a username" required autofocus>
            </div>
            <div class="form-field-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-input-control" placeholder="yourname@example.com" required>
            </div>
            <div class="form-field-group">
                <label>Password</label>
                <input type="password" name="password" class="form-input-control" required>
            </div>
            <div class="form-field-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-input-control" required>
            </div>
            <button type="submit" class="action-btn primary-btn" style="width: 100%; margin-top: 1rem;">Sign Up</button>
        </form>
        <div style="text-align: center; margin-top: 2rem; border-top: 1px solid #eee; padding-top: 1.5rem;">
            <p style="color: var(--text-muted); font-size: 0.95rem;">Already have an account?</p>
            <a href="login" class="action-btn" style="color: var(--primary-color); font-weight: 700; display: inline-block; margin-top: 0.5rem;">Log in here</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
<script src="script.js"></script>
</body>
</html>
