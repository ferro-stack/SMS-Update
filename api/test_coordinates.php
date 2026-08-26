<?php
require_once __DIR__ . '/init.php';

try {
    $pdo = getDB();

    $stmt = $pdo->query("
        SELECT id, first_name, last_name, address, latitude, longitude
        FROM applicants
        ORDER BY id DESC
    ");

    header('Content-Type: text/html; charset=utf-8');

    echo "<h2>Applicant Coordinates</h2>";
    echo "<table border='1' cellpadding='8'>";
    echo "<tr>
            <th>ID</th>
            <th>Name</th>
            <th>Address</th>
            <th>Latitude</th>
            <th>Longitude</th>
          </tr>";

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['address']) . "</td>";
        echo "<td>" . htmlspecialchars($row['latitude'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['longitude'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }

    echo "</table>";

} catch (Exception $e) {
    echo "Error: " . htmlspecialchars($e->getMessage());
}