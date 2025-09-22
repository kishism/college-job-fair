<?php
session_start();

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Optional: start a fresh session only if you want to pass a one-time message
session_start();
$_SESSION['message'] = "You have been logged out successfully.";

// Redirect to login page
header("Location: index.php");
exit;
