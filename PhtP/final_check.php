<?php
// Comprehensive test to check for runtime errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Mock session
$_SESSION = [
    'user_id' => 1,
    'role' => 'support',
    'lang' => 'fr',
    'theme' => 'dark'
];

// Mock $pdo (we'll just check syntax and basic logic)
// Include files and check for obvious issues

$files = [
    'C:/xampp/htdocs/pfeeeee/PhtP/support/dashboard.php',
    'C:/xampp/htdocs/pfeeeee/PhtP/support/incoming-requests.php',
    'C:/xampp/htdocs/pfeeeee/PhtP/support/training.php',
    'C:/xampp/htdocs/pfeeeee/PhtP/support/escalation-history.php',
    'C:/xampp/htdocs/pfeeeee/PhtP/support/history.php',
    'C:/xampp/htdocs/pfeeeee/PhtP/support/analytics.php',
    'C:/xampp/htdocs/pfeeeee/PhtP/support/messages.php',
    'C:/xampp/htdocs/pfeeeee/PhtP/support/assist.php',
    'C:/xampp/htdocs/pfeeeee/PhtP/support/customize-dashboards.php'
];

$issues = [];

foreach ($files as $file) {
    $content = file_get_contents($file);
    $fileName = basename($file);
    
    // Check for common runtime issues
    $fileIssues = [];
    
    // 1. Check for missing NULL checks on date fields
    if (preg_match_all('/\$([a-z_]+)\[\s*[\'"]([a-z_]+)[\'"]\s*\]/', $content, $matches)) {
        foreach ($matches[2] as $idx => $key) {
            if (strpos($key, 'At') !== false || strpos($key, 'Date') !== false) {
                // Check if there's a NULL check
                $pattern = '/\$' . $matches[1][$idx] . '\[.*' . $key . '.*\]\s*\?\s*:/';
                if (!preg_match($pattern, $content)) {
                    // Check if ?? null is used
                    if (strpos($content, $matches[1][$idx] . "['" . $key . "'] ??") === false) {
                        $fileIssues[] = "Possible missing NULL check for: $key";
                    }
                }
            }
        }
    }
    
    // 2. Check for to_camel_all() key mismatches
    if (strpos($content, 'to_camel_all') !== false) {
        // After to_camel_all(), keys should be camelCase
        // Check if any snake_case keys are used after to_camel_all()
        if (preg_match_all('/\$([a-z]+)\[\s*[\'"]([a-z]+_[a-z_]+)[\'"]\s*\]/', $content, $matches)) {
            foreach ($matches[2] as $key) {
                $fileIssues[] = "Possible snake_case key after to_camel_all(): $key (use camelCase)";
            }
        }
    }
    
    // 3. Check for SQL syntax issues
    if (preg_match_all('/SELECT.*FROM.*ORDER\s+BY/s', $content, $matches)) {
        foreach ($matches[0] as $sql) {
            // Check for malformed subqueries
            if (preg_match('/\(\s*SELECT/', $sql) && strpos($sql, '(') > strpos($sql, ')')) {
                $fileIssues[] = "Possible SQL subquery syntax issue";
            }
        }
    }
    
    if (!empty($fileIssues)) {
        $issues[$fileName] = $fileIssues;
    }
}

if (empty($issues)) {
    echo "No runtime issues found in code analysis.\n";
} else {
    echo "Potential issues found:\n\n";
    foreach ($issues as $file => $fileIssues) {
        echo "$file:\n";
        foreach ($fileIssues as $issue) {
            echo "  - $issue\n";
        }
        echo "\n";
    }
}

echo "Check complete.\n";
