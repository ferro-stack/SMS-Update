<?php
require_once __DIR__ . '/init.php';

try {
    $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
    if ($id <= 0) {
        sendError('Invalid notification ID.');
    }

    $pdo = getDB();

    $stmtSelect = $pdo->prepare("SELECT * FROM notifications WHERE id = ?");
    $stmtSelect->execute([$id]);
    $notif = $stmtSelect->fetch();

    if ($notif) {
        $title = ($notif['subject'] ?? 'Notification') . ' - ' . ($notif['recipient_name'] ?? '');
        $stmtTrash = $pdo->prepare("INSERT INTO deleted_items (item_type, item_id, title, item_data, deleted_by) VALUES (?, ?, ?, ?, ?)");
        $stmtTrash->execute(['notification', $id, $title, json_encode($notif), 'Registrar Staff']);

        $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ?");
        $stmt->execute([$id]);
    }

    sendJson(['success' => true, 'message' => 'Notification moved to Trash Bin.']);
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}
