<?php
ob_start();
error_reporting(0);
register_shutdown_function(function() {
    if ($e = error_get_last()) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success'=>false,'message'=>'Server error']);
    }
});
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    ob_clean();
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$json = json_decode(file_get_contents('php://input'), true);
if (is_array($json)) {
    $_POST = array_merge($_POST, $json);
}
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$role = $_SESSION['role'] ?? 'user';
$userId = $_SESSION['user_id'] ?? 0;

function json_response($data) {
    ob_clean();
    if (is_array($data)) {
        if (isset($data['status']) && $data['status'] === 'success') {
            $data['success'] = true;
        } elseif (!isset($data['status']) && !isset($data['error'])) {
            $data['success'] = true;
        }
    }
    echo json_encode($data);
    exit;
}

function json_error($msg) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => $msg, 'message' => $msg]);
    exit;
}

function addNotification($pdo, $user_id, $title, $body) {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, body) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $title, $body]);
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
        if (!in_array($role, ['user', 'support', 'admin'])) json_error('Unauthorized');
        $type = $_POST['type'] ?? 'request';
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = $_POST['category'] ?? 'category';
        $priority = $_POST['priority'] ?? 'medium';
        if (!$title || !$description) json_error('Title and description required');

        $assignStmt = $pdo->query("SELECT assigned_to, COUNT(*) as cnt FROM requests WHERE status IN ('new', 'in_progress') AND assigned_to IS NOT NULL GROUP BY assigned_to ORDER BY cnt ASC LIMIT 1");
        $assignedTo = 2; // default fallback
        if ($row = $assignStmt->fetch()) { $assignedTo = $row['assigned_to']; }
        
        $stmt = $pdo->prepare("INSERT INTO requests (user_id, type, title, description, category, priority, status, assigned_to) VALUES (?, ?, ?, ?, ?, ?, 'new', ?)");
        $stmt->execute([$userId, $type, $title, $description, $category, $priority, $assignedTo]);
        $reqId = $pdo->lastInsertId();

        // Handle file uploads
        if (!empty($_FILES['attachments']['name'][0])) {
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            $fileCount = count($_FILES['attachments']['name']);
            for ($i = 0; $i < $fileCount; $i++) {
                $tmpName = $_FILES['attachments']['tmp_name'][$i];
                $name = basename($_FILES['attachments']['name'][$i]);
                if ($tmpName && $name) {
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt'];
                    if (!in_array($ext, $allowedExts)) {
                        continue; // Skip dangerous files
                    }

                    $uniqueName = time() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', $name);
                    $destPath = $uploadDir . $uniqueName;
                    if (move_uploaded_file($tmpName, $destPath)) {
                        $pdo->prepare("INSERT INTO request_attachments (request_id, file_name, file_path) VALUES (?, ?, ?)")
                            ->execute([$reqId, $name, 'uploads/' . $uniqueName]);
                    }
                }
            }
        }

        log_audit($pdo, 'CREATE_REQUEST', "Request #$reqId created by user #$userId", $userId);

        // Notify all support agents
        $sups = $pdo->query("SELECT id FROM users WHERE role='support'")->fetchAll(PDO::FETCH_COLUMN);
        foreach($sups as $sid) {
            addNotification($pdo, $sid, "New Request #$reqId", "A new request has been submitted.");
        }

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
            $pdo->prepare("INSERT INTO messages (request_id, sender_id, body) VALUES (?, ?, ?)")->execute([$id, $userId, $reply]);
        }
        log_audit($pdo, 'UPDATE_REQUEST', "Request #$id status changed to $status", $userId);

        // Notify user
        $uid = $pdo->prepare("SELECT user_id FROM requests WHERE id=?");
        $uid->execute([$id]);
        $uid = $uid->fetchColumn();
        if ($uid) {
            addNotification($pdo, $uid, "Request #$id Updated", "Status changed to $status.");
        }

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
        
        log_audit($pdo, 'REPLY_REQUEST', "Replied to request #$id", $userId);

        // Notify the appropriate party
        $uid = $pdo->prepare("SELECT user_id FROM requests WHERE id=?");
        $uid->execute([$id]);
        $uid = $uid->fetchColumn();
        if ($uid) {
            if ($role === 'user') {
                $sups = $pdo->query("SELECT id FROM users WHERE role='support'")->fetchAll(PDO::FETCH_COLUMN);
                foreach($sups as $sid) {
                    addNotification($pdo, $sid, "New Reply on #$id", "User replied to request #$id.");
                }
            } else {
                addNotification($pdo, $uid, "New Reply on #$id", "Support replied to your request.");
            }
        }

        json_response(['status' => 'success']);
        break;

    case 'escalateRequest':
        if ($role !== 'support') json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) json_error('Request ID required');

        $pdo->prepare("UPDATE requests SET escalated_to_admin = 1, status = 'escalated', updated_at = NOW() WHERE id = ?")->execute([$id]);
        $pdo->prepare("INSERT INTO messages (request_id, sender_type, sender_id, body) VALUES (?, 'system', NULL, 'Request escalated to admin')")->execute([$id]);
        log_audit($pdo, 'ESCALATE_REQUEST', "Request #$id escalated", $userId);

        // Notify admins
        $ads = $pdo->query("SELECT id FROM users WHERE role='admin'")->fetchAll(PDO::FETCH_COLUMN);
        foreach($ads as $aid) {
            addNotification($pdo, $aid, "Request #$id Escalated", "A request has been escalated to you.");
        }

        json_response(['status' => 'success']);
        break;

    case 'addUser':
        if ($role !== 'admin') json_error('Unauthorized');
        $name = trim($_POST['fullName'] ?? $_POST['full_name'] ?? '');
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
        $name = trim($_POST['fullName'] ?? $_POST['full_name'] ?? '');
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
        json_response(['status' => 'success', 'user' => $user]);
        break;
        
    case 'getRequest':
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) json_error('Request ID required');
        $stmt = $pdo->prepare("SELECT r.*, u.full_name as requester_name, u.email as requester_email FROM requests r LEFT JOIN users u ON r.user_id = u.id WHERE r.id = ?");
        $stmt->execute([$id]);
        $req = to_camel($stmt->fetch());
        if (!$req) json_error('Request not found');

        // Fetch messages
        $msgStmt = $pdo->prepare("SELECT sender_type, body, created_at FROM messages WHERE request_id = ? ORDER BY created_at ASC");
        $msgStmt->execute([$id]);
        $messages = to_camel_all($msgStmt->fetchAll());
        $req['messages'] = $messages;

        $attStmt = $pdo->prepare("SELECT file_name, file_path FROM request_attachments WHERE request_id = ?");
        $attStmt->execute([$id]);
        $req['attachments'] = to_camel_all($attStmt->fetchAll());

        json_response(['status' => 'success', 'request' => $req]);
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
        $id = (int)($_POST['session_id'] ?? $_POST['id'] ?? 0);
        $pdo->prepare("INSERT INTO user_training_registrations (session_id, user_id, status) VALUES (?, ?, 'registered') ON DUPLICATE KEY UPDATE status='registered'")->execute([$id, $userId]);
        json_response(['status' => 'success']);
        break;

    case 'addTrainingSession':
        if ($role !== 'support' && $role !== 'admin') json_error('Unauthorized');
        $title = trim($_POST['title'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $date = $_POST['date'] ?? $_POST['session_date'] ?? '';
        $duration = (int)($_POST['duration'] ?? 1);
        if (!$title || !$date) json_error('Title and date required');
        $stmt = $pdo->prepare("INSERT INTO training_sessions (title, description, session_date, duration, created_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $desc, $date, $duration, $userId]);
        log_audit($pdo, 'ADD_TRAINING', "Training session added: $title", $userId);
        json_response(['status' => 'success', 'id' => $pdo->lastInsertId()]);
        break;

    case 'getTrainingSession':
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM training_sessions WHERE id = ?");
        $stmt->execute([$id]);
        json_response(['status' => 'success', 'session' => to_camel($stmt->fetch())]);
        break;

    case 'updateTrainingSession':
        if ($role !== 'support' && $role !== 'admin') json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        $title = $_POST['title'] ?? '';
        $desc = $_POST['description'] ?? '';
        $date = $_POST['date'] ?? $_POST['session_date'] ?? '';
        $duration = (int)($_POST['duration'] ?? 1);
        $pdo->prepare("UPDATE training_sessions SET title=?, description=?, session_date=?, duration=? WHERE id=?")->execute([$title, $desc, $date, $duration, $id]);
        json_response(['status' => 'success']);
        break;

    case 'deleteTrainingSession':
        if ($role !== 'support' && $role !== 'admin') json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) json_error('Session ID required');
        $pdo->prepare("DELETE FROM user_training_registrations WHERE session_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM training_sessions WHERE id = ?")->execute([$id]);
        log_audit($pdo, 'DELETE_TRAINING', "Training session deleted: $id", $userId);
        json_response(['status' => 'success']);
        break;

    case 'addGuide':
        if ($role !== 'support' && $role !== 'admin') json_error('Unauthorized');
        $title = $_POST['title'] ?? '';
        $category = $_POST['category'] ?? '';
        $content = $_POST['content'] ?? '';
        $stmt = $pdo->prepare("INSERT INTO assist_guides (title, category, content) VALUES (?, ?, ?)");
        $stmt->execute([$title, $category, $content]);
        json_response(['status' => 'success']);
        break;

    case 'getGuide':
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM assist_guides WHERE id = ?");
        $stmt->execute([$id]);
        json_response(['status' => 'success', 'guide' => to_camel($stmt->fetch())]);
        break;

    case 'editGuide':
        if ($role !== 'support' && $role !== 'admin') json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        $title = $_POST['title'] ?? '';
        $category = $_POST['category'] ?? '';
        $content = $_POST['content'] ?? '';
        $pdo->prepare("UPDATE assist_guides SET title = ?, category = ?, content = ? WHERE id = ?")->execute([$title, $category, $content, $id]);
        json_response(['status' => 'success']);
        break;

    case 'deleteGuide':
        if ($role !== 'support' && $role !== 'admin') json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("DELETE FROM assist_guides WHERE id = ?")->execute([$id]);
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
        $membersStr = trim($_POST['members'] ?? '');
        $stmt = $pdo->prepare("INSERT INTO distribution_boxes (name, location) VALUES (?, '')");
        $stmt->execute([$name]);
        $boxId = $pdo->lastInsertId();
        if ($membersStr) {
            $members = explode(',', $membersStr);
            $stmt = $pdo->prepare("INSERT INTO distribution_box_members (box_id, member_name) VALUES (?, ?)");
            foreach ($members as $m) {
                $m = trim($m);
                if ($m) $stmt->execute([$boxId, $m]);
            }
        }
        json_response(['status' => 'success']);
        break;

    case 'deleteBox':
        if ($role !== 'admin') json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("DELETE FROM distribution_boxes WHERE id = ?")->execute([$id]);
        json_response(['success' => true]);
        break;

    case 'getNotifications':
        $stmt = $pdo->prepare("SELECT id, title, body, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
        $stmt->execute([$userId]);
        $notifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $unread = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $unread->execute([$userId]);
        $unreadCount = $unread->fetchColumn();
        json_response(['success' => true, 'notifications' => to_camel_all($notifs), 'unreadCount' => $unreadCount]);
        break;

    case 'markNotificationsRead':
        $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0")->execute([$userId]);
        json_response(['success' => true]);
        break;

    case 'requestAccess':
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $dept = trim($_POST['department'] ?? '');
        if (!$fullName || !$email) json_error('Name and Email required');
        
        $pdo->prepare("INSERT INTO user_registrations (full_name, email, department, status) VALUES (?, ?, ?, 'pending')")->execute([$fullName, $email, $dept]);
        
        // Notify Admins
        $ads = $pdo->query("SELECT id FROM users WHERE role='admin'")->fetchAll(PDO::FETCH_COLUMN);
        foreach($ads as $aid) {
            addNotification($pdo, $aid, "New Registration", "User $fullName requested access.");
        }
        json_response(['success' => true]);
        break;

    case 'approveRegistration':
        if ($role !== 'admin') json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        $reg = $pdo->prepare("SELECT * FROM user_registrations WHERE id = ?");
        $reg->execute([$id]);
        $row = $reg->fetch();
        if (!$row) json_error('Registration not found');

        $tempPass = bin2hex(random_bytes(4));
        $hash = hash('sha256', $tempPass); // Keeping project's current logic for now
        $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, 'user')")->execute([$row['full_name'], $row['email'], $hash]);
        $pdo->prepare("UPDATE user_registrations SET status = 'approved', reviewed_at = NOW(), reviewed_by = ? WHERE id = ?")->execute([$userId, $id]);
        json_response(['success' => true, 'message' => "Approved. Temp password for user: $tempPass"]);
        break;

    case 'rejectRegistration':
        if ($role !== 'admin') json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("UPDATE user_registrations SET status = 'rejected', reviewed_at = NOW(), reviewed_by = ? WHERE id = ?")->execute([$userId, $id]);
        json_response(['success' => true]);
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
        $label = $_POST['label'] ?? $_POST['value'] ?? '';
        if (!$type || !$label) json_error('Type and label required');
        
        if ($type === 'sub_task' || $type === 'subTasks') {
            $pdo->prepare("INSERT INTO ref_sub_tasks (label) VALUES (?)")->execute([$label]);
        } elseif ($type === 'closure_motive') {
            $pdo->prepare("INSERT INTO ref_closure_motives (label) VALUES (?)")->execute([$label]);
        } else {
            $pdo->prepare("INSERT INTO referentials (type, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = value")->execute([$type, $label]);
        }
        json_response(['status' => 'success']);
        break;

    case 'removeReferential':
        if ($role !== 'admin') json_error('Unauthorized');
        $type = $_POST['type'] ?? '';
        $id = (int)($_POST['id'] ?? 0);
        $value = $_POST['value'] ?? '';
        
        if (($type === 'sub_task' || $type === 'subTasks' || !$type) && $id) {
            $pdo->prepare("DELETE FROM ref_sub_tasks WHERE id = ?")->execute([$id]);
        } elseif ($type === 'closure_motive' && $id) {
            $pdo->prepare("DELETE FROM ref_closure_motives WHERE id = ?")->execute([$id]);
        } elseif ($type && $value) {
            $pdo->prepare("DELETE FROM referentials WHERE type = ? AND value = ?")->execute([$type, $value]);
        }
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
        $tab = $_GET['tab'] ?? $_GET['view'] ?? '';
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
        $id = (int)($_GET['id'] ?? $_GET['session_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT u.full_name, u.email, tr.status FROM user_training_registrations tr JOIN users u ON tr.user_id = u.id WHERE tr.session_id = ?");
        $stmt->execute([$id]);
        json_response(['status' => 'success', 'registrations' => to_camel_all($stmt->fetchAll())]);
        break;

    case 'getAssistGuide':
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM assist_guides WHERE id = ?");
        $stmt->execute([$id]);
        json_response(['status' => 'success', 'guide' => to_camel($stmt->fetch())]);
        break;

    case 'processRequest':
        if (!in_array($role, ['support', 'admin'])) json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("UPDATE requests SET status = 'in_progress', updated_at = NOW() WHERE id = ?")->execute([$id]);
        json_response(['success' => true]);
        break;

    case 'unregisterTraining':
        $sessionId = (int)($_POST['session_id'] ?? 0);
        $pdo->prepare("DELETE FROM user_training_registrations WHERE session_id = ? AND user_id = ?")->execute([$sessionId, $userId]);
        json_response(['success' => true]);
        break;

    case 'editRequest':
        if ($role !== 'user') json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $priority = trim($_POST['priority'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $pdo->prepare("UPDATE requests SET title = ?, category = ?, priority = ?, description = ?, updated_at = NOW() WHERE id = ? AND user_id = ?")->execute([$title, $category, $priority, $desc, $id, $userId]);
        json_response(['success' => true]);
        break;

    case 'deleteRequest':
        if ($role !== 'user') json_error('Unauthorized');
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("DELETE FROM requests WHERE id = ? AND user_id = ?")->execute([$id, $userId]);
        json_response(['success' => true]);
        break;

    case 'saveDashboard':
        json_response(['success' => true]);
        break;

    default:
        json_response(['success' => false, 'error' => 'API endpoint not found: ' . $action]);
}






