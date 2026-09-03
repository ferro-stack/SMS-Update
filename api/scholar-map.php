<?php
require_once __DIR__ . '/init.php';

try {
    $pdo = getDB();

    $department = trim(
        $_GET['department'] ??
        $_GET['program'] ??
        ''
    );

    $list = [];

    /* =========================================================
       GET STUDENT IDS THAT ARE CURRENTLY IN TRASH
    ========================================================= */

    $deletedStudentIds = [];

    $stmtDeleted = $pdo->query("
        SELECT item_data
        FROM deleted_items
        WHERE item_type = 'record'
    ");

    $deletedRows = $stmtDeleted->fetchAll();

    foreach ($deletedRows as $deleted) {

        if (empty($deleted['item_data'])) {
            continue;
        }

        $data = json_decode(
            $deleted['item_data'],
            true
        );

        if (
            is_array($data) &&
            !empty($data['student_id'])
        ) {
            $deletedStudentIds[] =
                trim((string)$data['student_id']);
        }
    }

    $deletedStudentIds = array_unique(
        $deletedStudentIds
    );


    /* =========================================================
       1. FETCH SCHOLARS

       ONLY scholars that have an APPROVED applicant
    ========================================================= */

    $sqlScholars = "
        SELECT
            s.id,
            s.student_id,
            s.name,
            s.department,
            s.year_level,
            s.gwa,
            s.status,
            s.school_year,
            s.address,
            s.latitude,
            s.longitude
        FROM scholars s

        INNER JOIN applicants a
            ON a.student_id = s.student_id

        WHERE s.latitude IS NOT NULL
        AND s.longitude IS NOT NULL

        AND LOWER(TRIM(a.status)) = 'approved'
    ";

    $params = [];


    /* =========================================================
       EXCLUDE DELETED STUDENTS
    ========================================================= */

    if (!empty($deletedStudentIds)) {

        $placeholders = implode(
            ',',
            array_fill(
                0,
                count($deletedStudentIds),
                '?'
            )
        );

        $sqlScholars .= "
            AND s.student_id NOT IN ($placeholders)
        ";

        foreach ($deletedStudentIds as $studentId) {
            $params[] = $studentId;
        }
    }


    /* =========================================================
       DEPARTMENT FILTER
    ========================================================= */

    if (
        !empty($department) &&
        strtolower($department) !== 'all'
    ) {

        $sqlScholars .= "
            AND LOWER(s.department) = LOWER(?)
        ";

        $params[] = $department;
    }


    $stmt1 = $pdo->prepare($sqlScholars);
    $stmt1->execute($params);

    $scholarsRows = $stmt1->fetchAll();


    foreach ($scholarsRows as $r) {

        $list[] = [

            'id' =>
                (int)$r['id'],

            'studentId' =>
                $r['student_id'],

            'name' =>
                $r['name'],

            'department' =>
                $r['department'],

            'yearLevel' =>
                (int)$r['year_level'],

            'gwa' =>
                (float)$r['gwa'],

            'status' =>
                'approved',

            'schoolYear' =>
                $r['school_year'],

            'address' =>
                $r['address'] ?? '',

            'latitude' =>
                (float)$r['latitude'],

            'longitude' =>
                (float)$r['longitude'],

            'source' =>
                'scholar'
        ];
    }


    /* =========================================================
       2. FETCH APPROVED APPLICANTS

       This is the Evaluation source.
    ========================================================= */

    $sqlApp = "
        SELECT
            id,
            student_id,
            first_name,
            last_name,
            program,
            scholarship_type,
            status,
            address,
            latitude,
            longitude,
            gwa,
            year_level
        FROM applicants

        WHERE latitude IS NOT NULL
        AND longitude IS NOT NULL

        AND LOWER(TRIM(status)) = 'approved'
    ";

    $appParams = [];


    /* =========================================================
       EXCLUDE DELETED STUDENTS
    ========================================================= */

    if (!empty($deletedStudentIds)) {

        $placeholders = implode(
            ',',
            array_fill(
                0,
                count($deletedStudentIds),
                '?'
            )
        );

        $sqlApp .= "
            AND student_id NOT IN ($placeholders)
        ";

        foreach ($deletedStudentIds as $studentId) {
            $appParams[] = $studentId;
        }
    }


    /* =========================================================
       GET APPROVED APPLICANTS
    ========================================================= */

    $stmt2 = $pdo->prepare($sqlApp);
    $stmt2->execute($appParams);

    $appRows = $stmt2->fetchAll();


    /* =========================================================
       PREVENT DUPLICATES
    ========================================================= */

    $existingStudentIds = array_column(
        $list,
        'studentId'
    );


    /* =========================================================
       ADD APPROVED APPLICANTS
    ========================================================= */

    foreach ($appRows as $r) {

        if (
            in_array(
                $r['student_id'],
                $existingStudentIds
            )
        ) {
            continue;
        }


        /* =====================================================
           DETERMINE DEPARTMENT
        ===================================================== */

        $prog = $r['program'] ?? '';

        $dept = 'Information Technology';

        if (
            stripos($prog, 'nursing') !== false
        ) {

            $dept = 'Nursing';

        } elseif (
            stripos($prog, 'accountancy') !== false
        ) {

            $dept = 'Accountancy';

        } elseif (
            stripos($prog, 'business') !== false
        ) {

            $dept = 'Business Administration';

        } elseif (
            stripos($prog, 'education') !== false ||
            stripos($prog, 'liberal') !== false
        ) {

            $dept =
                'Liberal Arts and Education';

        } elseif (
            stripos($prog, 'food') !== false ||
            stripos($prog, 'service') !== false
        ) {

            $dept =
                'Food Preparation & Service Technology';

        } elseif (
            stripos($prog, 'technology') !== false ||
            stripos($prog, 'computer') !== false
        ) {

            $dept =
                'Information Technology';
        }


        /* =====================================================
           DEPARTMENT FILTER
        ===================================================== */

        if (
            !empty($department) &&
            strtolower($department) !== 'all' &&
            strtolower($dept) !== strtolower($department)
        ) {
            continue;
        }


        /* =====================================================
           ADD STUDENT
        ===================================================== */

        $list[] = [

            'id' =>
                (int)$r['id'],

            'studentId' =>
                $r['student_id'],

            'name' =>
                trim(
                    ($r['first_name'] ?? '') .
                    ' ' .
                    ($r['last_name'] ?? '')
                ),

            'department' =>
                $dept,

            'yearLevel' =>
                (int)(
                    $r['year_level'] ?? 1
                ),

            'gwa' =>
                (float)(
                    $r['gwa'] ?? 1.50
                ),

            'status' =>
                'approved',

            'schoolYear' =>
                '2025-2026',

            'address' =>
                $r['address'] ?? '',

            'latitude' =>
                (float)$r['latitude'],

            'longitude' =>
                (float)$r['longitude'],

            'source' =>
                'applicant'
        ];
    }


    /* =========================================================
       SEND RESULT
    ========================================================= */

    sendJson([
        'success' => true,
        'data' => $list
    ]);

} catch (Exception $e) {

    sendError(
        $e->getMessage(),
        500
    );
}