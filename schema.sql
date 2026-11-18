-- Super20 Academy - database schema (fresh install)
-- Works on MySQL 5.7+/8 and MariaDB (XAMPP).
-- Run:  mysql -u root -p < setup/schema.sql

CREATE DATABASE IF NOT EXISTS super20_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE super20_db;

CREATE TABLE IF NOT EXISTS admin (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50)  NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL              -- password_hash() output
);

CREATE TABLE IF NOT EXISTS batches (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    batch_name VARCHAR(100) NOT NULL,
    course     VARCHAR(100) NOT NULL,
    start_time TIME NULL,
    end_time   TIME NULL,
    start_date DATE NULL,
    status     VARCHAR(20) NOT NULL DEFAULT 'Active'
);

CREATE TABLE IF NOT EXISTS students (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    student_code   VARCHAR(30)  NOT NULL UNIQUE,
    full_name      VARCHAR(100) NOT NULL,
    mobile         VARCHAR(20)  NULL,
    parent_name    VARCHAR(100) NULL,
    parent_contact VARCHAR(20)  NULL,
    address        TEXT NULL,
    course         VARCHAR(100) NULL,
    batch          VARCHAR(100) NULL,           -- stores batches.batch_name
    joining_date   DATE NULL,
    total_fees     DECIMAL(10,2) NOT NULL DEFAULT 0,
    status         VARCHAR(20) NOT NULL DEFAULT 'Active',
    password       VARCHAR(255) NULL            -- NULL = first login with mobile number
);

CREATE TABLE IF NOT EXISTS attendance (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    student_id      INT NOT NULL,
    batch_id        INT NOT NULL,
    attendance_date DATE NOT NULL,
    status          VARCHAR(20) NOT NULL,
    UNIQUE KEY uq_student_day (student_id, attendance_date),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS fees (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    student_id   INT NOT NULL,
    amount       DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_mode VARCHAR(30) NULL,
    receipt_no   VARCHAR(50) NULL,
    remarks      VARCHAR(255) NULL,
    status       ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Approved',
    reference_no VARCHAR(40) NULL UNIQUE,       -- student's UPI/transaction ID
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS tests (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    batch_id    INT NOT NULL,
    test_name   VARCHAR(100) NOT NULL,
    subject     VARCHAR(100) NULL,
    test_date   DATE NULL,
    total_marks INT NOT NULL DEFAULT 100
);

CREATE TABLE IF NOT EXISTS marks (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    test_id        INT NOT NULL,
    student_id     INT NOT NULL,
    marks_obtained DECIMAL(6,2) NOT NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_test_student (test_id, student_id),
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS timetable (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    batch_id    INT NOT NULL,
    day_of_week VARCHAR(15) NOT NULL,
    subject     VARCHAR(100) NOT NULL,
    start_time  TIME NULL,
    end_time    TIME NULL,
    room_no     VARCHAR(30) NULL
);

CREATE TABLE IF NOT EXISTS notices (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200) NOT NULL,
    message     TEXT NOT NULL,
    notice_date DATE NULL,
    status      VARCHAR(20) NOT NULL DEFAULT 'Active'
);

-- Brute-force protection for both logins
CREATE TABLE IF NOT EXISTS login_attempts (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    attempt_key  VARCHAR(150) NOT NULL,
    attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_key_time (attempt_key, attempted_at)
);

-- Dedicated least-privilege database user (change the password!):
-- CREATE USER 'super20_user'@'localhost' IDENTIFIED BY 'CHANGE_ME_STRONG_PASSWORD';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON super20_db.* TO 'super20_user'@'localhost';
-- FLUSH PRIVILEGES;
