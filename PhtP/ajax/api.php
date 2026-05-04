<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];

function json_response($data) {
    echo json_encode($data);
    exit;
}

function json_error($msg) {
    echo json_encode(['error' => $msg]);
    exit;
}

switch ($action) {
    case 'setTheme':
        $_SESSION['theme'] = $_POST['theme'] ?? 'dark';
        json_response(['status' => 'success']);
        break;

    case 'setLanguage':
        $lang = $_POST['language'] ?? 'fr';
        if (in_array($lang, ['fr', 'en', 'ar'])) {
            $_SESSION['lang'] = $lang;
        }
        json_response(['status' => 'success']);
        break;

    case 'createRequest':
        if ($role !== 'user') json_error('Unauthorized');
        $type = $_POST['type'] ?? 'request';
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = $_POST['category'] ?? 'technical';
        $priority = $_POST['priority'] ?? 'medium';
        if (!$title || !$description) json_error('Title and description required');

        // Auto-assign to support staff with least open requests
        $assignStmt = $pdo->query("SELECT assigned_to, COUNT(*) as cnt FROM requests WHERE status IN ('new', 'in_progress') AND assigned_to IS NOT NULL GROUP BY assigned_to ORDER BY cnt ASC LIMIT 1");
        $assignedTo = 2; // default fallback
        if ($row = $assignStmt->fetch()) { $assignedTo = $row['assigned_to']; }
        
        $stmt = $pdo->prepare("INSERT INTO requests (user_id, type, title, description, category, priority, status, assigned_to) VALUES (?, ?, ?, ?, ?, ?, 'new', ?)");
        $stmt->execute([$userId, $type, $title, $description, $category, $priority, $assignedTo]);
        $reqId = $pdo->lastInsertId();

        log_audit($pdo, 'CREATE_REQUEST', "Request #$reqId created by user #$userId", $userId);
        json_response(['status' => 'success', 'id' => $reqId]);
        break;

    case 'updateRequestStatus':
        if ($role !== 'support' && $role !== 'admin') json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $reply = trim($_POST['reply'] ?? '');
        $validStatuses = ['new', 'in_progress', 'resolved', 'escalated', 'closed'];
        if (!$id || !in_array($status, $validStatuses)) json_error('Invalid status');

        $stmt = $pdo->prepare("UPDATE requests SET status = ?, updated_at = NOW()" . ($reply ? ", support_response = ?" : "") . " WHERE id = ?");
        if ($reply) {
            $stmt->execute([$status, $reply, $id]);
            $pdo->prepare("INSERT INTO messages (request_id, sender_type, sender_id, body) VALUES (?, ?, ?, ?)")
                ->execute([$id, $role, $userId, $reply]);
        } else {
            $stmt->execute([$status, $id]);
        }
        if ($status === 'escalated') {
            $pdo->prepare("UPDATE requests SET escalated_to_admin = 1 WHERE id = ?")->execute([$id]);
        }
        log_audit($pdo, 'UPDATE_STATUS', "Request #$id set to $status", $userId);
        json_response(['status' => 'success']);
        break;

    case 'replyToRequest':
        if (!in_array($role, ['user', 'support', 'admin'])) json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        $body = trim($_POST['body'] ?? '');
        if (!$id || !$body) json_error('Request ID and body required');

        $stmt = $pdo->prepare("INSERT INTO messages (request_id, sender_type, sender_id, body) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id, $role, $userId, $body]);
        $pdo->prepare("UPDATE requests SET updated_at = NOW() WHERE id = ?")->execute([$id]);
        json_response(['status' => 'success']);
        break;

    case 'escalateRequest':
        if ($role !== 'support') json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) json_error('Request ID required');

        $pdo->prepare("UPDATE requests SET escalated_to_admin = 1, status = 'escalated', updated_at = NOW() WHERE id = ?")->execute([$id]);
        $pdo->prepare("INSERT INTO messages (request_id, sender_type, sender_id, body) VALUES (?, 'system', NULL, 'Request escalated to admin')")->execute([$id]);
        log_audit($pdo, 'ESCALATE', "Request #$id escalated to admin", $userId);
        json_response(['status' => 'success']);
        break;

    case 'addUser':
        if ($role !== 'admin') json_error('Unauthorized');
        $name = trim($_POST['fullName'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $newRole = $_POST['role'] ?? 'user';
        $phone = trim($_POST['phone'] ?? '');
        $dept = trim($_POST['department'] ?? '');
        $pass = $_POST['password'] ?? '';
        if (!$name || !$email) json_error('Name and email required');

        $hash = $pass ? hash('sha256', $pass) : hash('sha256', 'demo');
        $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, full_name, role, phone, department) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$email, $hash, $name, $newRole, $phone, $dept]);
        log_audit($pdo, 'ADD_USER', "User added: $name ($newRole)", $userId);
        json_response(['status' => 'success', 'id' => $pdo->lastInsertId()]);
        break;

    case 'updateUser':
        if ($role !== 'admin') json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['fullName'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $dept = trim($_POST['department'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $status = $_POST['status'] ?? 'active';
        if (!$id || !$name || !$email) json_error('Required fields missing');

        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, department = ?, phone = ?, status = ? WHERE id = ?");
        $stmt->execute([$name, $email, $dept, $phone, $status, $id]);
        if (!empty($_POST['password'])) {
            $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([hash('sha256', $_POST['password']), $id]);
        }
        log_audit($pdo, 'UPDATE_USER', "User #$id updated: $name", $userId);
        json_response(['status' => 'success']);
        break;

    case 'getUser':
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) json_error('User ID required');
        $stmt = $pdo->prepare("SELECT id, email, full_name, role, phone, department, status FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = to_camel($stmt->fetch());
        if (!$user) json_error('User not found');
        json_response($user);
        break;

    case 'deleteUser':
        if ($role !== 'admin') json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        if ($id === $userId) json_error('Cannot delete yourself');
        
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
        log_audit($pdo, 'DELETE_USER', "User #$id deleted", $userId);
        json_response(['status' => 'success']);
        break;

    case 'resetPassword':
        if ($role !== 'admin') json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        $pass = $_POST['password'] ?? '';
        if (!$id || !$pass) json_error('Required fields missing');

        $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([hash('sha256', $pass), $id]);
        log_audit($pdo, 'USER_PASSWORD_RESET', "Password reset for user #$id", $userId);
        json_response(['status' => 'success']);
        break;

    case 'delegateRole':
        if ($role !== 'admin') json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        $newRole = $_POST['role'] ?? 'user';
        if (!$id) json_error('User ID required');

        $pdo->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$newRole, $id]);
        log_audit($pdo, 'DELEGATE_ROLE', "User #$id role changed to $newRole", $userId);
        json_response(['status' => 'success']);
        break;

    case 'registerTraining':
        if ($role !== 'user') json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("INSERT INTO training_registrations (session_id, user_id, status) VALUES (?, ?, 'registered') ON DUPLICATE KEY UPDATE status='registered'")->execute([$id, $userId]);
        json_response(['status' => 'success']);
        break;

    case 'addTrainingSession':
        if ($role !== 'support' && $role !== 'admin') json_error('Unauthorized');
        $title = trim($_POST['title'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $date = $_POST['date'] ?? '';
        $duration = (int)($_POST['duration'] ?? 1);
        if (!$title || !$date) json_error('Title and date required');
        $stmt = $pdo->prepare("INSERT INTO training_sessions (title, description, date, duration, created_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $desc, $date, $duration, $userId]);
        log_audit($pdo, 'ADD_TRAINING', "Training session added: $title", $userId);
        json_response(['status' => 'success', 'id' => $pdo->lastInsertId()]);
        break;

    case 'getTrainingSession':
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM training_sessions WHERE id = ?");
        $stmt->execute([$id]);
        json_response(to_camel($stmt->fetch()));
        break;

    case 'updateTrainingSession':
        if ($role !== 'support' && $role !== 'admin') json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        $title = $_POST['title'] ?? '';
        $desc = $_POST['description'] ?? '';
        $date = $_POST['date'] ?? '';
        $duration = (int)($_POST['duration'] ?? 1);
        $pdo->prepare("UPDATE training_sessions SET title=?, description=?, date=?, duration=? WHERE id=?")->execute([$title, $desc, $date, $duration, $id]);
        json_response(['status' => 'success']);
        break;

    case 'deleteTrainingSession':
        if ($role !== 'support' && $role !== 'admin') json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) json_error('Session ID required');
        $pdo->prepare("DELETE FROM training_registrations WHERE session_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM training_sessions WHERE id = ?")->execute([$id]);
        log_audit($pdo, 'DELETE_TRAINING', "Training session deleted: $id", $userId);
        json_response(['status' => 'success']);
        break;

    case 'addGuide':
        if ($role !== 'support' && $role !== 'admin') json_error('Unauthorized');
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $type = $_POST['type'] ?? 'guide';
        $stmt = $pdo->prepare("INSERT INTO guides_coordinators (full_name, email, type) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $type]);
        json_response(['status' => 'success']);
        break;

    case 'getGuide':
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM guides_coordinators WHERE id = ?");
        $stmt->execute([$id]);
        json_response(to_camel($stmt->fetch()));
        break;

    case 'editGuide':
        if ($role !== 'support' && $role !== 'admin') json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $pdo->prepare("UPDATE guides_coordinators SET full_name = ?, email = ? WHERE id = ?")->execute([$name, $email, $id]);
        json_response(['status' => 'success']);
        break;

    case 'deleteGuide':
        if ($role !== 'support' && $role !== 'admin') json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("DELETE FROM guides_coordinators WHERE id = ?")->execute([$id]);
        json_response(['status' => 'success']);
        break;

    case 'addCorrespondence':
        if ($role !== 'support' && $role !== 'admin') json_error('Unauthorized');
        $name = $_POST['name'] ?? $_POST['title'] ?? '';
        $type = $_POST['type'] ?? '';
        $content = $_POST['content'] ?? '';
        $stmt = $pdo->prepare("INSERT INTO correspondences (name, type, content, created_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $type, $content, $userId]);
        json_response(['status' => 'success']);
        break;

    case 'saveCorrespondenceReassign':
        if ($role !== 'admin') json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        $assignee = (int)($_POST['assignee'] ?? 0);
        $pdo->prepare("UPDATE correspondences SET assignee = ? WHERE id = ?")->execute([$assignee ?: null, $id]);
        json_response(['status' => 'success']);
        break;

    case 'saveCorrespondenceTemplate':
        if ($role !== 'admin') json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        $template = $_POST['template'] ?? '';
        $pdo->prepare("UPDATE correspondences SET template = ? WHERE id = ?")->execute([$template, $id]);
        json_response(['status' => 'success']);
        break;

    case 'deleteCorrespondence':
        if ($role !== 'support' && $role !== 'admin') json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("DELETE FROM correspondences WHERE id = ?")->execute([$id]);
        json_response(['status' => 'success']);
        break;

    case 'addBox':
        if ($role !== 'support' && $role !== 'admin') json_error('Unauthorized');
        $name = $_POST['name'] ?? '';
        $location = $_POST['location'] ?? '';
        $stmt = $pdo->prepare("INSERT INTO distribution_boxes (name, location) VALUES (?, ?)");
        $stmt->execute([$name, $location]);
        json_response(['status' => 'success']);
        break;

    case 'deleteBox':
        if ($role !== 'support' && $role !== 'admin') json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("DELETE FROM distribution_boxes WHERE id = ?")->execute([$id]);
        json_response(['status' => 'success']);
        break;

    case 'addTechnicalIssue':
        if ($role !== 'support' && $role !== 'admin') json_error('Unauthorized');
        $title = $_POST['title'] ?? '';
        $desc = $_POST['description'] ?? '';
        $priority = $_POST['priority'] ?? 'medium';
        $stmt = $pdo->prepare("INSERT INTO technical_issues (title, description, priority, created_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $desc, $priority, $userId]);
        json_response(['status' => 'success']);
        break;

    case 'resolveTechnicalIssue':
        if ($role !== 'support' && $role !== 'admin') json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("UPDATE technical_issues SET status = 'resolved', resolved_at = NOW() WHERE id = ?")->execute([$id]);
        json_response(['status' => 'success']);
        break;

    case 'deleteTechnicalIssue':
        if ($role !== 'support' && $role !== 'admin') json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("DELETE FROM technical_issues WHERE id = ?")->execute([$id]);
        json_response(['status' => 'success']);
        break;

    case 'addReferential':
        if ($role !== 'admin') json_error('Unauthorized');
        $type = $_POST['type'] ?? '';
        $value = $_POST['value'] ?? '';
        $pdo->prepare("INSERT INTO referentials (type, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = value")->execute([$type, $value]);
        json_response(['status' => 'success']);
        break;

    case 'removeReferential':
        if ($role !== 'admin') json_error('Unauthorized');
        $type = $_POST['type'] ?? '';
        $value = $_POST['value'] ?? '';
        $pdo->prepare("DELETE FROM referentials WHERE type = ? AND value = ?")->execute([$type, $value]);
        json_response(['status' => 'success']);
        break;

    case 'saveCommonCustomization':
        if ($role !== 'admin') json_error('Unauthorized');
        $cols = $_POST['columns'] ?? '';
        $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES ('common_columns', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$cols, $cols]);
        json_response(['status' => 'success']);
        break;

    case 'saveWorkflowCustomization':
        if ($role !== 'admin') json_error('Unauthorized');
        $steps = $_POST['steps'] ?? '';
        $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES ('workflow_steps', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$steps, $steps]);
        json_response(['status' => 'success']);
        break;

    case 'saveWidgetCustomization':
        $wr = (int)($_POST['widgetRequests'] ?? 1);
        $wu = (int)($_POST['widgetUsers'] ?? 1);
        $ws = (int)($_POST['widgetStats'] ?? 1);
        $wa = (int)($_POST['widgetActivity'] ?? 1);
        $stmt = $pdo->prepare("INSERT INTO user_customizations (user_id, widget_requests, widget_users, widget_stats, widget_activity) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE widget_requests=?, widget_users=?, widget_stats=?, widget_activity=?");
        $stmt->execute([$userId, $wr, $wu, $ws, $wa, $wr, $wu, $ws, $wa]);
        json_response(['status' => 'success']);
        break;

    case 'approveRegistration':
        if ($role !== 'support' && $role !== 'admin') json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("UPDATE user_registrations SET status = 'approved', reviewed_at = NOW(), reviewed_by = ? WHERE id = ?")->execute([$userId, $id]);
        json_response(['status' => 'success']);
        break;

    case 'rejectRegistration':
        if ($role !== 'support' && $role !== 'admin') json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("UPDATE user_registrations SET status = 'rejected', reviewed_at = NOW(), reviewed_by = ? WHERE id = ?")->execute([$userId, $id]);
        json_response(['status' => 'success']);
        break;

    case 'getData':
        if ($role === 'user') {
            $stmt = $pdo->prepare("SELECT id, email, full_name, role, phone, department, status, joined_at FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $users = to_camel_all($stmt->fetchAll());
            $stmt = $pdo->prepare("SELECT * FROM requests WHERE user_id = ?");
            $stmt->execute([$userId]);
            $requests = to_camel_all($stmt->fetchAll());
        } else {
            $stmt = $pdo->query("SELECT id, email, full_name, role, phone, department, status, joined_at FROM users");
            $users = to_camel_all($stmt->fetchAll());
            $stmt = $pdo->query("SELECT * FROM requests");
            $requests = to_camel_all($stmt->fetchAll());
        }
        json_response(['status' => 'success', 'users' => $users, 'requests' => $requests]);
        break;

    case 'getContent':
        $tab = $_GET['tab'] ?? '';
        $html = '';
        if ($tab === 'history') {
            $stmt = $pdo->prepare("SELECT * FROM requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
            $stmt->execute([$userId]);
            $items = $stmt->fetchAll();
            $html = '<table class="table-glass"><thead><tr><th>ID</th><th>Title</th><th>Status</th><th>Date</th></tr></thead><tbody>';
            foreach ($items as $r) { $html .= '<tr><td>'.$r['id'].'</td><td>'.htmlspecialchars($r['title']).'</td><td><span class="status-pill status-'.$r['status'].'">'.$r['status'].'</span></td><td>'.$r['created_at'].'</td></tr>'; }
            $html .= '</tbody></table>';
        } elseif ($tab === 'audit') {
            $stmt = $pdo->query("SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 50");
            $items = $stmt->fetchAll();
            $html = '<table class="table-glass"><thead><tr><th>Action</th><th>User</th><th>Details</th><th>Date</th></tr></thead><tbody>';
            foreach ($items as $l) { $html .= '<tr><td>'.htmlspecialchars($l['action']).'</td><td>'.$l['user_id'].'</td><td>'.htmlspecialchars($l['details']).'</td><td>'.$l['created_at'].'</td></tr>'; }
            $html .= '</tbody></table>';
        }
        json_response(['html' => $html]);
        break;

    case 'getTrainingRegistrations':
        if ($role !== 'support' && $role !== 'admin') json_error('Unauthorized');
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT u.full_name, u.email, tr.status FROM training_registrations tr JOIN users u ON tr.user_id = u.id WHERE tr.session_id = ?");
        $stmt->execute([$id]);
        json_response(to_camel_all($stmt->fetchAll()));
        break;

    case 'getAssistGuide':
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM assist_guides WHERE id = ?");
        $stmt->execute([$id]);
        json_response(to_camel($stmt->fetch()));
        break;

    default:
        json_response(['status' => 'success', 'message' => 'API endpoint: ' . $action]);
}
