<?php
$currentView = 'references';
require_once '../includes/header.php';

// Fetch reference data
$subTasks = $pdo->query("SELECT * FROM referentials WHERE type='subTasks' ORDER BY id")->fetchAll();
?>

<div class="section-header">
  <h2><i class="fas fa-tags me-2"></i><?php echo t('referentials'); ?></h2>
</div>

<div class="row g-3">
  <div class="col-md-6">
    <div class="glass-card" style="padding:20px;">
      <h5 class="mb-3"><?php echo t('subTasks'); ?></h5>
      <div id="subTasksChips" class="mb-3">
        <?php foreach ($subTasks as $i => $task): ?>
          <span class="chip">
            <?= htmlspecialchars($task['label']) ?>
            <i class="fas fa-times chip-remove" onclick="RecLise.removeRef('subTasks', <?= $i ?>)"></i>
          </span>
        <?php endforeach; ?>
      </div>
      <div class="d-flex gap-8">
        <input type="text" class="form-control" id="newSubTask" placeholder="<?php echo t('addItem'); ?>..." style="flex:1;">
        <button class="btn btn-neon btn-sm" onclick="RecLise.addRef('subTasks')"><?php echo t('addItem'); ?></button>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
