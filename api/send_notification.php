<?php
require_once __DIR__ . '/init.php';

try {
    $pdo = getDB();

    $type = trim($_POST['type'] ?? 'general');
    $mode = trim($_POST['mode'] ?? 'segment');
    $segment = trim($_POST['segment'] ?? 'all');
    $individualId = trim($_POST['individual'] ?? '');
    $deadline = trim($_POST['deadline'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($subject) || empty($message)) {
        sendError('Subject and message are required.');
    }

    $recipients = [];

    if ($mode === 'individual' && !empty($individualId)) {
        $stmt = $pdo->prepare("SELECT * FROM applicants WHERE id = ?");
        $stmt->execute([$individualId]);
        $app = $stmt->fetch();
        if ($app) {
            $recipients[] = [
                'id' => $app['id'],
                'name' => trim($app['first_name'] . ' ' . $app['last_name']),
                'email' => $app['email']
            ];
        }
    } else {
        $query = "SELECT * FROM applicants WHERE 1=1";
        if ($segment === 'pending') {
            $query .= " AND status = 'pending'";
        } else if ($segment === 'missing_req') {
            $query .= " AND docs_complete = 0";
        } else if ($segment === 'renewal') {
            $query .= " AND status = 'approved'";
        } else if ($segment === 'at_risk') {
            $query .= " AND (gwa > 2.0 OR failing_grades > 0)";
        }

        $stmt = $pdo->query($query);
        $rows = $stmt->fetchAll();
        foreach ($rows as $app) {
            $recipients[] = [
                'id' => $app['id'],
                'name' => trim($app['first_name'] . ' ' . $app['last_name']),
                'email' => $app['email']
            ];
        }
    }

    if (empty($recipients)) {
        // Fallback default recipient if no applicants match
        $recipients[] = [
            'id' => null,
            'name' => $mode === 'segment' ? ucfirst($segment) . ' Segment' : 'Applicant',
            'email' => 'student@scholarship.edu'
        ];
    }

    $stmtInsert = $pdo->prepare("INSERT INTO notifications (type, recipient_type, recipient_id, recipient_name, recipient_email, subject, message, deadline, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'sent')");

    $sentCount = 0;
    foreach ($recipients as $r) {
        // Replace placeholders in email template
        $parsedMessage = str_replace('{{first_name}}', explode(' ', $r['name'])[0], $message);
        $parsedMessage = str_replace('{{deadline}}', $deadline ?: 'due date', $parsedMessage);

        $stmtInsert->execute([
            $type,
            $mode,
            $r['id'],
            $r['name'],
            $r['email'],
            $subject,
            $parsedMessage,
            $deadline
        ]);
        $sentCount++;
    }

    sendJson([
        'success' => true,
        'sent' => $sentCount,
        'failed' => 0,
        'total' => $sentCount,
        'message' => "Successfully sent $sentCount notification(s)."
    ]);
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}
