<?php
require_once __DIR__ . '/config/config.php';

$requestUri = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$scriptDir = preg_replace('#/(pages|api|includes)(/.*)?$|/index\.php$#i', '', $scriptName);
$scriptDir = rtrim($scriptDir, '/');

$path = $requestUri;
if (!empty($scriptDir) && strpos($requestUri, $scriptDir) === 0) {
    $path = substr($requestUri, strlen($scriptDir));
}
$path = trim($path, '/');

if ($path === 'login') {
    require __DIR__ . '/pages/login.php';
    exit();
}

if ($path === '' || $path === 'index.php' || $path === 'dashboard') {
    require __DIR__ . '/pages/dashboard.php';
    exit();
}

$pageFile = __DIR__ . '/pages/' . $path . '.php';
if (file_exists($pageFile)) {
    require $pageFile;
    exit();
}

require __DIR__ . '/pages/dashboard.php';

