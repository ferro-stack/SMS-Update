<?php
require_once __DIR__ . '/init.php';

try {
    $pdo = getDB();
    $segment = trim($_GET['segment'] ?? 'all');

    $query = "SELECT * FROM applicants WHERE 1=1";
    $params = [];

    if ($segment === 'pending') {
        $query .= " AND status = 'pending'";
    } else if ($segment === 'missing_req') {
        $query .= " AND docs_complete = 0";
    } else if ($segment === 'renewal') {
        $query .= " AND status = 'approved'";
    } else if ($segment === 'at_risk') {
        $query .= " AND (gwa > 2.0 OR failing_grades > 0)";
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $data = array_map(function($r) {
        return [
            'id' => (int)$r['id'],
            'name' => trim($r['first_name'] . ' ' . $r['last_name']),
            'studentId' => $r['student_id'],
            'email' => $r['email'],
            'status' => $r['status']
        ];
    }, $rows);

    sendJson([
        'success' => true,
        'data' => $data,
        'count' => count($data)
    ]);
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}
