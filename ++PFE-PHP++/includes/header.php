<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/i18n.php';
require_once __DIR__ . '/../config/db.php';

if (!is_logged_in()) {
    header('Location: ../login.php');
    exit;
}

$currentView = $currentView ?? 'dashboard';
$userRole = $_SESSION['role'];
$lang = $_SESSION['lang'] ?? 'fr';

if (isset($_POST['set_language'])) {
    $lang = in_array($_POST['set_language'], ['fr', 'en', 'ar']) ? $_POST['set_language'] : 'fr';
    $_SESSION['lang'] = $lang;
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$theme = $_SESSION['theme'] ?? 'dark';
$dir = ($lang === 'ar') ? 'rtl' : 'ltr';

$roleTitles = ['admin' => 'Admin Space', 'support' => 'Support Space', 'user' => 'User Space'];
$pageTitle = 'RecLise — ' . ($roleTitles[$userRole] ?? 'RecLise');
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" data-bs-theme="<?= $theme ?>" dir="<?= $dir ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<base href="/pfeeeee/++PFE-PHP++/">
<title>RecLise — <?php echo $pageTitle ?? 'Dashboard'; ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="assets/css/styles.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/script.js"></script>
</head>
<body>
<canvas id="particleCanvas"></canvas>
<div class="toast-container" id="toastContainer"></div>
<div id="appShell">
<button class="btn-hamburger" id="btnHamburger" onclick="RecLise.toggleSidebar()"><i class="fas fa-bars"></i></button>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="RecLise.toggleSidebar()"></div>
<?php require_once __DIR__ . '/sidebar.php'; ?>
<?php require_once __DIR__ . '/topbar.php'; ?>
<main class="main-content" id="mainContent">
