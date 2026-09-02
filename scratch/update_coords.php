<?php
require_once __DIR__ . '/../config/db_helper.php';

$pdo = getDB();

$updatesScholars = [
    ['Maasin City, Southern Leyte', 10.1333, 124.8333, '20230001'],
    ['Hilongos, Leyte', 10.3739, 124.7497, '20230004'],
    ['Sogod, Southern Leyte', 10.3833, 124.9833, '20230101'],
    ['Malitbog, Southern Leyte', 10.1500, 125.0000, '20230102'],
    ['Macrohon, Southern Leyte', 10.0833, 124.9333, '20230103'],
    ['Bontoc, Southern Leyte', 10.3500, 124.9667, '20230104'],
    ['Maasin City, Southern Leyte', 10.1500, 124.8500, '20230005'],
    ['Saint Bernard, Southern Leyte', 10.3333, 125.1333, '20230105'],
    ['Liloan, Southern Leyte', 10.1667, 125.1333, '20230106'],
    ['Padre Burgos, Southern Leyte', 10.0389, 124.9750, '20230107']
];

$stmt1 = $pdo->prepare("UPDATE scholars SET address = ?, latitude = ?, longitude = ? WHERE student_id = ?");
foreach ($updatesScholars as $u) {
    $stmt1->execute($u);
}

$updatesApps = [
    [10.1333, 124.8333, '20230001'],
    [10.0833, 124.9333, '20230002'],
    [10.3333, 124.7833, '20230003'],
    [10.3739, 124.7497, '20230004'],
    [10.1500, 124.8500, '20230005'],
    [10.0389, 124.9750, '20230006']
];

$stmt2 = $pdo->prepare("UPDATE applicants SET latitude = ?, longitude = ? WHERE student_id = ?");
foreach ($updatesApps as $u) {
    $stmt2->execute($u);
}

echo "Coordinates updated successfully for all scholars and applicants!";
