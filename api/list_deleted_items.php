<?php
require_once __DIR__ . '/init.php';

try {
    $pdo = getDB();

    $type = trim($_GET['type'] ?? '');
    $sql = "SELECT * FROM deleted_items WHERE 1=1";
    $params = [];

    if (!empty($type) && strtolower($type) !== 'all') {
        $sql .= " AND LOWER(item_type) = LOWER(?)";
        $params[] = $type;
    }

    $sql .= " ORDER BY deleted_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    sendJson(['success' => true, 'data' => $items]);
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}
