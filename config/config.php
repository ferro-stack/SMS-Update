<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$scriptDir = preg_replace('#/(pages|api|includes)(/.*)?$|/index\.php$#i', '', $scriptName);
$scriptDir = rtrim($scriptDir, '/');

define('SITE_BASE', $scriptDir);
define('SITE_URL', $protocol . '://' . $host . SITE_BASE);

function checkAuth() {
    if (empty($_SESSION['user_logged_in'])) {
        header("Location: " . SITE_BASE . "/login");
        exit();
    }
}
