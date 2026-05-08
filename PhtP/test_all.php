<?php
// Test script to check all support pages for errors
ob_start();
$errors = [];

$files = [
    'C:\xampp\htdocs\pfeeeee\PhtP\includes\header.php',
    'C:\xampp\htdocs\pfeeeee\PhtP\includes\functions.php',
    'C:\xampp\htdocs\pfeeeee\PhtP\includes\auth.php',
    'C:\xampp\htdocs\pfeeeee\PhtP\includes\i18n.php',
    'C:\xampp\htdocs\pfeeeee\PhtP\ajax\api.php',
    'C:\xampp\htdocs\pfeeeee\PhtP\support\dashboard.php',
    'C:\xampp\htdocs\pfeeeee\PhtP\support\incoming-requests.php',
    'C:\xampp\htdocs\pfeeeee\PhtP\support\training.php',
    'C:\xampp\htdocs\pfeeeee\PhtP\support\escalation-history.php',
    'C:\xampp\htdocs\pfeeeee\PhtP\support\history.php',
    'C:\xampp\htdocs\pfeeeee\PhtP\support\analytics.php',
    'C:\xampp\htdocs\pfeeeee\PhtP\support\messages.php',
    'C:\xampp\htdocs\pfeeeee\PhtP\support\assist.php',
    'C:\xampp\htdocs\pfeeeee\PhtP\support\customize-dashboards.php'
];

foreach ($files as $file) {
    $output = [];
    $returnCode = 0;
    exec('C:\xampp\php\php.exe -l "' . $file . '" 2>&1', $output, $returnCode);
    if ($returnCode !== 0) {
        $errors[] = ['file' => $file, 'errors' => $output];
    }
}

if (empty($errors)) {
    echo "All files have no syntax errors.\n";
} else {
    echo "Found syntax errors:\n";
    foreach ($errors as $err) {
        echo "File: " . basename($err['file']) . "\n";
        foreach ($err['errors'] as $line) {
            echo "  $line\n";
        }
    }
}

// Check for common issues in the code
echo "\nChecking for common issues...\n";
foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Check for missing commas in json_encode (a common issue)
    if (preg_match("/json_encode\(\[.*?\]\)/s", $content, $matches)) {
        // Check for missing commas between array elements
        if (preg_match("/'[^']*'\s*=>\s*[^,;\]]+[^,;\]]/s", $matches[0])) {
            echo basename($file) . ": Possible missing comma in json_encode\n";
        }
    }
    
    // Check for unescaped quotes in HTML attributes
    if (preg_match('/onclick="[^"]*".*?>/', $content)) {
        // This is OK for our case as we're using JS functions
    }
}

echo "\nDone.\n";
