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
        $_SESSION['theme'] = 'dark';
    }

    public static function getBaseUrl() {
        $script = $_SERVER['SCRIPT_NAME']; // e.g. /pfeeeee/PhtP/admin/dashboard.php
        $path = explode('/', $script);
        // We know login.php is at the root of the project.
        // We can search for the project folder name, but let's just assume PhtP is the root.
        $rootIndex = array_search('PhtP', $path);
        if ($rootIndex !== false) {
            return implode('/', array_slice($path, 0, $rootIndex + 1)) . '/';
        }
        return '/';
    }

    public static function require() {
        self::start();
        if (!self::check()) {
            header('Location: ' . self::getBaseUrl() . 'login.php');
            exit;
        }
    }

    public static function requireRole($role) {
        self::start();
        if (!self::check() || $_SESSION['role'] !== $role) {
            header('Location: ' . self::getBaseUrl() . 'login.php');
            exit;
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
        $role = $_SESSION['role'] ?? 'user';
        $base = self::getBaseUrl();
        if ($role === 'admin') header('Location: ' . $base . 'admin/dashboard.php');
        elseif ($role === 'support') header('Location: ' . $base . 'support/dashboard.php');
        else header('Location: ' . $base . 'user/dashboard.php');
        exit;
    }
}
