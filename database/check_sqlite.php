<?php

$db = __DIR__ . '/scholarship.sqlite';

try {
    $pdo = new PDO('sqlite:' . $db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2>SQLite Database Check</h2>";

    echo "<p><strong>Database:</strong> " . htmlspecialchars($db) . "</p>";

    echo "<h3>Integrity Check</h3>";

    $result = $pdo->query("PRAGMA integrity_check")->fetchColumn();

    echo "<pre>";
    echo htmlspecialchars($result);
    echo "</pre>";

    echo "<h3>Tables</h3>";

    $tables = $pdo->query("
        SELECT name
        FROM sqlite_master
        WHERE type = 'table'
        ORDER BY name
    ")->fetchAll(PDO::FETCH_COLUMN);

    echo "<pre>";
    print_r($tables);
    echo "</pre>";

} catch (Exception $e) {
    echo "<h2>Database Error</h2>";
    echo "<pre>";
    echo htmlspecialchars($e->getMessage());
    echo "</pre>";
}