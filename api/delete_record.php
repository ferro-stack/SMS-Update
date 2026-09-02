<?php
require_once __DIR__ . '/init.php';

try {
    $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
    if ($id <= 0) {
        sendError('Invalid record ID.');
    }

    $pdo = getDB();

    $stmtSelect = $pdo->prepare("SELECT * FROM records WHERE id = ?");
    $stmtSelect->execute([$id]);
    $rec = $stmtSelect->fetch();

    if ($rec) {
        $title = ($rec['name'] ?? 'Record') . ' (' . ($rec['student_id'] ?? '') . ')';
        $stmtTrash = $pdo->prepare("INSERT INTO deleted_items (item_type, item_id, title, item_data, deleted_by) VALUES (?, ?, ?, ?, ?)");
        $stmtTrash->execute(['record', $id, $title, json_encode($rec), 'Registrar Staff']);

        $stmt = $pdo->prepare("DELETE FROM records WHERE id = ?");
        $stmt->execute([$id]);
    }

    sendJson(['success' => true, 'message' => 'Record moved to Trash Bin.']);
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}
