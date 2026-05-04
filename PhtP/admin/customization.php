<?php
$currentView = 'customization';
require_once '../includes/header.php';

// Fetch system settings
$settings = $pdo->query("SELECT setting_key, setting_value FROM system_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$subTasks = $pdo->query("SELECT value FROM referentials WHERE type='subTasks'")->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="section-header">
  <h2><i class="fas fa-sliders me-2"></i><?php echo t('commonCustomization'); ?></h2>
</div>

<div class="row g-3">
  <!-- Dashboard Widgets -->
  <div class="col-md-6">
    <div class="glass-card" style="padding:20px;">
      <h5 class="mb-4"><i class="fas fa-th-large me-2" style="color:var(--neon-accent)"></i><?php echo t('dashboardWidgets'); ?></h5>
      <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" id="widgetRequests" checked>
        <label class="form-check-label" for="widgetRequests"><?php echo t('widgetRequests'); ?></label>
      </div>
      <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" id="widgetUsers" checked>
        <label class="form-check-label" for="widgetUsers"><?php echo t('widgetUsers'); ?></label>
      </div>
      <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" id="widgetStats" checked>
        <label class="form-check-label" for="widgetStats"><?php echo t('widgetStats'); ?></label>
      </div>
      <div class="form-check form-switch mb-4">
        <input class="form-check-input" type="checkbox" id="widgetActivity" checked>
        <label class="form-check-label" for="widgetActivity"><?php echo t('widgetActivity'); ?></label>
      </div>
      <button class="btn btn-neon btn-sm" onclick="RecLise.saveDashboardCustomization()">
        <i class="fas fa-save me-1"></i><?php echo t('save'); ?>
      </button>
    </div>
  </div>

  <!-- Workflow -->
  <div class="col-md-6">
    <div class="glass-card" style="padding:20px;">
      <h5 class="mb-4"><i class="fas fa-route me-2" style="color:var(--neon-accent)"></i><?php echo t('workflow'); ?></h5>
      <div class="mb-3">
        <label class="form-label"><?php echo t('customizeWorkflow'); ?></label>
        <select class="form-select" id="workflowSelect">
          <option value=""><?php echo t('all'); ?></option>
          <?php foreach ($subTasks as $st): ?>
            <option value="<?= htmlspecialchars($st) ?>"><?= htmlspecialchars($st) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn btn-neon btn-sm mt-3" onclick="RecLise.saveWorkflowCustomization()">
        <i class="fas fa-save me-1"></i><?php echo t('save'); ?>
      </button>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
