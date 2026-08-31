<?php
require_once __DIR__ . '/init.php';

try {
    $pdo = getDB();

    $stmt = $pdo->query("PRAGMA table_info(applicants)");
    $columns = $stmt->fetchAll();

    echo '<pre>';
    print_r($columns);
    echo '</pre>';

} catch (Exception $e) {
    echo $e->getMessage();
}