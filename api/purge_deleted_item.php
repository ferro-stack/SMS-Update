<?php
require_once __DIR__ . '/init.php';

try {
    $pdo = getDB();

    $id = $_POST['id'] ?? $_GET['id'] ?? 0;

    if ($id === 'all') {
        $pdo->exec("DELETE FROM deleted_items");
        sendJson(['success' => true, 'message' => 'All items permanently removed from Trash Bin.']);
    } else {
        $itemId = (int)$id;
        if ($itemId <= 0) {
            sendError('Invalid item ID.');
        }
        $stmt = $pdo->prepare("DELETE FROM deleted_items WHERE id = ?");
        $stmt->execute([$itemId]);
        sendJson(['success' => true, 'message' => 'Item permanently removed from Trash Bin.']);
    }
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}
