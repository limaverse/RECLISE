<?php
session_start();
require_once 'config/database.php';

echo "<h2>RecLise Installation</h2>";

// Create user_page_history table
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_page_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        page VARCHAR(255) NOT NULL,
        visited_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_user_page (user_id, page),
        INDEX idx_user_id (user_id),
        INDEX idx_visited_at (visited_at),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo "<p>✓ user_page_history table created successfully!</p>";
} catch (PDOException $e) {
    echo "<p>✗ Error creating user_page_history: " . $e->getMessage() . "</p>";
}

// Add role column to audit_logs if not exists
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM audit_logs LIKE 'role'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE audit_logs ADD COLUMN role VARCHAR(50) NULL AFTER user_id");
        echo "<p>✓ Added 'role' column to audit_logs</p>";
    } else {
        echo "<p>✓ 'role' column already exists in audit_logs</p>";
    }
} catch (PDOException $e) {
    echo "<p>✗ Error checking audit_logs: " . $e->getMessage() . "</p>";
}

// Create user_customizations table
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_customizations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        widget_name VARCHAR(100) NOT NULL,
        is_visible TINYINT(1) DEFAULT 1,
        display_order INT DEFAULT 0,
        UNIQUE KEY uq_user_widget (user_id, widget_name),
        INDEX idx_user_id (user_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "<p>✓ user_customizations table created successfully!</p>";
} catch (Exception $e) {
    echo "<p>✗ Error creating user_customizations: " . $e->getMessage() . "</p>";
}

echo "<p><strong>Installation complete!</strong></p>";
echo "<p><a href='/pfeeeee/PhtP/login.php'>Go to Login</a></p>";
