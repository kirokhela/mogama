<?php
// includes/auth.php
// Simple admin auth helpers (hardcoded admin/password)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check credentials against hardcoded admin.
 * Change the password here when ready.
 */
function check_admin_credentials(string $username, string $password): bool {
    return $username === 'admin' && $password === 'password';
}

/**
 * Call on pages that must be admin-only.
 */
function require_admin(): void {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header('Location: login.php');
        exit;
    }
}

/**
 * Return true if current session is admin.
 */
function is_admin(): bool {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}

/**
 * Log admin in (set session).
 */
function do_admin_login(string $username): void {
    // caller already validated credentials
    $_SESSION['logged_in'] = true;
    $_SESSION['role'] = 'admin';
    $_SESSION['username'] = $username;
}

/**
 * Log out admin (clear session).
 */
function do_logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}