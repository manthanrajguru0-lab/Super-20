<?php
/*
 * Simple brute-force protection backed by the login_attempts table.
 * Blocks a (ip + identifier) pair after $max failures within $window seconds.
 */

function throttle_key(string $ident): string
{
    $ip = $_SERVER["REMOTE_ADDR"] ?? "unknown";
    return substr($ip . "|" . strtolower(trim($ident)), 0, 150);
}

function throttle_blocked(mysqli $conn, string $key, int $max = 5, int $window = 900): bool
{
    $stmt = $conn->prepare(
        "SELECT COUNT(*) FROM login_attempts
         WHERE attempt_key = ? AND attempted_at > (NOW() - INTERVAL ? SECOND)"
    );
    if (!$stmt) {
        return false; // table missing: do not lock everyone out
    }
    $stmt->bind_param("si", $key, $window);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count >= $max;
}

function throttle_fail(mysqli $conn, string $key): void
{
    $stmt = $conn->prepare("INSERT INTO login_attempts (attempt_key) VALUES (?)");
    if ($stmt) {
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $stmt->close();
    }
}

function throttle_clear(mysqli $conn, string $key): void
{
    $stmt = $conn->prepare("DELETE FROM login_attempts WHERE attempt_key = ?");
    if ($stmt) {
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $stmt->close();
    }
}
