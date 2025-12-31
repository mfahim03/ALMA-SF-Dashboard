<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Require both login and admin access
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    session_unset();
    session_destroy();
    header('Location: index.php?error=unauthorized');
    exit;
}
?>
