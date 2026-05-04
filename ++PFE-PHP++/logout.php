<?php
session_start();
require_once 'config/db.php';

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("INSERT INTO audit_logs (action, user_id, details) VALUES ('LOGOUT', ?, 'User logged out')");
    $stmt->execute([$_SESSION['user_id']]);
}

$_SESSION = [];
session_destroy();
header("Location: login.php");
exit;
