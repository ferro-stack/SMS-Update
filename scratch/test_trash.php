<?php
require_once __DIR__ . '/../config/db_helper.php';

$pdo = getDB();

// 1. Soft delete applicant 1
$_POST['id'] = 1;
ob_start();
require __DIR__ . '/../api/delete_applicant.php';
$delOut = ob_get_clean();
echo "Delete output: " . $delOut . "\n";

// 2. Check Trash Bin items
$stmt = $pdo->query("SELECT * FROM deleted_items");
$items = $stmt->fetchAll();
echo "Trash Bin Items Count: " . count($items) . "\n";
print_r($items);

if (count($items) > 0) {
    $trashId = $items[0]['id'];
    $_POST['id'] = $trashId;
    ob_start();
    require __DIR__ . '/../api/restore_deleted_item.php';
    $restoreOut = ob_get_clean();
    echo "Restore output: " . $restoreOut . "\n";
}

// 3. Verify restored applicant in applicants table
$stmtApp = $pdo->query("SELECT id, student_id, first_name, last_name FROM applicants WHERE id = 1");
print_r($stmtApp->fetch());
