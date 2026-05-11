<?php
$currentView = 'system-settings';
require_once '../includes/header.php';

// Fetch system settings
$settings = $pdo->query("SELECT setting_key, setting_value FROM system_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<div class="section-header">
  <h2><i class="fas fa-gear me-2"></i><?php echo t('systemSettings'); ?></h2>
</div>

<div class="row g-3">
  <!-- Email Notifications -->
  <div class="col-md-6">
    <div class="glass-card" style="padding:20px;">
      <h5 class="mb-4"><i class="fas fa-bell me-2" style="color:var(--neon-accent)"></i><?php echo t('emailNotifications'); ?></h5>
      <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" id="settEmailNotif" checked>
        <label class="form-check-label" for="settEmailNotif"><?php echo t('emailNotifications'); ?></label>
      </div>
      <p class="text-secondary italic" style="font-size:0.82rem;"><?php echo t('settingsNote'); ?></p>
    </div>
  </div>

  <!-- Session Timeout -->
  <div class="col-md-6">
    <div class="glass-card" style="padding:20px;">
      <h5 class="mb-4"><i class="fas fa-clock me-2" style="color:var(--neon-accent)"></i><?php echo t('sessionTimeout'); ?></h5>
      <div class="mb-3">
        <label class="form-label"><?php echo t('sessionTimeout'); ?> (<?php echo t('minutes'); ?>)</label>
        <input type="number" class="form-control" id="settTimeout" value="30" min="5" max="120">
      </div>
      <p class="text-secondary italic" style="font-size:0.82rem;"><?php echo t('settingsNote'); ?></p>
    </div>
  </div>

  <div class="col-12">
    <button class="btn btn-neon" onclick="saveSystemSettings()">
      <i class="fas fa-save me-2"></i><?php echo t('save'); ?>
    </button>
  </div>
</div>

<script>
window.saveSystemSettings = function() {
    fetch('/pfeeeee/PhtP/ajax/api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'saveCommonCustomization',
            email_notif: document.getElementById('settEmailNotif').checked ? 1 : 0,
            timeout: document.getElementById('settTimeout').value
        })
    })
    .then(r => r.json())
    .then(function(data) {
        if (data.success) { alert('System settings saved!'); } else { alert(data.message || 'Error'); }
    })
    .catch(function() { alert('Error'); });
};
</script>

<?php require_once '../includes/footer.php'; ?>

