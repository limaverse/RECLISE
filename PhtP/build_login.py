import sys

with open(r'C:\Users\wassi\OneDrive\Desktop\pfeeeee\login\Light\FR-white\login.html', 'r', encoding='utf-8') as f:
    content = f.read()

php_header = """<?php
session_start();
require_once 'config/db.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') header('Location: admin/dashboard.php');
    elseif ($_SESSION['role'] === 'support') header('Location: support/dashboard.php');
    else header('Location: user/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && hash('sha256', $password) === $user['password_hash']) {
            if ($user['status'] !== 'active') {
                $error = 'Votre compte est inactif.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                
                $stmt = $pdo->prepare("INSERT INTO audit_logs (action, user_id, details) VALUES ('LOGIN', :user_id, 'User logged in')");
                $stmt->execute(['user_id' => $user['id']]);

                if ($user['role'] === 'admin') header('Location: admin/dashboard.php');
                elseif ($user['role'] === 'support') header('Location: support/dashboard.php');
                else header('Location: user/dashboard.php');
                exit;
            }
        } else {
            $error = 'Identifiants incorrects.';
        }
    }
}
?>
"""

# Update form action and inputs
content = content.replace('<form id="loginForm" autocomplete="off">', '<form id="loginForm" method="POST" action="login.php" autocomplete="off">')
content = content.replace('id="inputUsername"', 'name="email" id="inputUsername"')
content = content.replace('id="inputPassword"', 'name="password" id="inputPassword"')

# Remove the JS event listener
content = content.replace("document.getElementById('loginForm').addEventListener('submit', handleLogin);", '')

# Add PHP error message displaying block right after toast container
error_block = """  <?php if ($error): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      showToast('<?php echo addslashes($error); ?>', 'error');
    });
  </script>
  <?php endif; ?>
"""
content = content.replace('<div id="loginPage">', error_block + '<div id="loginPage">')

with open(r'c:\Users\wassi\OneDrive\Desktop\pfeeeee\++PFE-PHP++\login.php', 'w', encoding='utf-8') as f:
    f.write(php_header + content)
