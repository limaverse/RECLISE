<?php
$currentView = 'customize-dashboards';
require_once '../includes/header.php';

// Load current settings - match existing table structure
$userId = $_SESSION['user_id'] ?? 0;
$settings = [];

try {
    $stmt = $pdo->prepare("SELECT widget_requests, widget_users, widget_stats, widget_activity FROM user_customizations WHERE user_id = ?");
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    if ($row) {
        $settings = [
            'requests' => (int)$row['widget_requests'],
            'users' => (int)$row['widget_users'],
            'stats' => (int)$row['widget_stats'],
            'activity' => (int)$row['widget_activity']
        ];
    } else {
        $settings = ['requests' => 1, 'users' => 1, 'stats' => 1, 'activity' => 1];
    }
} catch (Exception $e) {
    $settings = ['requests' => 1, 'users' => 1, 'stats' => 1, 'activity' => 1];
}
?>

<div class="section-header">
  <h2><i class="fas fa-palette me-2"></i><?php echo t('customizeUserDashboards'); ?></h2>
</div>

<div class="glass-card" style="padding:20px;">
  <h5 class="mb-4"><i class="fas fa-th-large me-2" style="color:var(--neon-accent)"></i><?php echo t('userWidgets'); ?></h5>
  
  <div class="form-check form-switch mb-3">
    <input class="form-check-input" type="checkbox" id="uwRequests" <?= $settings['requests'] ? 'checked' : '' ?>>
    <label class="form-check-label" for="uwRequests"><?php echo t('widgetRequests'); ?></label>
  </div>
  <div class="form-check form-switch mb-3">
    <input class="form-check-input" type="checkbox" id="uwUsers" <?= $settings['users'] ? 'checked' : '' ?>>
    <label class="form-check-label" for="uwUsers"><?php echo t('widgetUsers'); ?></label>
  </div>
  <div class="form-check form-switch mb-3">
    <input class="form-check-input" type="checkbox" id="uwStats" <?= $settings['stats'] ? 'checked' : '' ?>>
    <label class="form-check-label" for="uwStats"><?php echo t('widgetStats'); ?></label>
  </div>
  <div class="form-check form-switch mb-4">
    <input class="form-check-input" type="checkbox" id="uwActivity" <?= $settings['activity'] ? 'checked' : '' ?>>
    <label class="form-check-label" for="uwActivity"><?php echo t('widgetActivity'); ?></label>
  </div>
  
  <button class="btn btn-neon btn-sm" onclick="RecLise.saveDashboard()">
    <i class="fas fa-save me-1"></i><?php echo t('saveDashboardCustomization'); ?>
  </button>
</div>

<?php require_once '../includes/footer.php'; ?>
