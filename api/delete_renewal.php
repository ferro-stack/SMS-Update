<?php
require_once __DIR__ . '/init.php';

try {
    $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
    if ($id <= 0) {
        sendError('Invalid item ID.');
    }

    $pdo = getDB();
    $stmt = $pdo->prepare("DELETE FROM renewal_retention WHERE id = ?");
    $stmt->execute([$id]);

    sendJson(['success' => true, 'message' => 'Scholar renewal entry deleted.']);
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}
