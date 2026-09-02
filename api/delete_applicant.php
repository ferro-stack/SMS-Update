<?php
require_once __DIR__ . '/init.php';

try {
    $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
    if ($id <= 0) {
        sendError('Invalid applicant ID.');
    }

    $pdo = getDB();

    $stmtSelect = $pdo->prepare("SELECT * FROM applicants WHERE id = ?");
    $stmtSelect->execute([$id]);
    $applicant = $stmtSelect->fetch();

    if ($applicant) {
        $name = trim(($applicant['first_name'] ?? '') . ' ' . ($applicant['last_name'] ?? ''));
        $title = $name ? ($name . ' (' . ($applicant['student_id'] ?? '') . ')') : 'Applicant #' . $id;

        $stmtTrash = $pdo->prepare("INSERT INTO deleted_items (item_type, item_id, title, item_data, deleted_by) VALUES (?, ?, ?, ?, ?)");
        $stmtTrash->execute(['applicant', $id, $title, json_encode($applicant), 'Registrar Staff']);

        $stmt = $pdo->prepare("DELETE FROM applicants WHERE id = ?");
        $stmt->execute([$id]);
    }

    sendJson(['success' => true, 'message' => 'Applicant moved to Trash Bin. Can be reverted anytime.']);
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}
