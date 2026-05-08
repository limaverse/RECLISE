<?php
require_once 'C:\xampp\htdocs\pfeeeee\PhtP\includes\auth.php';
require_once 'C:\xampp\htdocs\pfeeeee\PhtP\includes\functions.php';
require_once 'C:\xampp\htdocs\pfeeeee\PhtP\includes\i18n.php';
require_once 'C:\xampp\htdocs\pfeeeee\PhtP\config\database.php';

// Simulate a session
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'support';
$_SESSION['lang'] = 'fr';

// Test the api.php with a GET request
$_GET['action'] = 'get_request';
$_GET['id'] = 1;

ob_start();
include 'C:\xampp\htdocs\pfeeeee\PhtP\ajax\api.php';
$output = ob_get_clean();

echo "Output: " . substr($output, 0, 200) . "\n";
echo "JSON valid: " . (json_decode($output) ? "Yes" : "No") . "\n";
if (!json_decode($output)) {
    echo "Raw output: $output\n";
}

// Test json_encode with missing comma
echo "\nTesting json_encode syntax...\n";
$test = ['success' => false, 'message' => 'test'];
echo "Correct: " . json_encode($test) . "\n";

// This should fail (missing comma)
// $bad = ['success' => false 'message' => 'test']; // This would be a parse error
echo "No syntax errors in test file.\n";
