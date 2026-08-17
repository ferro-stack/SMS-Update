<?php
require_once __DIR__ . '/../config/database.php';

function initDatabase(): PDO {
    $pdo = getSQLiteConnection();

    // 1. Users Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'registrar',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Scholarships Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS scholarships (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        code TEXT NOT NULL,
        description TEXT,
        type TEXT NOT NULL,
        gwa_requirement REAL NOT NULL,
        slots INTEGER NOT NULL DEFAULT 0,
        slots_available INTEGER NOT NULL DEFAULT 0,
        coverage TEXT,
        status TEXT NOT NULL DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 3. Applicants Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS applicants (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        student_id TEXT NOT NULL,
        first_name TEXT NOT NULL,
        last_name TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT,
        birthdate TEXT,
        address TEXT,
        school TEXT,
        program TEXT,
        year_level TEXT,
        gpa REAL DEFAULT 0,
        scholarship_type TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT 'pending',
        gwa REAL DEFAULT 0,
        gwa_req REAL DEFAULT 1.75,
        failing_grades INTEGER DEFAULT 0,
        units INTEGER DEFAULT 21,
        enrolled INTEGER DEFAULT 1,
        docs_complete INTEGER DEFAULT 1,
        remarks TEXT DEFAULT '',
        essay TEXT DEFAULT '',
        transcript_file TEXT DEFAULT '',
        recommendation_file TEXT DEFAULT '',
        valid_id_file TEXT DEFAULT '',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 4. Notifications Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        type TEXT NOT NULL,
        recipient_type TEXT NOT NULL DEFAULT 'segment',
        recipient_id TEXT DEFAULT NULL,
        recipient_name TEXT DEFAULT '',
        recipient_email TEXT DEFAULT '',
        subject TEXT NOT NULL,
        message TEXT NOT NULL,
        deadline TEXT DEFAULT '',
        status TEXT NOT NULL DEFAULT 'sent',
        sent_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 5. Records Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS records (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        applicant_id INTEGER,
        student_id TEXT NOT NULL,
        name TEXT NOT NULL,
        scholarship_type TEXT NOT NULL,
        status TEXT NOT NULL,
        semester TEXT NOT NULL,
        sy TEXT NOT NULL,
        date_evaluated TEXT NOT NULL,
        remarks TEXT DEFAULT '',
        FOREIGN KEY(applicant_id) REFERENCES applicants(id) ON DELETE SET NULL
    )");

    // 6. Renewal & Retention Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS renewal_retention (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        student_id TEXT NOT NULL,
        name TEXT NOT NULL,
        gwa REAL NOT NULL,
        failing_grades INTEGER DEFAULT 0,
        enrolled INTEGER DEFAULT 1,
        status TEXT NOT NULL DEFAULT 'eligible',
        school_year TEXT NOT NULL,
        semester TEXT NOT NULL,
        scholarship_type TEXT NOT NULL,
        remarks TEXT DEFAULT '',
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 7. Imported Files Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS imported_files (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        file_type TEXT NOT NULL,
        file_name TEXT NOT NULL,
        file_size INTEGER DEFAULT 0,
        records_count INTEGER DEFAULT 0,
        imported_by TEXT DEFAULT 'Registrar Staff',
        status TEXT NOT NULL DEFAULT 'Active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Seed Data if empty
    seedDataIfEmpty($pdo);

    return $pdo;
}

function seedDataIfEmpty(PDO $pdo): void {
    // Check if applicants is empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM applicants");
    if ($stmt->fetchColumn() == 0) {
        // Seed Scholarships
        $scholarships = [
            ['Academic Merit Scholarship', 'AMS', 'Full tuition coverage for high-performing students with GPA <= 1.50', 'Academic Merit', 1.50, 50, 14, '100% Tuition & Allowances', 'active'],
            ['Financial Need-Based Grant', 'FNBG', 'Assistance for students with low family income', 'Financial Need-Based', 2.25, 100, 32, '75% Tuition', 'active'],
            ['Athletic Excellence Scholarship', 'AES', 'For university varsity athletes representing the institution', 'Athletic', 2.50, 30, 8, 'Full Tuition + Sports Allowance', 'active'],
            ['Community Leadership Award', 'CLA', 'For active student organization leaders and community servers', 'Community Service', 2.00, 25, 5, '50% Tuition', 'active']
        ];
        $insertSch = $pdo->prepare("INSERT INTO scholarships (name, code, description, type, gwa_requirement, slots, slots_available, coverage, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($scholarships as $sch) {
            $insertSch->execute($sch);
        }

        // Seed Applicants
        $applicants = [
            ['20230001', 'Juan', 'Dela Cruz', 'juan.delacruz@email.com', '09171234567', '2003-05-14', 'Maasin City, Southern Leyte', 'College of Maasin', 'BS Information Technology · 2nd Year', '2nd Year', 1.43, 'Academic Merit', 'pending', 1.43, 1.75, 0, 21, 1, 1, 'Top rank in class'],
            ['20230002', 'Maria', 'Santos', 'maria.santos@email.com', '09189876543', '2002-11-20', 'Macrohon, Southern Leyte', 'College of Maasin', 'BS Computer Science · 3rd Year', '3rd Year', 1.65, 'Academic Merit', 'review', 1.65, 1.75, 0, 21, 1, 1, 'Requires Dean recommendation verification'],
            ['20230003', 'Carlo', 'Mendoza', 'carlo.mendoza@email.com', '09195551234', '2004-01-10', 'Batu, Leyte', 'College of Maasin', 'BS Business Administration · 1st Year', '1st Year', 2.10, 'Financial Need-Based', 'interview', 2.10, 2.25, 0, 18, 1, 1, 'Scheduled for panel interview'],
            ['20230004', 'Angelica', 'Reyes', 'angelica.reyes@email.com', '09204443322', '2003-08-05', 'Hilongos, Leyte', 'College of Maasin', 'BS Nursing · 2nd Year', '2nd Year', 1.25, 'Academic Merit', 'approved', 1.25, 1.75, 0, 24, 1, 1, 'Endorsed for 100% grant'],
            ['20230005', 'Kevin', 'Bautista', 'kevin.bautista@email.com', '09213332211', '2002-03-30', 'Maasin City, Southern Leyte', 'College of Maasin', 'BS Criminology · 4th Year', '4th Year', 2.80, 'Athletic', 'rejected', 2.80, 2.50, 2, 15, 1, 0, 'Did not meet minimum units & GWA'],
            ['20230006', 'Samantha', 'Gomez', 'samantha.gomez@email.com', '09228889900', '2004-09-12', 'Padre Burgos, Southern Leyte', 'College of Maasin', 'BS Education · 1st Year', '1st Year', 1.80, 'Community Service', 'pending', 1.80, 2.00, 0, 21, 1, 1, 'Submitted complete documents']
        ];
        $insertApp = $pdo->prepare("INSERT INTO applicants (student_id, first_name, last_name, email, phone, birthdate, address, school, program, year_level, gpa, scholarship_type, status, gwa, gwa_req, failing_grades, units, enrolled, docs_complete, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($applicants as $app) {
            $insertApp->execute($app);
        }

        // Seed Notifications
        $notifications = [
            ['missing_requirements', 'segment', null, 'Missing Documents Group', 'juan.delacruz@email.com', 'Action needed: missing scholarship requirements', 'Hi Juan, your application is missing the Certificate of Indigency. Please submit before deadline.', '2026-08-20', 'sent', '2026-08-08 10:30:00'],
            ['renewal_deadline', 'segment', null, 'Active Scholars', 'maria.santos@email.com', 'Reminder: scholarship renewal deadline approaching', 'Hi Maria, submit your 1st semester clearance before August 25.', '2026-08-25', 'sent', '2026-08-09 14:15:00'],
            ['failed_retention', 'individual', '5', 'Kevin Bautista', 'kevin.bautista@email.com', 'Important: scholarship retention requirements not met', 'Hi Kevin, please visit the Registrar office regarding your scholarship status.', '', 'sent', '2026-08-10 09:00:00']
        ];
        $insertNotif = $pdo->prepare("INSERT INTO notifications (type, recipient_type, recipient_id, recipient_name, recipient_email, subject, message, deadline, status, sent_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($notifications as $notif) {
            $insertNotif->execute($notif);
        }

        // Seed Records
        $records = [
            [4, '20230004', 'Angelica Reyes', 'Academic Merit', 'approved', '1st Semester', '2025-2026', '2026-08-01', 'Approved with High Distinction'],
            [5, '20230005', 'Kevin Bautista', 'Athletic', 'rejected', '1st Semester', '2025-2026', '2026-08-02', 'Disqualified due to low GWA']
        ];
        $insertRec = $pdo->prepare("INSERT INTO records (applicant_id, student_id, name, scholarship_type, status, semester, sy, date_evaluated, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($records as $rec) {
            $insertRec->execute($rec);
        }

        // Seed Renewal & Retention
        $renewalData = [
            ['20230001', 'Juan Dela Cruz', 1.43, 0, 1, 'eligible', '2025-2026', 'First Semester', 'Academic Merit', 'Meets all retention requirements'],
            ['20230002', 'Maria Santos', 1.65, 0, 1, 'eligible', '2025-2026', 'First Semester', 'Academic Merit', 'Good standing'],
            ['20230003', 'Carlo Mendoza', 2.45, 1, 1, 'at-risk', '2025-2026', 'First Semester', 'Financial Need-Based', 'Near GWA threshold limit'],
            ['20230005', 'Kevin Bautista', 2.80, 2, 0, 'terminated', '2025-2026', 'First Semester', 'Athletic', 'Unenrolled / failed retention standard']
        ];
        $insertRen = $pdo->prepare("INSERT INTO renewal_retention (student_id, name, gwa, failing_grades, enrolled, status, school_year, semester, scholarship_type, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($renewalData as $ren) {
            $insertRen->execute($ren);
        }
    }

    // Check if imported_files is empty
    $stmtImp = $pdo->query("SELECT COUNT(*) FROM imported_files");
    if ($stmtImp->fetchColumn() == 0) {
        $sampleFiles = [
            ['grades', '2025-SY1_Academic_Grades.csv', 45056, 42, 'Registrar Staff', 'Active', '2026-08-12 11:20:00'],
            ['grades', 'BSIT_2ndYear_Midterm_Grades.xlsx', 62400, 38, 'Registrar Staff', 'Active', '2026-08-14 09:15:00'],
            ['enrollment', '2025-2026_Enrolled_Students.xlsx', 1048576, 120, 'Registrar Staff', 'Active', '2026-08-10 14:45:00'],
            ['enrollment', '1stSem_Official_Enrollment.csv', 81920, 85, 'Registrar Staff', 'Active', '2026-08-13 16:30:00']
        ];
        $insertImp = $pdo->prepare("INSERT INTO imported_files (file_type, file_name, file_size, records_count, imported_by, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($sampleFiles as $imp) {
            $insertImp->execute($imp);
        }
    }
}

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    initDatabase();
    echo "SQLite Database initialized & seeded successfully!\n";
}
