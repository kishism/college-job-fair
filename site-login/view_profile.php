<?php
session_start();
require_once '../site-login/auth.php';
require_login(); // Make sure the user is logged in

$username = $_SESSION['username'] ?? 'Unknown User';
$email = $_SESSION['email'] ?? 'user@example.com';
$role = $_SESSION['role'] ?? 'User';
$created_at = $_SESSION['created_at'] ?? '2025-08-16';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Profile</title>
<style>
    body {
    font-family: 'Inter', 'Helvetica Neue', Helvetica, Arial, sans-serif;
    background: #fafafa;
    color: #111;
    margin: 0;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* Header */
.profile-header {
    background: #111;
    color: #fff;
    padding: 2.5rem 3rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.avatar {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background: #fff;
    color: #111;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: bold;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.25);
}

.username {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.2rem;
}

.role {
    font-size: 1rem;
    font-weight: 500;
    opacity: 0.85;
    letter-spacing: 0.3px;
}

/* Profile details container */
.profile-details {
    flex: 1;
    padding: 3rem;
    background: #fff;
    border-top: 1px solid #e5e5e5;
    max-width: 900px;
}

/* Section titles */
.detail-section {
    margin-bottom: 2.5rem;
}

.section-title {
    font-size: 0.9rem;
    font-weight: 700;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 1rem;
    border-left: 4px solid #111;
    padding-left: 0.75rem;
}

/* List items */
.detail-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 0;
    border-bottom: 1px solid #eee;
    transition: background 0.2s ease;
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-label {
    font-size: 0.95rem;
    font-weight: 600;
    opacity: 0.6;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-value {
    font-size: 1.05rem;
    font-weight: 500;
    color: #111;
}

.detail-item:hover {
    background: #f9f9f9;
}

.detail-value::after {
    content: "⋯";
    font-size: 1.2rem;
    opacity: 0;
    margin-left: 0.5rem;
    transition: opacity 0.2s ease;
}

.detail-item:hover .detail-value::after {
    opacity: 0.3;
}

/* Back link */
.back-link {
    display: inline-block;
    margin: 2rem 3rem;
    font-weight: 500;
    color: #111;
    opacity: 0.8;
    transition: opacity 0.2s ease;
}
.back-link:hover { 
    text-decoration: underline; 
    opacity: 1; 
}


</style>
</head>
<body>
<div class="profile-header">
    <div class="header-left">
        <div class="avatar">JD</div>
        <div>
            <div class="username">John Doe</div>
            <div class="role">Superadmin</div>
        </div>
    </div>
</div>

<div class="profile-details">
    <div class="detail-section">
        <div class="section-title">Account Info</div>
        <ul class="detail-list">
            <li class="detail-item">
                <span class="detail-label">Username</span>
                <span class="detail-value">johndoe</span>
            </li>
            <li class="detail-item">
                <span class="detail-label">Email</span>
                <span class="detail-value">john@example.com</span>
            </li>
        </ul>
    </div>

    <div class="detail-section">
        <div class="section-title">Security</div>
        <ul class="detail-list">
            <li class="detail-item">
                <span class="detail-label">Password</span>
                <span class="detail-value">••••••••</span>
            </li>
            <li class="detail-item">
                <span class="detail-label">Last Login</span>
                <span class="detail-value">2025-08-16 12:30</span>
            </li>
        </ul>
    </div>

    <div class="detail-section">
        <div class="section-title">Preferences</div>
        <ul class="detail-list">
            <li class="detail-item">
                <span class="detail-label">Theme</span>
                <span class="detail-value">Dark</span>
            </li>
            <li class="detail-item">
                <span class="detail-label">Language</span>
                <span class="detail-value">English</span>
            </li>
        </ul>
    </div>
</div>

<a class="back-link" href="/site-login/index.php">← Back to Dashboard</a>

</body>
</html>
