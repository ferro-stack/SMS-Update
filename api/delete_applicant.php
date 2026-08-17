<?php
require_once __DIR__ . '/init.php';

try {
    $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
    if ($id <= 0) {
        sendError('Invalid applicant ID.');
    }

    $pdo = getDB();
    $stmt = $pdo->prepare("DELETE FROM applicants WHERE id = ?");
    $stmt->execute([$id]);

    sendJson(['success' => true, 'message' => 'Applicant deleted successfully.']);
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}
