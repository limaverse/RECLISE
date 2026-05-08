-- Create user_page_history table for persisting Quick Actions
CREATE TABLE IF NOT EXISTS user_page_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    page VARCHAR(255) NOT NULL,
    visited_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_page (user_id, page),
    INDEX idx_user_id (user_id),
    INDEX idx_visited_at (visited_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add role column to audit_logs if not exists
ALTER TABLE audit_logs 
ADD COLUMN IF NOT EXISTS role VARCHAR(50) NULL AFTER user_id;
