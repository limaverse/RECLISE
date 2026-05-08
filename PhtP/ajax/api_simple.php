<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Simple test without database
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'get_request') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) {
        echo json_encode(["success" => false, "message" => "Request ID required"]);
        exit;
    }
    
    // Return dummy data for testing
    echo json_encode([
        "success" => true,
        "request" => [
            "id" => $id,
            "title" => "Test Request",
            "type" => "request",
            "status" => "new",
            "attachments" => []
        ]
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid action"]);
}
