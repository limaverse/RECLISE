<?php
session_start();
require_once 'config/database.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS user_page_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        page VARCHAR(255) NOT NULL,
        visited_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_user_page (user_id, page),
        INDEX idx_user_id (user_id),
        INDEX idx_visited_at (visited_at),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "Table user_page_history created successfully!<br>";
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'user_page_history'");
    if ($stmt->rowCount() > 0) {
        echo "Table verified successfully!";
    } else {
        echo "Table creation failed!";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
