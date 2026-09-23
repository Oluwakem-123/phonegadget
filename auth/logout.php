<?php
require_once dirname(__DIR__) . '/config/database.php';

// Unset all session variables
$_SESSION = array();

// Destroy the session
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

// Redirect to home page
header("Location: " . base_url());
exit;
