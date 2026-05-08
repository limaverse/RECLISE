<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Check if there's any output before this point
$output = ob_get_contents();
if ($output) {
    echo "Output before JSON: " . $output;
    exit;
}

header('Content-Type: application/json');

// Simple test
echo json_encode(["success" => true, "message" => "API is working"]);
exit;
