<?php
$host = 'localhost';
$db   = 'reclise_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Check all tables
$tables = ['users', 'requests', 'messages', 'training_sessions', 'user_training_registrations', 'assist_guides', 'audit_logs', 'user_page_history', 'user_customizations'];

foreach ($tables as $table) {
    echo "Table: $table\n";
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "  Columns: " . implode(', ', $cols) . "\n";
    } catch (Exception $e) {
        echo "  ERROR: " . $e->getMessage() . "\n";
    }
}
?>
