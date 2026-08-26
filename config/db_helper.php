    <?php
    require_once __DIR__ . '/database.php';
    require_once __DIR__ . '/../database/init_db.php';

    function getDB(): PDO {
        static $pdo = null;
        if ($pdo === null) {
            $pdo = initDatabase();
        }
        return $pdo;
    }
