<?php
require_once __DIR__ . '/init.php';

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

    if ($id > 0) {
        // Update existing applicant
        $sql = "UPDATE applicants SET 
            student_id = ?, first_name = ?, last_name = ?, email = ?, phone = ?, birthdate = ?, 
            address = ?, school = ?, program = ?, year_level = ?, gpa = ?, scholarship_type = ?, 
            essay = ?, updated_at = CURRENT_TIMESTAMP";
        $params = [$studentId, $firstName, $lastName, $email, $phone, $birthdate, $address, $school, $program, $yearLevel, $gpa, $scholarshipType, $essay];

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
            student_id, first_name, last_name, email, phone, birthdate, address, school, 
            program, year_level, gpa, scholarship_type, status, gwa, gwa_req, failing_grades, 
            units, enrolled, docs_complete, essay, transcript_file, recommendation_file, valid_id_file
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1.75, 0, 21, 1, 1, ?, ?, ?, ?)");

        $stmt->execute([
            $studentId, $firstName, $lastName, $email, $phone, $birthdate, $address, $school,
            $program, $yearLevel, $gpa, $scholarshipType, $status, $gpa, $essay,
            $transcriptFile, $recommendationFile, $validIdFile
        ]);

        $newId = (int)$pdo->lastInsertId();
        sendJson(['success' => true, 'id' => $newId, 'message' => 'Applicant added successfully.']);
    }
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}
