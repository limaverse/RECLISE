<?php
$files = [
    'C:\xampp\htdocs\pfeeeee\PhtP\support\dashboard.php',
    'C:\xampp\htdocs\pfeeeee\PhtP\support\incoming-requests.php',
    'C:\xampp\htdocs\pfeeeee\PhtP\support\training.php',
    'C:\xampp\htdocs\pfeeeee\PhtP\support\escalation-history.php',
    'C:\xampp\htdocs\pfeeeee\PhtP\support\history.php',
    'C:\xampp\htdocs\pfeeeee\PhtP\support\analytics.php',
    'C:\xampp\htdocs\pfeeeee\PhtP\support\messages.php',
    'C:\xampp\htdocs\pfeeeee\PhtP\support\assist.php',
    'C:\xampp\htdocs\pfeeeee\PhtP\support\customize-dashboards.php',
    'C:\xampp\htdocs\pfeeeee\PhtP\ajax\api.php'
];

$issues = [];

foreach ($files as $file) {
    $content = file_get_contents($file);
    $fileName = basename($file);
    $fileIssues = [];
    
    // Check for to_camel_all() key mismatch
    if (strpos($content, 'to_camel_all') !== false) {
        // Check if array keys use snake_case instead of camelCase
        if (preg_match_all('/\\[\s*[\'"]([a-z_]+)[\'"]\s*\]/', $content, $matches)) {
            foreach ($matches[1] as $key) {
                if (strpos($key, '_') !== false && strpos($key, 'request_') === false) {
                    // Could be a database column name that should be camelCase
                    $fileIssues[] = "Possible key mismatch: $key (should be camelCase after to_camel_all())";
                }
            }
        }
    }
    
    // Check for missing NULL checks on date fields
    if (preg_match_all('/strtotime\s*\(\s*\$([a-z]+)\[\s*[\'"](\w+)[\'"]\s*\]/', $content, $matches)) {
        foreach ($matches[2] as $dateField) {
            if (strpos($content, $dateField . "'] ?") === false && 
                strpos($content, $dateField . '"] ??') === false) {
                $fileIssues[] = "Missing NULL check for date field: $dateField";
            }
        }
    }
    
    // Check for syntax issues with onclick handlers
    if (preg_match_all('/onclick\s*=\s*"([^"]*?)(\(\s*\$\w+\[\s*[\'"]\w+[\'"]\s*\)[^"]*?)"/', $content, $matches)) {
        foreach ($matches[2] as $handler) {
            if (strpos($handler, '))') !== false) {
                $fileIssues[] = "Possible syntax error in onclick handler: $handler";
            }
        }
    }
    
    if (!empty($fileIssues)) {
        $issues[$fileName] = $fileIssues;
    }
}

if (empty($issues)) {
    echo "No common issues found.\n";
} else {
    echo "Issues found:\n\n";
    foreach ($issues as $file => $fileIssues) {
        echo "File: $file\n";
        foreach ($fileIssues as $issue) {
            echo "  - $issue\n";
        }
        echo "\n";
    }
}
