<?php
$currentView = 'customization';
require_once '../includes/header.php';

// Fetch system settings
$settings = $pdo->query("SELECT setting_key, setting_value FROM system_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$subTasks = $pdo->query("SELECT label FROM ref_sub_tasks ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);

// Default widget settings
$widgetSettings = [
    'requests' => true,
    'users' => true,
    'stats' => true,
    'activity' => true
];
$workflow = '';
if (isset($settings['widget_customization'])) {
    $decoded = json_decode($settings['widget_customization'], true);
    if (is_array($decoded)) $widgetSettings = array_merge($widgetSettings, $decoded);
}
if (isset($settings['workflow'])) {
    $workflow = $settings['workflow'];
}
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
        <input class="form-check-input" type="checkbox" id="widgetRequests" <?= ($widgetSettings['requests'] ?? true) ? 'checked' : '' ?>>
        <label class="form-check-label" for="widgetRequests"><?php echo t('widgetRequests'); ?></label>
      </div>
      <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" id="widgetUsers" <?= ($widgetSettings['users'] ?? true) ? 'checked' : '' ?>>
        <label class="form-check-label" for="widgetUsers"><?php echo t('widgetUsers'); ?></label>
      </div>
      <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" id="widgetStats" <?= ($widgetSettings['stats'] ?? true) ? 'checked' : '' ?>>
        <label class="form-check-label" for="widgetStats"><?php echo t('widgetStats'); ?></label>
      </div>
      <div class="form-check form-switch mb-4">
        <input class="form-check-input" type="checkbox" id="widgetActivity" <?= ($widgetSettings['activity'] ?? true) ? 'checked' : '' ?>>
        <label class="form-check-label" for="widgetActivity"><?php echo t('widgetActivity'); ?></label>
      </div>
      <button class="btn btn-neon btn-sm" onclick="saveWidgetCustomization()">
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
            <option value="<?= htmlspecialchars($st) ?>" <?= $workflow === $st ? 'selected' : '' ?>><?= htmlspecialchars($st) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn btn-neon btn-sm mt-3" onclick="saveWorkflowCustomization()">
        <i class="fas fa-save me-1"></i><?php echo t('save'); ?>
      </button>
    </div>
  </div>
</div>

<script>
window.saveWidgetCustomization = function() {
    var settings = {
        action: 'saveWidgetCustomization',
        requests: document.getElementById('widgetRequests').checked ? 1 : 0,
        users: document.getElementById('widgetUsers').checked ? 1 : 0,
        stats: document.getElementById('widgetStats').checked ? 1 : 0,
        activity: document.getElementById('widgetActivity').checked ? 1 : 0
    };
    fetch('/pfeeeee/PhtP/ajax/api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(settings)
    })
    .then(r => r.json())
    .then(function(data) {
        if (data.success) { alert('Widget settings saved!'); } else { alert(data.message || 'Error'); }
    })
    .catch(function() { alert('Error'); });
};

window.saveWorkflowCustomization = function() {
    var workflow = document.getElementById('workflowSelect').value;
    fetch('/pfeeeee/PhtP/ajax/api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'saveWorkflowCustomization', workflow: workflow })
    })
    .then(r => r.json())
    .then(function(data) {
        if (data.success) { alert('Workflow saved!'); } else { alert(data.message || 'Error'); }
    })
    .catch(function() { alert('Error'); });
};
</script>

<?php require_once '../includes/footer.php'; ?>

