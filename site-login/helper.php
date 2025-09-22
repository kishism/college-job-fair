<?php

define('DEBUG_MODE', true); // Set to false in production

function redirectToError($message) {
    $_SESSION['error_message'] = $message;

    if (DEBUG_MODE) {
        ob_start();
        debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $_SESSION['error_trace'] = ob_get_clean();
    }

    header("Location: error.php");
    exit;
}
