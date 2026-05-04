<?php
session_start();
require_once '../includes/functions.php';
require_login();
require_role('admin');

$currentView = 'common-customization';

// Ensure serverData is loaded from DB or use ajax.
// The JS logic will handle rendering via renderContent(currentView)
require_once '../includes/header.php';
?>

<!-- The content is dynamically rendered here by script.js -->

<?php require_once '../includes/footer.php'; ?>
