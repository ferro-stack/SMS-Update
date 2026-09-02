<?php
require_once __DIR__ . '/init.php';

try {
    $pdo = getDB();

    $department = trim($_GET['department'] ?? '');
    $yearLevel = (int)($_GET['year_level'] ?? $_GET['year'] ?? 0);
    $status = trim($_GET['status'] ?? '');
    $search = trim($_GET['search'] ?? '');

    $sql = "SELECT * FROM scholars WHERE 1=1";
    $params = [];

    if (!empty($department) && strtolower($department) !== 'all') {
        $sql .= " AND LOWER(department) = LOWER(?)";
        $params[] = $department;
    }

    if ($yearLevel > 0) {
        $sql .= " AND year_level = ?";
        $params[] = $yearLevel;
    }

    if (empty($status) || strtolower($status) === 'all') {
        $status = 'above';
    }

    if (strtolower($status) === 'above' || strtolower($status) === 'active') {
        $sql .= " AND (gwa <= 1.50 OR LOWER(status) = 'active')";
    } else if (strtolower($status) === 'below' || strtolower($status) === 'removed') {
        $sql .= " AND (gwa > 1.50 OR LOWER(status) = 'removed')";
    }

    if (!empty($search)) {
        $sql .= " AND (LOWER(name) LIKE LOWER(?) OR LOWER(student_id) LIKE LOWER(?))";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }

    $sql .= " ORDER BY id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $scholars = $stmt->fetchAll();

    // Dynamically ensure GWA maintenance logic flag
    foreach ($scholars as &$s) {
        $gwa = (float)($s['gwa'] ?? 0);
        $s['maintainsGrade'] = ($gwa <= 1.50);
        $s['thresholdRequirement'] = 1.50;
    }

    sendJson(['success' => true, 'data' => $scholars]);
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}
