<?php

ini_set('session.cookie_secure', 1);

ini_set('session.cookie_httponly', 1);

ini_set('session.use_only_cookies', 1);

session_start();


if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 180)) {
    error_log("Session timed out for user_id " . ($_SESSION['user_id'] ?? 'unknown'));

    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

$_SESSION['LAST_ACTIVITY'] = time();


$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

if (!isset($_SESSION['USER_AGENT_HASH'])) {
    $_SESSION['USER_AGENT_HASH'] = hash('sha256', $userAgent);
} else {
    if ($_SESSION['USER_AGENT_HASH'] !== hash('sha256', $userAgent)) {
        error_log("Possible session hijack detected for user_id " . ($_SESSION['user_id'] ?? 'unknown'));

        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }
}
