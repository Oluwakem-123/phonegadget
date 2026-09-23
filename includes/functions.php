<?php
// Core helper functions
require_once __DIR__ . '/notifications.php';
require_once __DIR__ . '/favorites.php';
require_once __DIR__ . '/admin.php';

/**
 * Sanitize output for HTML context to prevent XSS
 */
function h($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a specific URL and exit
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Get full base URL of the application
 */
function base_url($path = '') {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $base_dir = '/phone_marketplace'; // Ensure this matches the installation folder
    return $protocol . $host . $base_dir . '/' . ltrim($path, '/');
}
