<?php
require_once __DIR__ . '/init.php';

try {
    $pdo = getDB();

    $stmt = $pdo->query("
        SELECT
            r.id AS record_id,
            r.applicant_id,
            r.student_id,
            r.name,
            r.status AS record_status,
            a.id AS applicant_table_id,
            a.status AS applicant_status,
            a.address,
            a.program,
            a.latitude,
            a.longitude
        FROM records r
        LEFT JOIN applicants a
            ON r.applicant_id = a.id
        ORDER BY r.id DESC
    ");

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $rows
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}