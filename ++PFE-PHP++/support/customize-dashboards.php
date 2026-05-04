<?php
$currentView = 'customize-dashboards';
require_once '../includes/header.php';

// Fetch user's dashboard customization
$stmt = $pdo->prepare("SELECT * FROM user_customizations WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$cust = $stmt->fetch();
if (!$cust) {
    $cust = [
        'widget_requests' => 1,
        'widget_users' => 1,
        'widget_stats' => 1,
        'widget_activity' => 1,
        'workflow' => ''
    ];
}
?>

<div class="section-header">
  <h2><i class="fas fa-th me-2"></i><?php echo t('customizeDashboards'); ?></h2>
</div>

<div class="glass-card" style="padding:20px;">
  <h5 class="mb-3"><?php echo t('dashboardWidgets'); ?></h5>
  
  <div class="mb-3">
    <div class="form-check mb-2">
      <input class="form-check-input" type="checkbox" id="widgetRequests" <?= $cust['widget_requests'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="widgetRequests"><?php echo t('requests'); ?></label>
    </div>
    <div class="form-check mb-2">
      <input class="form-check-input" type="checkbox" id="widgetUsers" <?= $cust['widget_users'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="widgetUsers"><?php echo t('users'); ?></label>
    </div>
    <div class="form-check mb-2">
      <input class="form-check-input" type="checkbox" id="widgetStats" <?= $cust['widget_stats'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="widgetStats"><?php echo t('statistics'); ?></label>
    </div>
    <div class="form-check mb-2">
      <input class="form-check-input" type="checkbox" id="widgetActivity" <?= $cust['widget_activity'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="widgetActivity"><?php echo t('recentActivity'); ?></label>
    </div>
  </div>

  <h5 class="mb-3 mt-4"><?php echo t('workflowSettings'); ?></h5>
  <div class="mb-3">
    <label class="form-label"><?php echo t('workflow'); ?></label>
    <select class="form-select" id="workflowType">
      <option value="default" <?= ($cust['workflow'] ?? 'default') === 'default' ? 'selected' : '' ?>><?php echo t('defaultWorkflow'); ?></option>
      <option value="auto_assign" <?= ($cust['workflow'] ?? '') === 'auto_assign' ? 'selected' : '' ?>><?php echo t('autoAssign'); ?></option>
      <option value="escalate_first" <?= ($cust['workflow'] ?? '') === 'escalate_first' ? 'selected' : '' ?>><?php echo t('escalateFirst'); ?></option>
    </select>
  </div>

  <button class="btn btn-neon w-100" onclick="RecLise.saveDashboardCustomization()">
    <i class="fas fa-save me-2"></i><?php echo t('save'); ?>
  </button>
</div>

<?php require_once '../includes/footer.php'; ?>
