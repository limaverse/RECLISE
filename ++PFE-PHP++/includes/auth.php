<?php
class Auth {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function check() {
        return isset($_SESSION['user_id']);
    }

    public static function require() {
        self::start();
        if (!self::check()) {
            header('Location: ../login.php');
            exit;
        }
    }

    public static function requireRole($role) {
        self::start();
        if (!self::check() || $_SESSION['role'] !== $role) {
            header('Location: ../login.php');
            exit;
        }
    }

    public static function user() {
        self::start();
        if (!self::check()) return null;
        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['email'] ?? '',
            'full_name' => $_SESSION['full_name'] ?? '',
            'role' => $_SESSION['role'] ?? '',
            'lang' => $_SESSION['lang'] ?? 'fr'
        ];
    }

    public static function login($user) {
        self::start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        if (!isset($_SESSION['lang'])) {
            $_SESSION['lang'] = 'fr';
        }
    }

    public static function logout() {
        self::start();
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

    public static function redirectByRole() {
        $role = $_SESSION['role'];
        if ($role === 'admin') header('Location: admin/dashboard.php');
        elseif ($role === 'support') header('Location: support/dashboard.php');
        else header('Location: user/dashboard.php');
        exit;
    }
}
