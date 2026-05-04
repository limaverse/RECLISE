<?php
$currentView = 'customization';
require_once '../includes/header.php';

// Fetch system settings
$settings = $pdo->query("SELECT setting_key, setting_value FROM system_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<div class="section-header">
  <h2><i class="fas fa-gear me-2"></i><?php echo t('systemSettings'); ?></h2>
</div>

<div class="row g-3">
  <!-- Common Customization -->
  <div class="col-md-6">
    <div class="glass-card" style="padding:20px;">
      <h5 class="mb-3"><?php echo t('commonCustomization'); ?></h5>
      <div class="mb-3">
        <label class="form-label"><?php echo t('emailNotifications'); ?></label>
        <select class="form-select" id="emailNotif">
          <option value="1" <?= ($settings['email_notifications'] ?? '1') ? 'selected' : '' ?>><?php echo t('enabled'); ?></option>
          <option value="0" <?= ($settings['email_notifications'] ?? '1') ? '' : 'selected' ?>><?php echo t('disabled'); ?></option>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label"><?php echo t('sessionTimeout'); ?></label>
        <input type="number" class="form-control" id="sessionTimeout" value="<?= $settings['session_timeout'] ?? 30 ?>">
        <small class="text-secondary"><?php echo t('minutes'); ?></small>
      </div>
      <button class="btn btn-neon w-100" onclick="RecLise.saveCommonCustomization()">
        <i class="fas fa-save me-2"></i><?php echo t('save'); ?>
      </button>
      <p class="text-secondary mt-2" style="font-size:0.82rem;"><?php echo t('settingsNote'); ?></p>
    </div>
  </div>

  <!-- Workflow Settings -->
  <div class="col-md-6">
    <div class="glass-card" style="padding:20px;">
      <h5 class="mb-3"><?php echo t('workflowSettings'); ?></h5>
      <div class="mb-3">
        <label class="form-label"><?php echo t('workflow'); ?></label>
        <select class="form-select" id="workflowType">
          <option value="default" <?= ($settings['workflow_type'] ?? 'default') === 'default' ? 'selected' : '' ?>><?php echo t('defaultWorkflow'); ?></option>
          <option value="auto_assign" <?= ($settings['workflow_type'] ?? '') === 'auto_assign' ? 'selected' : '' ?>><?php echo t('autoAssign'); ?></option>
          <option value="escalate_first" <?= ($settings['workflow_type'] ?? '') === 'escalate_first' ? 'selected' : '' ?>><?php echo t('escalateFirst'); ?></option>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label"><?php echo t('notifySupport'); ?></label>
        <select class="form-select" id="notifySupport">
          <option value="1" <?= ($settings['notify_support'] ?? '1') ? 'selected' : '' ?>><?php echo t('enabled'); ?></option>
          <option value="0" <?= ($settings['notify_support'] ?? '1') ? '' : 'selected' ?>><?php echo t('disabled'); ?></option>
        </select>
      </div>
      <button class="btn btn-neon w-100" onclick="RecLise.saveWorkflowCustomization()">
        <i class="fas fa-save me-2"></i><?php echo t('save'); ?>
      </button>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
