<?php
require_once __DIR__ . '/init.php';

try {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        sendError('Invalid applicant ID.');
    }

    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM applicants WHERE id = ?");
    $stmt->execute([$id]);
    $r = $stmt->fetch();

    if (!$r) {
        sendError('Applicant not found.', 404);
    }

    $data = [
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
        'transcriptFile' => $r['transcript_file'],
        'recommendationFile' => $r['recommendation_file'],
        'validIdFile' => $r['valid_id_file'],
        'createdAt' => $r['created_at'],
        'created_at' => $r['created_at']
    ];

    sendJson(['success' => true, 'data' => $data]);
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}
