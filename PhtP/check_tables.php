<?php
require_once 'C:/xampp/htdocs/pfeeeee/PhtP/config/database.php';

echo "=== REQUESTS ===\n";
$cols = $pdo->query('DESCRIBE requests')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) echo "{$c['Field']} ({$c['Type']})\n";

echo "\n=== MESSAGES ===\n";
try {
    $cols = $pdo->query('DESCRIBE messages')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) echo "{$c['Field']} ({$c['Type']})\n";
} catch (Exception $e) { echo "Error: " . $e->getMessage() . "\n"; }

echo "\n=== TRAINING_SESSIONS ===\n";
try {
    $cols = $pdo->query('DESCRIBE training_sessions')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) echo "{$c['Field']} ({$c['Type']})\n";
} catch (Exception $e) { echo "Error: " . $e->getMessage() . "\n"; }

echo "\n=== USER_TRAINING_REGISTRATIONS ===\n";
try {
    $cols = $pdo->query('DESCRIBE user_training_registrations')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) echo "{$c['Field']} ({$c['Type']})\n";
} catch (Exception $e) { echo "Error: " . $e->getMessage() . "\n"; }

echo "\n=== ASSIST_GUIDES ===\n";
try {
    $cols = $pdo->query('DESCRIBE assist_guides')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) echo "{$c['Field']} ({$c['Type']})\n";
} catch (Exception $e) { echo "Error: " . $e->getMessage() . "\n"; }
