<?php
require_once 'config/db.php';
$stmt = $pdo->query("SELECT email, password_hash FROM users LIMIT 5");
while ($row = $stmt->fetch()) {
    echo $row['email'] . ": " . $row['password_hash'] . " (Length: " . strlen($row['password_hash']) . ")\n";
}
