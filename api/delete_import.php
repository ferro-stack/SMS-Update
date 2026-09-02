<?php
require_once __DIR__ . '/init.php';

try {
    $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
    if ($id <= 0) {
        sendError('Invalid import ID.');
    }

    $pdo = getDB();

    $stmtSelect = $pdo->prepare("SELECT * FROM imported_files WHERE id = ?");
    $stmtSelect->execute([$id]);
    $imp = $stmtSelect->fetch();

    if ($imp) {
        $title = ($imp['file_name'] ?? 'Imported File');
        $stmtTrash = $pdo->prepare("INSERT INTO deleted_items (item_type, item_id, title, item_data, deleted_by) VALUES (?, ?, ?, ?, ?)");
        $stmtTrash->execute(['import', $id, $title, json_encode($imp), 'Registrar Staff']);

        $stmt = $pdo->prepare("DELETE FROM imported_files WHERE id = ?");
        $stmt->execute([$id]);
    }

    sendJson(['success' => true, 'message' => 'Imported file record moved to Trash Bin.']);
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}
