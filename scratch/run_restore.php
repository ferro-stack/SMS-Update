<?php
require_once __DIR__ . '/../config/db_helper.php';

$pdo = getDB();

$stmt = $pdo->query("SELECT * FROM deleted_items");
$items = $stmt->fetchAll();
echo "Trash count: " . count($items) . "\n";
if (!empty($items)) {
    echo "Item: " . $items[0]['title'] . "\n";
    $_POST['id'] = $items[0]['id'];
    require __DIR__ . '/../api/restore_deleted_item.php';
}
