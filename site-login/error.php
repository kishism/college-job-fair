<?php
session_start();
$message = $_SESSION['error_message'] ?? 'An unexpected error occurred.';
$trace = $_SESSION['error_trace'] ?? null;

// Clear to avoid repeating
unset($_SESSION['error_message'], $_SESSION['error_trace']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Error</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; background: #fff; color: #333; }
        .error-box { max-width: 600px; margin: auto; background: #fefefe; border-left: 5px solid #c0392b; padding: 20px; }
        .trace { background: #f9f9f9; padding: 10px; margin-top: 20px; border: 1px solid #ccc; white-space: pre-wrap; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="error-box">
        <h2> Oops, something went wrong!</h2>
        <p><?= htmlspecialchars($message) ?></p>

        <?php if (defined('DEBUG_MODE') && DEBUG_MODE && $trace): ?>
            <div class="trace"><?= htmlspecialchars($trace) ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
