<?php
session_start();
require_once 'config/db.php';

if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? 'user';
    $stmt = $pdo->prepare("INSERT INTO audit_logs (action, user_id, details) VALUES ('LOGOUT', ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $role . ' logged out']);
}

$_SESSION = [];
session_destroy();
header("Location: login.php");
exit;
