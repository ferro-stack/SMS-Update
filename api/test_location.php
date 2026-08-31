_<?php
require_once __DIR__ . '/init.php';

try {
    $pdo = getDB();

    $stmt = $pdo->prepare("
        UPDATE applicants
        SET latitude = ?, longitude = ?
        WHERE id = ?
    ");

    // Example coordinates for Maasin City
    $stmt->execute([
        10.1336,
        124.8447,
        1
    ]);

    echo "Juan's coordinates updated successfully.";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
