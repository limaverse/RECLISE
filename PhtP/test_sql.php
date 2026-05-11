<?php
require_once 'C:/xampp/htdocs/pfeeeee/PhtP/config/database.php';
try {
    $stmt = $pdo->query("SELECT r.*, u.full_name AS requester_name, u.department AS requester_dept 
        FROM requests r 
        LEFT JOIN users u ON r.user_id = u.id 
        WHERE r.status = 'new' 
        ORDER BY r.created_at DESC");
    $results = $stmt->fetchAll();
    echo "SQL works! Found " . count($results) . " rows\n";
} catch (Exception $e) {
    echo "SQL Error: " . $e->getMessage() . "\n";
}
