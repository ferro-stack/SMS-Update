<?php
require_once __DIR__ . '/init.php';

try {
    $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
    if ($id <= 0) {
        sendError('Invalid import ID.');
    }

    $pdo = getDB();
    $stmt = $pdo->prepare("DELETE FROM imported_files WHERE id = ?");
    $stmt->execute([$id]);

    sendJson(['success' => true, 'message' => 'Imported file record deleted successfully.']);
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}
