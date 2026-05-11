<?php
require_once 'C:/xampp/htdocs/pfeeeee/PhtP/config/database.php';

echo "=== TABLES ===\n";
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $t) echo "$t\n";

echo "\n=== MESSAGES COLUMNS ===\n";
try {
    $cols = $pdo->query('DESCRIBE messages')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) echo "{$c['Field']} ({$c['Type']})\n";
} catch (Exception $e) { echo "Error: " . $e->getMessage() . "\n"; }

echo "\n=== REQUESTS COLUMNS ===\n";
try {
    $cols = $pdo->query('DESCRIBE requests')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) echo "{$c['Field']} ({$c['Type']})\n";
} catch (Exception $e) { echo "Error: " . $e->getMessage() . "\n"; }

echo "\n=== TRAINING_SESSIONS COLUMNS ===\n";
try {
    $cols = $pdo->query('DESCRIBE training_sessions')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) echo "{$c['Field']} ({$c['Type']})\n";
} catch (Exception $e) { echo "Error: " . $e->getMessage() . "\n"; }

echo "\n=== TRAINING_REGISTRATIONS TABLE ===\n";
try {
    $pdo->query('SELECT 1 FROM user_training_registrations LIMIT 1');
    echo "EXISTS\n";
} catch (Exception $e) { echo "NOT FOUND: " . $e->getMessage() . "\n"; }
