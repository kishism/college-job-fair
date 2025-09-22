<?php 
session_start();
require './vendor/autoload.php';
require 'helper.php';

$configRegistryPath =  __DIR__ . '/config_registry.json';

if (!file_exists($configRegistryPath)) {
    redirectToError("Configuration registry not found. Please select a configuration first.");
}

$registry = json_decode(file_get_contents($configRegistryPath), true);
if(!$registry || !isset($registry['configs'], $registry['active'])) {
    redirectToError("Invalid or corrupted configuration registry. Please recreate it.");
}

$activeConfig = $registry['active'];
$configs = $registry['configs'];

if (!isset($configs[$activeConfig])) {
    redirectToError("Active configuration not found. Please select a valid configuration.");
}

$activeConfigFile = __DIR__ . '/configs/' . $configs[$activeConfig]['filename'];

if (!file_exists($activeConfigFile)) {
    redirectToError("Active configuration file is missing: {$configs[$activeConfig]['filename']}");
}


$config = include $activeConfigFile;

$client = new MongoDB\Client($config['mongo_uri']);
$collection = $client->{$config['mongo_db']}->users;

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim(strip_tags($_POST["username"]));
    $password = trim($_POST["password"]);

    $user = $collection -> findOne(['username' => $username]);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'username' => $user['username'],
            'role' => $user['role'] ?? 'user'
        ];
        $_SESSION['logged_in'] = true;
        header("Location:/site-login/index.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Job Fair</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            line-height: 1.6;
        }

        /* ====== Header ====== */
        .site-header {
            display: flex;
            align-items: center;
            padding: 0.5rem 2rem;
            background-color: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .site-header .logo {
            height: 115px;
            width: 112px;
        }
        

        .logo-container,
        nav {
            display: flex;
            align-items: center;
        }

        nav {
            padding: 5px 1rem;
            margin-left: 1.5rem; /* adjust to taste */
            display: flex;
            align-items: center;
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            padding-bottom: 2px;
            border-bottom: 2px solid transparent;
            transition: 0.2s;
        }
        .nav-links a:hover,
        .nav-links a.active {
            color: #007bff;
            border-bottom: 2px solid #007bff;
        }

        /* ====== Layout ====== */
        .auth-container {
            display: flex;
            min-height: calc(100vh - 80px); /* minus header */
        }

        /* Left branding section */
        .auth-left {
            flex: 1;
            background-color: #000000;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100%25' height='100%25' viewBox='0 0 800 800'%3E%3Cg %3E%3Ccircle fill='%23000000' cx='400' cy='400' r='600'/%3E%3Ccircle fill='%23230046' cx='400' cy='400' r='500'/%3E%3Ccircle fill='%232f0052' cx='400' cy='400' r='400'/%3E%3Ccircle fill='%233b075e' cx='400' cy='400' r='300'/%3E%3Ccircle fill='%2348156a' cx='400' cy='400' r='200'/%3E%3Ccircle fill='%23552277' cx='400' cy='400' r='100'/%3E%3C/g%3E%3C/svg%3E");
            background-attachment: fixed;
            background-size: cover;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem;
            text-align: center;
        }
        .auth-left img {
            max-width: 300px;
            margin-bottom: 1.5rem;
        }
        .auth-left h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        .auth-left p {
            font-size: 1rem;
            opacity: 0.9;
        }

        /* Right form section */
        .auth-right {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #fff;
            padding: 2rem;
        }
        .login-box {
            width: 100%;
            max-width: 350px;
            padding: 2rem;
            background: white;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            border-radius: 8px;
        }
        h2 {
            text-align: center;
            margin-bottom: 1rem;
        }
        .login-box img {
            width: 80px;
            display: block;
            margin: 0 auto 1rem;
        }
        input[type=text],
        input[type=password] {
            width: 93%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        input[type=submit] {
            background:rgb(255, 180, 68);
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.2s;
        }
        input[type=submit]:hover {
            background:rgb(0, 0, 0);
        }
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .password-wrapper input {
            width: 100%;
            padding-right: 40px;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 1rem;
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 1rem;
            font-size: 0.875rem;
            color: #666;
        }


        @media (max-width: 768px) {
            .auth-container {
                flex-direction: column;
            }
            .auth-left, .auth-right {
                flex: unset;
                width: 100%;
            }
            .auth-left {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>

<header class="site-header">
    <div class="logo-container">
        <img class="logo" src="../assets/ilw1.png" alt="Logo">
    </div>
    <nav>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="login.php" class="active">Login</a>
            <a href="signup.php">Sign Up</a>
        </div>
    </nav>
</header>

<div class="auth-container">
    <!-- Left panel for branding -->
    <div class="auth-left">
        <img class=logo src="../assets/ilw1.png" alt_text="logo"> 
        <h1>Welcome Back!</h1>
        <p> Don't read Camus' books. They are absurd!! </p>
    </div>

    <!-- Right panel for form -->
    <div class="auth-right">
        <div class="login-box">
            <img src="../assets/login-icon-5625771-512.png" alt="Login Icon" />
            <h2>Login to Dashboard</h2>
            <?php if ($error): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required />
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" placeholder="Password" required />
                    <button type="button" class="toggle-password" onclick="togglePassword()">👁️</button>
                </div>
                <input type="submit" value="Login" />
            </form>
            <p style="text-align:center; margin-top: 15px;">
                Don't have an account? <a href="signup.php">Sign up here</a>
            </p>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const pwField = document.getElementById("password");
    const toggleBtn = document.querySelector(".toggle-password");
    if (pwField.type === "password") {
        pwField.type = "text";
        toggleBtn.textContent = "😶‍🌫️"; 
    } else {
        pwField.type = "password";
        toggleBtn.textContent = "👁️"; 
    }
}
</script>

<footer>
    &copy; <?= date('Y') ?> ILU Weekly Portal. Powered by PHP & MongoDB.
</footer>

</body>
</html>

