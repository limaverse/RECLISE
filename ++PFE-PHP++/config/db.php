<?php
$host = 'localhost';
$dbname = 'reclise_db';
$user = 'root';
$pass = '';

function getDb() {
    static $pdo = null;
    if ($pdo === null) {
        global $host, $dbname, $user, $pass;
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $user, $pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
    }
    return $pdo;
}

$pdo = getDb();
