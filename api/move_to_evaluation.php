<?php
require_once __DIR__ . '/init.php';

try {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        sendError('Invalid applicant ID.');
    }

    $pdo = getDB();
    $stmt = $pdo->prepare("UPDATE applicants SET status = 'review', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$id]);

    sendJson(['success' => true, 'message' => 'Applicant moved to evaluation successfully.']);
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}
