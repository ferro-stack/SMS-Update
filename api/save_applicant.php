<?php
require_once __DIR__ . '/init.php';

function getCoordinates($address) {
    if (empty($address)) {
        return [10.1333, 124.8333];
    }

    $addrLower = strtolower($address);
    if (strpos($addrLower, 'maasin') !== false) return [10.1333, 124.8333];
    if (strpos($addrLower, 'macrohon') !== false) return [10.0833, 124.9333];
    if (strpos($addrLower, 'batu') !== false || strpos($addrLower, 'bato') !== false) return [10.3333, 124.7833];
    if (strpos($addrLower, 'hilongos') !== false) return [10.3739, 124.7497];
    if (strpos($addrLower, 'padre burgos') !== false) return [10.0389, 124.9750];
    if (strpos($addrLower, 'sogod') !== false) return [10.3833, 124.9833];
    if (strpos($addrLower, 'malitbog') !== false) return [10.1500, 125.0000];
    if (strpos($addrLower, 'saint bernard') !== false) return [10.3333, 125.1333];
    if (strpos($addrLower, 'liloan') !== false) return [10.1667, 125.1333];
    if (strpos($addrLower, 'bontoc') !== false) return [10.3500, 124.9667];

    $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
        'q' => $address,
        'format' => 'json',
        'limit' => 1
    ]);

    $opts = [
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: SMRES-Scholarship-System/1.0\r\n",
            'timeout' => 2
        ]
    ];

    $context = stream_context_create($opts);
    $response = @file_get_contents($url, false, $context);

    if ($response !== false) {
        $data = json_decode($response, true);
        if (!empty($data[0]['lat']) && !empty($data[0]['lon'])) {
            return [(float)$data[0]['lat'], (float)$data[0]['lon']];
        }
    }

    return [10.1333, 124.8333];
}

try {
    $pdo = getDB();

    $id = (int)($_POST['id'] ?? $_POST['applicantId'] ?? 0);
    $firstName = trim($_POST['firstName'] ?? $_POST['first_name'] ?? '');
    $lastName = trim($_POST['lastName'] ?? $_POST['last_name'] ?? '');
    $studentId = trim($_POST['studentId'] ?? $_POST['student_id'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $birthdate = trim($_POST['birthdate'] ?? '');
    $address = trim($_POST['address'] ?? '');
    [$latitude, $longitude] = getCoordinates($address);
    $school = trim($_POST['school'] ?? '');
    $program = trim($_POST['program'] ?? '');
    $yearLevel = trim($_POST['yearLevel'] ?? $_POST['year_level'] ?? '');
    $gpa = (float)($_POST['gpa'] ?? 0);
    $scholarshipType = trim($_POST['scholarshipType'] ?? $_POST['scholarship_type'] ?? 'Academic Merit');
    $essay = trim($_POST['essay'] ?? '');
    $status = trim($_POST['status'] ?? 'pending');

    if (empty($firstName) || empty($lastName) || empty($studentId) || empty($email)) {
        sendError('Please fill out all required fields (First name, Last name, Student ID, Email).');
    }

    // Handle File Uploads
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $transcriptFile = '';
    $recommendationFile = '';
    $validIdFile = '';

    if (!empty($_FILES['transcript']['name'])) {
        $ext = pathinfo($_FILES['transcript']['name'], PATHINFO_EXTENSION);
        $fileName = 'transcript_' . time() . '_' . rand(100, 999) . '.' . $ext;
        move_uploaded_file($_FILES['transcript']['tmp_name'], $uploadDir . $fileName);
        $transcriptFile = 'uploads/' . $fileName;
    }

    if (!empty($_FILES['recommendation']['name'])) {
        $ext = pathinfo($_FILES['recommendation']['name'], PATHINFO_EXTENSION);
        $fileName = 'recommendation_' . time() . '_' . rand(100, 999) . '.' . $ext;
        move_uploaded_file($_FILES['recommendation']['tmp_name'], $uploadDir . $fileName);
        $recommendationFile = 'uploads/' . $fileName;
    }

    if (!empty($_FILES['validId']['name']) || !empty($_FILES['valid_id']['name'])) {
        $fileObj = $_FILES['validId'] ?? $_FILES['valid_id'];
        $ext = pathinfo($fileObj['name'], PATHINFO_EXTENSION);
        $fileName = 'valid_id_' . time() . '_' . rand(100, 999) . '.' . $ext;
        move_uploaded_file($fileObj['tmp_name'], $uploadDir . $fileName);
        $validIdFile = 'uploads/' . $fileName;
    }

    // Check if applicant with same student_id or email already exists to prevent duplicate insertion
    if ($id <= 0) {
        $checkStmt = $pdo->prepare("SELECT id FROM applicants WHERE student_id = ? OR (email = ? AND email != '') LIMIT 1");
        $checkStmt->execute([$studentId, $email]);
        $existing = $checkStmt->fetch();
        if ($existing) {
            $id = (int)$existing['id'];
        }
    }

    if ($id > 0) {
        // Update existing applicant
        $sql = "UPDATE applicants SET
        student_id = ?, first_name = ?, last_name = ?, email = ?, phone = ?, birthdate = ?,
        address = ?, latitude = ?, longitude = ?, school = ?, program = ?, year_level = ?, gpa = ?, scholarship_type = ?,
        essay = ?, updated_at = CURRENT_TIMESTAMP";
        $params = [$studentId, $firstName, $lastName, $email, $phone, $birthdate, $address, $latitude, $longitude, $school, $program, $yearLevel, $gpa, $scholarshipType, $essay];

        if ($transcriptFile) {
            $sql .= ", transcript_file = ?";
            $params[] = $transcriptFile;
        }
        if ($recommendationFile) {
            $sql .= ", recommendation_file = ?";
            $params[] = $recommendationFile;
        }
        if ($validIdFile) {
            $sql .= ", valid_id_file = ?";
            $params[] = $validIdFile;
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        sendJson(['success' => true, 'id' => $id, 'message' => 'Applicant updated successfully.']);
    } else {
        // Insert new applicant
        $stmt = $pdo->prepare("INSERT INTO applicants (
            student_id, first_name, last_name, email, phone, birthdate, address, latitude, longitude, school,
            program, year_level, gpa, scholarship_type, status, gwa, gwa_req, failing_grades,
            units, enrolled, docs_complete, essay, transcript_file, recommendation_file, valid_id_file
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1.75, 0, 21, 1, 1, ?, ?, ?, ?)");

        $stmt->execute([
            $studentId, $firstName, $lastName, $email, $phone, $birthdate, $address, $latitude, $longitude, $school,
            $program, $yearLevel, $gpa, $scholarshipType, $status, $gpa, $essay,
            $transcriptFile, $recommendationFile, $validIdFile
        ]);

        $newId = (int)$pdo->lastInsertId();
        sendJson(['success' => true, 'id' => $newId, 'message' => 'Applicant added successfully.']);
    }
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}
