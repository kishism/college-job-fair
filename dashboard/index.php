<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../site-login/login.php");
    exit;
}

if (isset($_SESSION['message'])) {
    echo '<div style="background:#e0ffe0; padding:10px; border:1px solid #8bc34a; color:#2e7d32; margin-bottom:15px;">' . htmlspecialchars($_SESSION['message']) . '</div>';
    unset($_SESSION['message']); 
}

include 'header.php';
include 'sidebar.php';
?>

<main style="margin-left: 200px; padding: 20px;">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['user']['username']) ?>!</h1>
    <p>This is your dashboard.</p>
</main>

<?php include 'footer.php'; ?>
