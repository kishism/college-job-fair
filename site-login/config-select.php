<?php
session_start();
require_once '../site-login/auth.php';
require_superadmin();

$registryFile = __DIR__ . '/config_registry.json';
$configs = [];
$activeConfig = null;

if (file_exists($registryFile)) {
    $registry = json_decode(file_get_contents($registryFile), true);
    $configs = $registry['configs'] ?? [];
    $activeConfig = $registry['active'] ?? null;
}

// Handle selection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cancel
    if (isset($_POST['cancel_switch'])) {
        unset($_SESSION['pending_config']);
        $_SESSION['message'] = "Config switch canceled.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // First step: request confirmation
    if (isset($_POST['selected_config']) && !isset($_POST['confirm_switch'])) {
        $selected = $_POST['selected_config'];
        if (!isset($configs[$selected])) {
            $_SESSION['error_message'] = "Selected configuration does not exist.";
        } else {
            $_SESSION['pending_config'] = $selected;
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Second step: confirmed switch
    if (isset($_POST['confirm_switch'])) {
        $selected = $_POST['confirm_switch'];
        if (isset($configs[$selected])) {
            $registry['active'] = $selected;
            file_put_contents($registryFile, json_encode($registry, JSON_PRETTY_PRINT));
            unset($_SESSION['pending_config']);

            // Log out immediately
            session_destroy();
            header("Location: /site-login/logout.php");
            exit;
        }
    }
}

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
$error = $_SESSION['error_message'] ?? '';
unset($_SESSION['error_message']);
$pending = $_SESSION['pending_config'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Select Configuration</title>
<style>
    body {
        font-family: 'Inter', sans-serif;
        background: #fafafa;
        color: #111;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        min-height: 100vh;
        padding: 2rem;
    }

    .container {
        background: #fff;
        padding: 2rem;
        border-radius: 8px;
        max-width: 500px;
        width: 100%;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    h1 {
        margin-bottom: 1.5rem;
        font-size: 1.5rem;
        text-align: center;
    }

    .message, .error {
        padding: 0.75rem 1rem;
        margin-bottom: 1rem;
        border-radius: 6px;
        font-size: 0.95rem;
    }
    .message { background: #111; color: #fff; }
    .error { background: #fff; border: 1px solid #111; }

    ul {
        list-style: none;
        padding: 0;
        margin: 0 0 1rem;
    }

    li {
        margin-bottom: 0.5rem;
    }

    label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 0.8rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        cursor: pointer;
    }

    input[type="radio"] {
        accent-color: #111;
    }

    .active {
        font-size: 0.85rem;
        font-weight: bold;
        margin-left: auto;
        color: #111;
    }

    button {
        padding: 0.7rem;
        border: none;
        border-radius: 4px;
        font-size: 1rem;
        cursor: pointer;
        width: 48%;
    }

    .btn-confirm { background: #111; color: #fff; }
    .btn-cancel { background: #eee; color: #111; }

    .btn-row {
        display: flex;
        justify-content: space-between;
        gap: 4%;
    }

    .back-link {
        display: block;
        text-align: center;
        margin-top: 1.5rem;
        font-size: 0.9rem;
        color: #111;
    }
    .back-link:hover { text-decoration: underline; }
</style>
</head>
<body>
<div class="container">
    <h1>Select Configuration</h1>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($pending): ?>
        <div class="message">
            Switch to <strong><?= htmlspecialchars($pending) ?></strong>?
            <form method="POST" style="margin-top:1rem;" class="btn-row">
                <input type="hidden" name="confirm_switch" value="<?= htmlspecialchars($pending) ?>">
                <button type="submit" class="btn-confirm">Yes, Switch</button>
                <button type="submit" name="cancel_switch" class="btn-cancel">Cancel</button>
            </form>
        </div>
    <?php else: ?>
        <form method="POST">
            <ul>
                <?php foreach ($configs as $name => $meta): ?>
                    <li>
                        <label>
                            <input type="radio" name="selected_config" value="<?= htmlspecialchars($name) ?>" 
                                <?= $name === $activeConfig ? 'checked' : '' ?>>
                            <?= htmlspecialchars($name) ?>
                            <?php if ($name === $activeConfig): ?>
                                <span class="active">(Active)</span>
                            <?php endif; ?>
                        </label>
                    </li>
                <?php endforeach; ?>
            </ul>
            <button type="submit" class="btn-confirm" style="width:100%;">Set Active</button>
        </form>
    <?php endif; ?>

    <a class="back-link" href="/site-login/index.php">← Back to Dashboard</a>
</div>
</body>
</html>
