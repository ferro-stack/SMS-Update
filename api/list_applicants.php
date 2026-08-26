<?php
require_once __DIR__ . '/init.php';

try {
    $pdo = getDB();
    $statusParam = trim($_GET['status'] ?? '');
    $typeParam = trim($_GET['type'] ?? '');

    $query = "SELECT * FROM applicants WHERE 1=1";
    $params = [];

    if ($statusParam !== '' && strtolower($statusParam) !== 'all') {
        $statuses = array_map('trim', explode(',', $statusParam));
        $placeholders = implode(',', array_fill(0, count($statuses), '?'));
        $query .= " AND status IN ($placeholders)";
        $params = array_merge($params, $statuses);
    }

    if ($typeParam !== '' && strtolower($typeParam) !== 'all') {
        $query .= " AND scholarship_type = ?";
        $params[] = $typeParam;
    }

    $query .= " ORDER BY id DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    // Map rows to camelCase structure for JS consumption if needed
    $data = array_map(function($r) {
        return [
            'id' => (int)$r['id'],
            'studentId' => $r['student_id'],
            'student_id' => $r['student_id'],
            'firstName' => $r['first_name'],
            'first_name' => $r['first_name'],
            'lastName' => $r['last_name'],
            'last_name' => $r['last_name'],
            'name' => trim($r['first_name'] . ' ' . $r['last_name']),
            'email' => $r['email'],
            'phone' => $r['phone'],
            'birthdate' => $r['birthdate'],
            'address' => $r['address'],
            'school' => $r['school'],
            'latitude' => $r['latitude'] !== null ? (float)$r['latitude'] : null,
            'longitude' => $r['longitude'] !== null ? (float)$r['longitude'] : null,
            'program' => $r['program'],
            'yearLevel' => $r['year_level'],
            'year_level' => $r['year_level'],
            'gpa' => (float)$r['gpa'],
            'scholarshipType' => $r['scholarship_type'],
            'scholarship_type' => $r['scholarship_type'],
            'type' => $r['scholarship_type'],
            'status' => $r['status'],
            'gwa' => (float)$r['gwa'],
            'gwaReq' => (float)$r['gwa_req'],
            'failingGrades' => (int)$r['failing_grades'],
            'units' => (int)$r['units'],
            'enrolled' => (bool)$r['enrolled'],
            'docsComplete' => (bool)$r['docs_complete'],
            'remarks' => $r['remarks'],
            'essay' => $r['essay'],
            'createdAt' => $r['created_at'],
            'created_at' => $r['created_at']
        ];
    }, $rows);

    sendJson(['success' => true, 'data' => $data]);
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}
