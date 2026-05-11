<?php
$currentView = 'references';
require_once '../includes/header.php';

// Fetch reference data
$subTasks = $pdo->query("SELECT id, label FROM ref_sub_tasks ORDER BY id")->fetchAll();
?>

<div class="section-header">
  <h2><i class="fas fa-tags me-2"></i><?php echo t('referentials'); ?></h2>
</div>

<div class="row g-3">
  <div class="col-md-6">
    <div class="glass-card" style="padding:20px;">
      <h5 class="mb-3"><?php echo t('subTasks'); ?></h5>
      <div id="subTasksChips" class="mb-3">
        <?php foreach ($subTasks as $task): ?>
          <span class="chip">
            <?= htmlspecialchars($task['label']) ?>
            <i class="fas fa-times chip-remove" onclick="removeRef('subTasks', <?= $task['id'] ?>)"></i>
          </span>
        <?php endforeach; ?>
      </div>
      <div class="d-flex gap-8">
        <input type="text" class="form-control" id="newSubTask" placeholder="<?php echo t('addItem'); ?>..." style="flex:1;">
        <button class="btn btn-neon btn-sm" onclick="addRef('subTasks')"><?php echo t('addItem'); ?></button>
      </div>
    </div>
  </div>
</div>

<script>
window.addRef = function(type) {
    var input = document.getElementById('newSubTask');
    var val = input.value.trim();
    if (!val) return;
    fetch('/pfeeeee/PhtP/ajax/api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'addReferential', type: type, label: val })
    })
    .then(r => r.json())
    .then(function(data) {
        if (data.success) { input.value = ''; location.reload(); } else { alert(data.message || 'Error'); }
    })
    .catch(function() { alert('Error'); });
};

window.removeRef = function(type, id) {
    if (!confirm('Remove this item?')) return;
    fetch('/pfeeeee/PhtP/ajax/api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'removeReferential', id: id })
    })
    .then(r => r.json())
    .then(function(data) {
        if (data.success) { location.reload(); } else { alert(data.message || 'Error'); }
    })
    .catch(function() { alert('Error'); });
};
</script>

<?php require_once '../includes/footer.php'; ?>

