<?php
require_once 'config/db.php';
$stmt = $pdo->query("SELECT DISTINCT role FROM users");
while ($row = $stmt->fetch()) {
    echo $row['role'] . "\n";
}
