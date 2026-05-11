<?php
require_once 'config/db.php';
$stmt = $pdo->query('SELECT count(*) FROM ref_sub_tasks');
echo $stmt->fetchColumn();
