<?php
require_once __DIR__ . '/init.php';

try {
    $pdo = getDB();

    $department = trim($_GET['department'] ?? $_GET['program'] ?? '');

    $list = [];

    // 1. Fetch from scholars table where latitude and longitude are set
    $sqlScholars = "SELECT id, student_id, name, department, year_level, gwa, status, school_year, address, latitude, longitude FROM scholars WHERE latitude IS NOT NULL AND longitude IS NOT NULL";
    $params = [];
    if (!empty($department) && strtolower($department) !== 'all') {
        $sqlScholars .= " AND LOWER(department) = LOWER(?)";
        $params[] = $department;
    }
    $stmt1 = $pdo->prepare($sqlScholars);
    $stmt1->execute($params);
    $scholarsRows = $stmt1->fetchAll();

    foreach ($scholarsRows as $r) {
        $list[] = [
            'id' => (int)$r['id'],
            'studentId' => $r['student_id'],
            'name' => $r['name'],
            'department' => $r['department'],
            'yearLevel' => (int)$r['year_level'],
            'gwa' => (float)$r['gwa'],
            'status' => $r['status'],
            'schoolYear' => $r['school_year'],
            'address' => $r['address'] ?? '',
            'latitude' => (float)$r['latitude'],
            'longitude' => (float)$r['longitude'],
            'source' => 'scholar'
        ];
    }

    // 2. Fetch from applicants table where latitude and longitude are set
    $sqlApp = "SELECT id, student_id, first_name, last_name, program, scholarship_type, status, address, latitude, longitude, gwa, year_level FROM applicants WHERE latitude IS NOT NULL AND longitude IS NOT NULL";
    $stmt2 = $pdo->query($sqlApp);
    $appRows = $stmt2->fetchAll();

    $existingStudentIds = array_column($list, 'studentId');

    foreach ($appRows as $r) {
        if (in_array($r['student_id'], $existingStudentIds)) {
            continue; // avoid duplication if already in scholars table
        }
        $prog = $r['program'] ?? '';
        $dept = 'Information Technology';
        if (stripos($prog, 'nursing') !== false) $dept = 'Nursing';
        elseif (stripos($prog, 'accountancy') !== false) $dept = 'Accountancy';
        elseif (stripos($prog, 'business') !== false) $dept = 'Business Administration';
        elseif (stripos($prog, 'education') !== false || stripos($prog, 'liberal') !== false) $dept = 'Liberal Arts and Education';
        elseif (stripos($prog, 'food') !== false || stripos($prog, 'service') !== false) $dept = 'Food Preparation & Service Technology';
        elseif (stripos($prog, 'technology') !== false || stripos($prog, 'computer') !== false) $dept = 'Information Technology';

        if (!empty($department) && strtolower($department) !== 'all' && strtolower($dept) !== strtolower($department)) {
            continue;
        }

        $list[] = [
            'id' => (int)$r['id'],
            'studentId' => $r['student_id'],
            'name' => trim($r['first_name'] . ' ' . $r['last_name']),
            'department' => $dept,
            'yearLevel' => 1,
            'gwa' => (float)($r['gwa'] ?? 1.50),
            'status' => $r['status'],
            'schoolYear' => '2025-2026',
            'address' => $r['address'] ?? '',
            'latitude' => (float)$r['latitude'],
            'longitude' => (float)$r['longitude'],
            'source' => 'applicant'
        ];
    }

    sendJson([
        'success' => true,
        'data' => $list
    ]);
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}
