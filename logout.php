<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Clear session variables
$_SESSION = array();

// 2. Kill the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destroy the session
session_destroy();

// 4. Redirect to home
header('Location: index.php');
exit();
?>
