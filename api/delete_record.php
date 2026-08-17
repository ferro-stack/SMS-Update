<?php
require_once __DIR__ . '/init.php';

try {
    $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
    if ($id <= 0) {
        sendError('Invalid record ID.');
    }

    $pdo = getDB();
    $stmt = $pdo->prepare("DELETE FROM records WHERE id = ?");
    $stmt->execute([$id]);

    sendJson(['success' => true, 'message' => 'Record deleted successfully.']);
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}
