<?php
require_once __DIR__ . '/init.php';

try {
    $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
    if ($id <= 0) {
        sendError('Invalid scholarship ID.');
    }

    $pdo = getDB();

    $stmtSelect = $pdo->prepare("SELECT * FROM scholarships WHERE id = ?");
    $stmtSelect->execute([$id]);
    $sch = $stmtSelect->fetch();

    if ($sch) {
        $title = ($sch['name'] ?? 'Scholarship') . ' (' . ($sch['code'] ?? '') . ')';
        $stmtTrash = $pdo->prepare("INSERT INTO deleted_items (item_type, item_id, title, item_data, deleted_by) VALUES (?, ?, ?, ?, ?)");
        $stmtTrash->execute(['scholarship', $id, $title, json_encode($sch), 'Registrar Staff']);

        $stmt = $pdo->prepare("DELETE FROM scholarships WHERE id = ?");
        $stmt->execute([$id]);
    }

    sendJson(['success' => true, 'message' => 'Scholarship program moved to Trash Bin.']);
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}
