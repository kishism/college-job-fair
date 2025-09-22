<?php
function require_login() {
    if (!isset($_SESSION['user'])) {
        $_SESSION['error_message'] = "Access Denied. Please log in first.";
        header("Location: ../site-login/error.php");
        exit;
    }
}

function require_superadmin() {
    require_login();
    if ($_SESSION['user']['role'] !== 'superadmin') {
        $_SESSION['error_message'] = "You do not have permission to change configuration.";
        header("Location: ../site-login/index.php");
        exit;
    }
}
