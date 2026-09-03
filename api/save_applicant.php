    <?php
    require_once __DIR__ . '/init.php';

    function getCoordinates($address)
    {
        if (empty($address)) {
            return [10.1333, 124.8333];
        }

        $addrLower = strtolower($address);

        // Common locations in Southern Leyte
        if (strpos($addrLower, 'maasin') !== false) {
            return [10.1333, 124.8333];
        }

        if (strpos($addrLower, 'macrohon') !== false) {
            return [10.0833, 124.9333];
        }

        if (
            strpos($addrLower, 'batu') !== false ||
            strpos($addrLower, 'bato') !== false
        ) {
            return [10.3333, 124.7833];
        }

        if (strpos($addrLower, 'hilongos') !== false) {
            return [10.3739, 124.7497];
        }

        if (strpos($addrLower, 'padre burgos') !== false) {
            return [10.0389, 124.9750];
        }

        if (strpos($addrLower, 'sogod') !== false) {
            return [10.3833, 124.9833];
        }

        if (strpos($addrLower, 'malitbog') !== false) {
            return [10.1500, 125.0000];
        }

        if (strpos($addrLower, 'saint bernard') !== false) {
            return [10.3333, 125.1333];
        }

        if (strpos($addrLower, 'liloan') !== false) {
            return [10.1667, 125.1333];
        }

        if (strpos($addrLower, 'bontoc') !== false) {
            return [10.3500, 124.9667];
        }

        /*
        * Nominatim fallback
        */
        try {
            $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
                'q' => $address,
                'format' => 'json',
                'limit' => 1
            ]);

            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 2,
                    'header' => "User-Agent: ScholarshipManagementSystem/1.0\r\n"
                ]
            ]);

            $response = @file_get_contents($url, false, $context);

            if ($response !== false) {
                $results = json_decode($response, true);

                if (!empty($results[0]['lat']) && !empty($results[0]['lon'])) {
                    return [
                        (float) $results[0]['lat'],
                        (float) $results[0]['lon']
                    ];
                }
            }
        } catch (Throwable $e) {
            // Use default coordinates if geocoding fails.
        }

        return [10.1333, 124.8333];
    }

    try {
        $pdo = getDB();

        /*
        * ============================================================
        * GET FORM DATA
        * ============================================================
        */

        $id = (int) ($_POST['id'] ?? $_POST['applicantId'] ?? 0);

        $firstName = trim(
            $_POST['firstName'] ??
            $_POST['first_name'] ??
            ''
        );

        $lastName = trim(
            $_POST['lastName'] ??
            $_POST['last_name'] ??
            ''
        );

        $studentId = trim(
            $_POST['studentId'] ??
            $_POST['student_id'] ??
            ''
        );

        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $birthdate = trim($_POST['birthdate'] ?? '');
        $address = trim($_POST['address'] ?? '');

        $school = trim($_POST['school'] ?? '');
        $program = trim($_POST['program'] ?? '');

        $yearLevel = trim(
            $_POST['yearLevel'] ??
            $_POST['year_level'] ??
            ''
        );

        $gpa = (float) ($_POST['gpa'] ?? 0);

        $scholarshipType = trim(
            $_POST['scholarshipType'] ??
            $_POST['scholarship_type'] ??
            'Academic Merit'
        );

        $essay = trim($_POST['essay'] ?? '');

        $status = trim($_POST['status'] ?? 'pending');

        /*
        * ============================================================
        * VALIDATION
        * ============================================================
        */

        if (
            empty($firstName) ||
            empty($lastName) ||
            empty($studentId) ||
            empty($email)
        ) {
            sendError(
                'Please fill out all required fields (First name, Last name, Student ID, Email).'
            );
        }

        if (empty($scholarshipType)) {
            sendError('Please select a scholarship type.');
        }

        /*
        * ============================================================
        * GET COORDINATES
        * ============================================================
        */

        [$latitude, $longitude] = getCoordinates($address);

        /*
        * ============================================================
        * FILE UPLOADS
        * ============================================================
        */

        $uploadDir = __DIR__ . '/../uploads/';

        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                sendError('Unable to create upload directory.', 500);
            }
        }

        $transcriptFile = '';
        $recommendationFile = '';
        $validIdFile = '';

        function saveUploadedFile($fieldName, $uploadDir)
        {
            if (
                !isset($_FILES[$fieldName]) ||
                !isset($_FILES[$fieldName]['name']) ||
                $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE
            ) {
                return '';
            }

            if ($_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
                return '';
            }

            $originalName = basename($_FILES[$fieldName]['name']);

            $extension = strtolower(
                pathinfo($originalName, PATHINFO_EXTENSION)
            );

            $allowedExtensions = [
                'pdf',
                'jpg',
                'jpeg',
                'png',
                'doc',
                'docx'
            ];

            if (!in_array($extension, $allowedExtensions, true)) {
                throw new Exception(
                    "Invalid file type for {$fieldName}."
                );
            }

            /*
            * Give every uploaded file a unique name.
            */
            $newFileName =
                uniqid($fieldName . '_', true) .
                '.' .
                $extension;

            $destination = $uploadDir . $newFileName;

            if (!move_uploaded_file(
                $_FILES[$fieldName]['tmp_name'],
                $destination
            )) {
                throw new Exception(
                    "Failed to upload {$fieldName}."
                );
            }

            return $newFileName;
        }

        /*
        * Support both naming styles.
        */
        $transcriptField =
            isset($_FILES['transcript'])
                ? 'transcript'
                : null;

        $recommendationField =
            isset($_FILES['recommendation'])
                ? 'recommendation'
                : null;

        $validIdField =
            isset($_FILES['validId'])
                ? 'validId'
                : (
                    isset($_FILES['valid_id'])
                        ? 'valid_id'
                        : null
                );

        if ($transcriptField) {
            $transcriptFile =
                saveUploadedFile(
                    $transcriptField,
                    $uploadDir
                );
        }

        if ($recommendationField) {
            $recommendationFile =
                saveUploadedFile(
                    $recommendationField,
                    $uploadDir
                );
        }

        if ($validIdField) {
            $validIdFile =
                saveUploadedFile(
                    $validIdField,
                    $uploadDir
                );
        }

        /*
        * ============================================================
        * UPDATE EXISTING APPLICANT
        * ============================================================
        *
        * This happens ONLY when JavaScript explicitly sends an ID.
        *
        * This is important:
        * We do NOT automatically update an applicant just because
        * the Student ID already exists.
        */

        if ($id > 0) {

            $findStmt = $pdo->prepare("
                SELECT
                    id,
                    student_id,
                    scholarship_type
                FROM applicants
                WHERE id = ?
                LIMIT 1
            ");

            $findStmt->execute([$id]);

            $existingApplicant = $findStmt->fetch(PDO::FETCH_ASSOC);

            if (!$existingApplicant) {
                sendError(
                    'The applicant you are trying to update no longer exists.',
                    404
                );
            }

            /*
            * Make sure the Student ID still belongs to the
            * applicant being updated.
            */
            if (
                (string) $existingApplicant['student_id']
                !== (string) $studentId
            ) {
                sendError(
                    'The Student ID does not match the selected applicant.',
                    400
                );
            }

            $updateSql = "
                UPDATE applicants
                SET
                    student_id = ?,
                    first_name = ?,
                    last_name = ?,
                    email = ?,
                    phone = ?,
                    birthdate = ?,
                    address = ?,
                    latitude = ?,
                    longitude = ?,
                    school = ?,
                    program = ?,
                    year_level = ?,
                    gpa = ?,
                    scholarship_type = ?,
                    essay = ?,
                    updated_at = CURRENT_TIMESTAMP
            ";

            $updateParams = [
                $studentId,
                $firstName,
                $lastName,
                $email,
                $phone,
                $birthdate,
                $address,
                $latitude,
                $longitude,
                $school,
                $program,
                $yearLevel,
                $gpa,
                $scholarshipType,
                $essay
            ];

            /*
            * Only replace file columns when a new file
            * was actually uploaded.
            */
            if ($transcriptFile !== '') {
                $updateSql .= ", transcript_file = ?";
                $updateParams[] = $transcriptFile;
            }

            if ($recommendationFile !== '') {
                $updateSql .= ", recommendation_file = ?";
                $updateParams[] = $recommendationFile;
            }

            if ($validIdFile !== '') {
                $updateSql .= ", valid_id_file = ?";
                $updateParams[] = $validIdFile;
            }

            $updateSql .= " WHERE id = ?";

            $updateParams[] = $id;

            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute($updateParams);

            sendJson([
                'success' => true,
                'action' => 'updated',
                'id' => $id,
                'message' => 'Applicant updated successfully.'
            ]);
        }

        /*
        * ============================================================
        * CHECK EXISTING STUDENT ID + SCHOLARSHIP TYPE
        * ============================================================
        *
        * IMPORTANT:
        *
        * We only consider it a duplicate when BOTH:
        *
        *     student_id
        *     scholarship_type
        *
        * are the same.
        *
        * Same Student ID + DIFFERENT scholarship = new application.
        */

        $duplicateStmt = $pdo->prepare("
            SELECT
                id,
                student_id,
                first_name,
                last_name,
                email,
                scholarship_type,
                status
            FROM applicants
            WHERE student_id = ?
            AND scholarship_type = ?
            ORDER BY id DESC
            LIMIT 1
        ");

        $duplicateStmt->execute([
            $studentId,
            $scholarshipType
        ]);

        $duplicate = $duplicateStmt->fetch(PDO::FETCH_ASSOC);

        if ($duplicate) {

            /*
            * Tell JavaScript that a matching application already exists.
            *
            * Do NOT insert.
            * Do NOT update.
            *
            * JavaScript will ask the user what to do.
            */
            sendJson([
                'success' => false,
                'duplicate' => true,
                'existingApplicant' => [
                    'id' => (int) $duplicate['id'],
                    'studentId' => $duplicate['student_id'],
                    'firstName' => $duplicate['first_name'],
                    'lastName' => $duplicate['last_name'],
                    'email' => $duplicate['email'],
                    'scholarshipType' => $duplicate['scholarship_type'],
                    'status' => $duplicate['status']
                ],
                'message' =>
                    'An application with this Student ID already exists for this scholarship.'
            ]);
        }

        /*
        * ============================================================
        * CREATE NEW APPLICANT
        * ============================================================
        *
        * If we reach this point:
        *
        * - Student ID is new, OR
        * - Student ID exists but scholarship type is different.
        *
        * Therefore, create a new application.
        */

                /*
        * ============================================================
        * CREATE NEW APPLICANT
        * ============================================================
        */

        $insertSql = "
            INSERT INTO applicants (
                student_id,
                first_name,
                last_name,
                email,
                phone,
                birthdate,
                address,
                latitude,
                longitude,
                school,
                program,
                year_level,
                gpa,
                scholarship_type,
                status,
                gwa,
                gwa_req,
                failing_grades,
                units,
                enrolled,
                docs_complete,
                essay,
                transcript_file,
                recommendation_file,
                valid_id_file
            )
            VALUES (
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?
            )
        ";

        $insertStmt = $pdo->prepare($insertSql);

        $insertParams = [
            $studentId,          // 1
            $firstName,          // 2
            $lastName,           // 3
            $email,              // 4
            $phone,              // 5
            $birthdate,          // 6
            $address,            // 7
            $latitude,           // 8
            $longitude,          // 9
            $school,             // 10
            $program,            // 11
            $yearLevel,          // 12
            $gpa,                // 13
            $scholarshipType,    // 14
            $status,             // 15
            $gpa,                // 16 - gwa
            1.75,                // 17 - gwa_req
            0,                   // 18 - failing_grades
            21,                  // 19 - units
            1,                   // 20 - enrolled
            1,                   // 21 - docs_complete
            $essay,              // 22
            $transcriptFile,     // 23
            $recommendationFile, // 24
            $validIdFile         // 25
        ];

        $insertStmt->execute($insertParams);

        $newId = (int) $pdo->lastInsertId();
        sendJson([
            'success' => true,
            'action' => 'created',
            'id' => $newId,
            'message' => 'Applicant added successfully.'
        ]);

    } catch (Throwable $e) {

        sendError(
            $e->getMessage(),
            500
        );
    }