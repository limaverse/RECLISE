<?php
$currentView = 'customize-dashboards';
require_once '../includes/header.php';
?>

<div class="section-header">
  <h2><i class="fas fa-palette me-2"></i><?php echo t('customizeUserDashboards'); ?></h2>
</div>

<div class="glass-card" style="padding:20px;">
  <h5 class="mb-4"><i class="fas fa-th-large me-2" style="color:var(--neon-accent)"></i><?php echo t('userWidgets'); ?></h5>
  
  <div class="form-check form-switch mb-3">
    <input class="form-check-input" type="checkbox" id="uwWelcome" checked>
    <label class="form-check-label" for="uwWelcome">Welcome Message Widget</label>
  </div>
  <div class="form-check form-switch mb-3">
    <input class="form-check-input" type="checkbox" id="uwQuickActions" checked>
    <label class="form-check-label" for="uwQuickActions">Quick Actions Widget</label>
  </div>
  <div class="form-check form-switch mb-3">
    <input class="form-check-input" type="checkbox" id="uwStats" checked>
    <label class="form-check-label" for="uwStats">Status Summary Widget</label>
  </div>
  <div class="form-check form-switch mb-4">
    <input class="form-check-input" type="checkbox" id="uwHistory" checked>
    <label class="form-check-label" for="uwHistory">Recent Activity Widget</label>
  </div>
  
  <button class="btn btn-neon btn-sm" onclick="RecLise.showToast('<?php echo t('settingsSaved'); ?>', 'success')">
    <i class="fas fa-save me-1"></i><?php echo t('saveDashboardCustomization'); ?>
  </button>
</div>

<?php require_once '../includes/footer.php'; ?>
