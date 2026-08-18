<?php
// Database Driver Config ('sqlite' | 'mysql')
define('DB_DRIVER', 'sqlite');
define('SQLITE_PATH', __DIR__ . '/../database/scholarship.sqlite');

/**
 * Returns a PDO SQLite connection instance when needed.
 */
function getSQLiteConnection(string $path = SQLITE_PATH): PDO {
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $pdo = new PDO("sqlite:" . $path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec("PRAGMA foreign_keys = ON;");
    return $pdo;
}

// MySQL fallback connection (uncomment if switching back to MySQL)
/*
$host = "localhost";
$user = "root";
$password = "";
$db = "applicant_db";

$conn = new mysqli($host, $user, $password, $db);
if ($conn->connect_error) {
    die("Connection Failed");
}
*/