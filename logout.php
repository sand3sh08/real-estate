<?php
require_once __DIR__ . '/includes/auth.php';

// Start session if it isn't already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Remove all session variables
$_SESSION = [];

// Delete the session cookie (optional but recommended)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: ' . BASE_URL . '/login.php');
exit;