-- Run this ONLY if you already have a super20_db from the old version.
-- Needs MariaDB (XAMPP) or MySQL 8.0.29+; on older MySQL drop "IF NOT EXISTS" and run each line once.
USE super20_db;

ALTER TABLE students ADD COLUMN IF NOT EXISTS password VARCHAR(255) NULL;

ALTER TABLE fees ADD COLUMN IF NOT EXISTS status ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Approved';
ALTER TABLE fees ADD COLUMN IF NOT EXISTS reference_no VARCHAR(40) NULL;
ALTER TABLE fees ADD UNIQUE INDEX uq_fees_reference (reference_no);

CREATE TABLE IF NOT EXISTS login_attempts (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    attempt_key  VARCHAR(150) NOT NULL,
    attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_key_time (attempt_key, attempted_at)
);

-- Old student passwords were saved in plain text in students.password and were never used for login.
-- Clear them so students sign in with their mobile number once and choose a new (hashed) password:
UPDATE students SET password = NULL WHERE password IS NOT NULL AND password NOT LIKE '$2y$%';
