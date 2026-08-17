<?php
require_once __DIR__ . '/init.php';

try {
    $pdo = getDB();

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $type = trim($_POST['type'] ?? 'Academic');
        $gwaReq = (float)($_POST['gwa_requirement'] ?? $_POST['gwaReq'] ?? 2.0);
        $slots = (int)($_POST['slots'] ?? 0);
        $coverage = trim($_POST['coverage'] ?? '');
        $status = trim($_POST['status'] ?? 'active');

        if (empty($name) || empty($code)) {
            sendError('Scholarship name and code are required.');
        }

        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE scholarships SET name = ?, code = ?, description = ?, type = ?, gwa_requirement = ?, slots = ?, slots_available = ?, coverage = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $code, $description, $type, $gwaReq, $slots, $slots, $coverage, $status, $id]);
            sendJson(['success' => true, 'id' => $id, 'message' => 'Scholarship updated successfully.']);
        } else {
            $stmt = $pdo->prepare("INSERT INTO scholarships (name, code, description, type, gwa_requirement, slots, slots_available, coverage, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $code, $description, $type, $gwaReq, $slots, $slots, $coverage, $status]);
            sendJson(['success' => true, 'id' => (int)$pdo->lastInsertId(), 'message' => 'Scholarship added successfully.']);
        }
    }

    $stmt = $pdo->query("SELECT * FROM scholarships ORDER BY id DESC");
    $rows = $stmt->fetchAll();

    $data = array_map(function($r) {
        return [
            'id' => (int)$r['id'],
            'name' => $r['name'],
            'code' => $r['code'],
            'description' => $r['description'],
            'type' => $r['type'],
            'gwaRequirement' => (float)$r['gwa_requirement'],
            'gwa_requirement' => (float)$r['gwa_requirement'],
            'slots' => (int)$r['slots'],
            'slotsAvailable' => (int)$r['slots_available'],
            'slots_available' => (int)$r['slots_available'],
            'coverage' => $r['coverage'],
            'status' => $r['status']
        ];
    }, $rows);

    sendJson(['success' => true, 'data' => $data]);
} catch (Exception $e) {
    sendError($e->getMessage(), 500);
}
