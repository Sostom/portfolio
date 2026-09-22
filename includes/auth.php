<?php
// Gestion des sessions et authentification admin

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => false, // à true en HTTPS
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

function require_admin(string $redirect = '/portfolio/admin/login.php'): void {
    if (empty($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
        header('Location: ' . $redirect);
        exit;
    }
}

function is_admin(): bool {
    return !empty($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true;
}

function admin_login(int $user_id): void {
    session_regenerate_id(true);
    $_SESSION['admin_logged'] = true;
    $_SESSION['admin_user_id'] = $user_id;
}

function admin_logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
}
