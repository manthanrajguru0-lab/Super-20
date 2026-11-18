<?php
/*
 * Create (or reset) an admin account from the command line:
 *     php setup/create_admin.php
 */
if (PHP_SAPI !== "cli") {
    http_response_code(403);
    exit("Run this from the command line.");
}

require_once __DIR__ . "/../config/database.php";

echo "Admin username: ";
$username = trim((string)fgets(STDIN));

echo "Admin password (min 10 characters, typed text is visible): ";
$password = rtrim((string)fgets(STDIN), "\r\n");

if ($username === "" || strlen($password) < 10) {
    exit("Username required and password must be at least 10 characters.\n");
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    "INSERT INTO admin (username, password) VALUES (?, ?)
     ON DUPLICATE KEY UPDATE password = VALUES(password)"
);
$stmt->bind_param("ss", $username, $hash);

echo $stmt->execute()
    ? "Admin '$username' saved.\n"
    : "Failed: " . $stmt->error . "\n";
