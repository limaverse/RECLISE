<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Simulate logged in user
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'user';

$id = 1;
$stmt = $pdo->prepare("SELECT r.*, u.full_name AS requester_name, u.department AS requester_dept FROM requests r LEFT JOIN users u ON r.user_id = u.id WHERE r.id = ?");
$stmt->execute([$id]);
$req = $stmt->fetch(PDO::FETCH_ASSOC);

if ($req) {
    // Get attachments
    $attStmt = $pdo->prepare("SELECT file_name, file_path FROM request_attachments WHERE request_id = ?");
    $attStmt->execute([$id]);
    $req['attachments'] = $attStmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode(["success" => true, "request" => $req]);
} else {
    header('Content-Type: application/json');
    echo json_encode(["success" => false, "message" => "Request not found"]);
}
