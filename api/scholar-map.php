```php
<?php
require_once __DIR__ . '/init.php';

try {
    $pdo = getDB();

    /*
     * Get approved scholarship records
     * and their location from the applicants table.
     */
    $stmt = $pdo->query("
        SELECT
            r.id,
            r.applicant_id,
            r.student_id,
            r.name,
            r.scholarship_type,
            r.status,
            a.address,
            a.program,
            a.latitude,
            a.longitude
        FROM records r
        INNER JOIN applicants a
            ON r.applicant_id = a.id
        WHERE LOWER(r.status) = 'approved'
          AND a.latitude IS NOT NULL
          AND a.longitude IS NOT NULL
        ORDER BY r.name ASC
    ");

    $rows = $stmt->fetchAll();

    $data = array_map(function ($r) {
        return [
            'id' => (int)$r['id'],
            'applicantId' => (int)$r['applicant_id'],
            'studentId' => $r['student_id'],
            'name' => $r['name'],
            'scholarshipType' => $r['scholarship_type'],
            'status' => $r['status'],
            'address' => $r['address'] ?? '',
            'program' => $r['program'] ?? '',
            'latitude' => (float)$r['latitude'],
            'longitude' => (float)$r['longitude']
        ];
    }, $rows);

    sendJson([
        'success' => true,
        'data' => $data
    ]);

} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}

