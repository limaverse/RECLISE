<?php
$currentView = 'references';
require_once '../includes/header.php';

// Fetch reference data
$subTasks = $pdo->query("SELECT * FROM ref_sub_tasks ORDER BY id")->fetchAll();
$motives = $pdo->query("SELECT * FROM ref_closure_motives ORDER BY id")->fetchAll();
?>

<div class="section-header">
  <h2><i class="fas fa-book me-2"></i><?php echo t('referentials'); ?></h2>
</div>

<div class="row g-3">
  <!-- Sub Tasks -->
  <div class="col-md-6">
    <div class="glass-card" style="padding:20px;">
      <h5 class="mb-3"><?php echo t('subTasks'); ?></h5>
      <div id="subTasksContainer">
        <?php foreach ($subTasks as $i => $task): ?>
          <div class="chip" id="subTask-<?= $task['id'] ?>">
            <?= htmlspecialchars($task['label']) ?>
            <i class="fas fa-times chip-remove" onclick="RecLise.removeRef('subTasks', <?= $i ?>)"></i>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="mt-3 d-flex gap-8">
        <input type="text" class="form-control" id="newSubTask" placeholder="<?php echo t('addItem'); ?>...">
        <button class="btn btn-neon btn-sm" onclick="RecLise.addRef('subTasks')"><?php echo t('addItem'); ?></button>
      </div>
    </div>
  </div>

  <!-- Closure Motives -->
  <div class="col-md-6">
    <div class="glass-card" style="padding:20px;">
      <h5 class="mb-3"><?php echo t('closureMotives'); ?></h5>
      <div id="closureMotivesContainer">
        <?php foreach ($motives as $i => $motive): ?>
          <div class="chip" id="motive-<?= $motive['id'] ?>">
            <?= htmlspecialchars($motive['label']) ?>
            <i class="fas fa-times chip-remove" onclick="RecLise.removeRef('closureMotives', <?= $i ?>)"></i>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="mt-3 d-flex gap-8">
        <input type="text" class="form-control" id="newClosureMotive" placeholder="<?php echo t('addItem'); ?>...">
        <button class="btn btn-neon btn-sm" onclick="RecLise.addRef('closureMotives')"><?php echo t('addItem'); ?></button>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
