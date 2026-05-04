<?php
require_once __DIR__ . '/auth.php';

function is_logged_in() {
    return Auth::check();
}

function require_login() {
    Auth::require();
}

function require_role($role) {
    Auth::requireRole($role);
}

function get_user_by_id($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function log_audit($pdo, $action, $details, $user_id = null) {
    if ($user_id === null && Auth::check()) {
        $user_id = $_SESSION['user_id'];
    }
    $stmt = $pdo->prepare("INSERT INTO audit_logs (action, user_id, details) VALUES (?, ?, ?)");
    $stmt->execute([$action, $user_id, $details]);
}

function to_camel($row) {
    if (!is_array($row)) return $row;
    $result = [];
    foreach ($row as $key => $value) {
        $camel = preg_replace_callback('/_([a-z])/', function ($m) {
            return strtoupper($m[1]);
        }, $key);
        $result[$camel] = $value;
    }
    return $result;
}

function to_camel_all($rows) {
    return array_map('to_camel', $rows);
}

function t_status($status) {
    $map = [
        'new' => 'statusNew',
        'in_progress' => 'statusInProgress',
        'resolved' => 'statusResolved',
        'escalated' => 'statusEscalated',
        'closed' => 'statusClosed'
    ];
    $key = $map[$status] ?? 'statusNew';
    return t($key);
}
