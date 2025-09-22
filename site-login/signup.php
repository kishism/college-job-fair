<?php
session_start();
require './vendor/autoload.php';
require 'helper.php';

$configRegistryPath = __DIR__ . '/config_registry.json';

if (!file_exists($configRegistryPath)) {
    redirectToError("Configuration registry not found. Please select a configuration first.");
}

$registry = json_decode(file_get_contents($configRegistryPath), true);
if (!$registry || !isset($registry['configs'], $registry['active'])) {
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

// MongoDB setup
$client = new MongoDB\Client($config['mongo_uri']);
$db = $client->selectDatabase($config['mongo_db']);
$collection = $db->selectCollection($config['mongo_users_collection'] ?? 'users');

$errors = [];
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? '');
    $password = trim($_POST["password"] ?? '');

    // Validate username
    if (empty($username)) {
        $errors['username'] = 'Username is required';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,25}$/', $username)) {
        $errors['username'] = 'Username must be 3-25 characters and alphanumeric (underscores allowed).';
    }

    // Validate password
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)[A-Za-z\d@$!%*?&]{5,}$/', $password)) {
        $errors['password'] = 'Password must be at least 5 characters with one uppercase letter and one number.';
    }

    // If valid, check MongoDB
    if (empty($errors)) {
        $existingUser = $collection->findOne(['username' => $username]);
        if ($existingUser) {
            $errors['username'] = "Username already exists. Please choose another.";
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert user in MongoDB
            $collection->insertOne([
                'username' => $username,
                'password' => $hashedPassword,
                'role' => 'admin',
            ]);

            // Optionally create local user-data JSON (clean slate)
            $userDataDir = __DIR__ . '/../user-data';
            if (!is_dir($userDataDir)) mkdir($userDataDir, 0755, true);

            $userFilePath = $userDataDir . '/' . $username . '.json';
            $userData = [
                'username' => $username,
                'role' => 'admin',
                'created_at' => date('c')
            ];
            file_put_contents($userFilePath, json_encode($userData, JSON_PRETTY_PRINT));

            $success = "Account created successfully. <a href='login.php'>Log in now</a>.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sign Up - Job Fair</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
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
            min-height: calc(100vh - 80px);
        }

        /* Left form section */
        .auth-left {
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
        .login-box img {
            width: 80px;
            display: block;
            margin: 0 auto 1rem;
        }
        h2 {
            text-align: center;
            margin-bottom: 1rem;
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
        .error { color: red; }
        .success { color: green; }

        /* Right branding section */
        .auth-right {
            flex: 1;
            background-color: #330033;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='613' height='613' viewBox='0 0 800 800'%3E%3Cg fill='none' stroke='%23404' stroke-width='1'%3E%3Cpath d='M769 229L1037 260.9M927 880L731 737 520 660 309 538 40 599 295 764 126.5 879.5 40 599-197 493 102 382-31 229 126.5 79.5-69-63'/%3E%3Cpath d='M-31 229L237 261 390 382 603 493 308.5 537.5 101.5 381.5M370 905L295 764'/%3E%3Cpath d='M520 660L578 842 731 737 840 599 603 493 520 660 295 764 309 538 390 382 539 269 769 229 577.5 41.5 370 105 295 -36 126.5 79.5 237 261 102 382 40 599 -69 737 127 880'/%3E%3Cpath d='M520-140L578.5 42.5 731-63M603 493L539 269 237 261 370 105M902 382L539 269M390 382L102 382'/%3E%3Cpath d='M-222 42L126.5 79.5 370 105 539 269 577.5 41.5 927 80 769 229 902 382 603 493 731 737M295-36L577.5 41.5M578 842L295 764M40-201L127 80M102 382L-261 269'/%3E%3C/g%3E%3Cg fill='%23505'%3E%3Ccircle cx='769' cy='229' r='10'/%3E%3Ccircle cx='539' cy='269' r='10'/%3E%3Ccircle cx='603' cy='493' r='10'/%3E%3Ccircle cx='731' cy='737' r='10'/%3E%3Ccircle cx='520' cy='660' r='10'/%3E%3Ccircle cx='309' cy='538' r='10'/%3E%3Ccircle cx='295' cy='764' r='10'/%3E%3Ccircle cx='40' cy='599' r='10'/%3E%3Ccircle cx='102' cy='382' r='10'/%3E%3Ccircle cx='127' cy='80' r='10'/%3E%3Ccircle cx='370' cy='105' r='10'/%3E%3Ccircle cx='578' cy='42' r='10'/%3E%3Ccircle cx='237' cy='261' r='10'/%3E%3Ccircle cx='390' cy='382' r='10'/%3E%3C/g%3E%3C/svg%3E");
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem;
            text-align: center;
        }
        .auth-right img {
            max-width: 600px;
            margin-bottom: 1.5rem;
        }
        .auth-right h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        .auth-right p {
            font-size: 1rem;
            opacity: 0.9;
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 1rem;
            font-size: 0.875rem;
            color: #666;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .auth-container {
                flex-direction: column-reverse;
            }
            .auth-left, .auth-right {
                width: 100%;
            }
            .auth-right {
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
            <a href="login.php">Login</a>
            <a href="signup.php" class="active">Sign Up</a>
        </div>
    </nav>
</header>

<div class="auth-container">
    <div class="auth-left">
        <div class="login-box">
            <img src="../assets/form-icon-137131-512.png" alt="Sign Up Icon" />
            <h2>Create an Account</h2>

            <?php if (!empty($errors)): ?>
                <ul class="error-list">
                    <?php foreach ($errors as $error): ?>
                        <li class="error"><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <p class="success"><?= $success ?></p>
            <?php endif; ?>

            <form method="POST">
                <input type="text" name="username" placeholder="Username" required />
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" placeholder="Password" required />
                    <button type="button" class="toggle-password" onclick="togglePassword()">👁️</button>
                </div>  
                <input type="submit" value="Sign Up" />
            </form>
            <p style="text-align:center; margin-top: 15px;">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </div>
    </div>

    <div class="auth-right">
        <img class=logo src="../assets/ilw1.png" alt_text="logo"> 
        <h1> Welcome to ILU Weekly </h1>
        <p> Sign up and create an account to access to dashboard. </p>
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
