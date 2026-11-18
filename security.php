<?php
/*
 * Shared security helpers: hardened session start + CSRF protection.
 * Usage:  require_once ".../includes/security.php";
 *         csrf_protect();   // on pages that handle POST forms
 *         <?php echo csrf_field(); ?>  inside every POST form
 */

if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off");
    session_set_cookie_params([
        "lifetime" => 0,
        "path"     => "/",
        "secure"   => $secure,
        "httponly" => true,
        "samesite" => "Lax",
    ]);
    ini_set("session.use_strict_mode", "1");
    session_start();
}

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

function csrf_token(): string
{
    return $_SESSION["csrf_token"];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars(csrf_token(), ENT_QUOTES, "UTF-8") . '">';
}

function csrf_protect(): void
{
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        return;
    }

    $sent = $_POST["csrf_token"] ?? "";

    if (!is_string($sent) || !hash_equals($_SESSION["csrf_token"], $sent)) {
        http_response_code(400);
        die("Invalid or expired form. Please go back, refresh the page and try again.");
    }
}
