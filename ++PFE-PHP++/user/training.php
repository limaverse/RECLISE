<?php
$currentView = 'training';
require_once '../includes/header.php';

// Fetch training sessions
$sessions = to_camel_all($pdo->query("SELECT *, 
    (SELECT COUNT(*) FROM user_training_registrations WHERE session_id = training_sessions.id) as registered_count 
    FROM training_sessions 
    ORDER BY session_date ASC")->fetchAll());

// Check user registrations
$userRegs = $pdo->query("SELECT session_id FROM user_training_registrations WHERE user_id = {$_SESSION['user_id']}")->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="section-header">
  <h2><i class="fas fa-graduation-cap me-2"></i><?php echo t('training'); ?></h2>
</div>

<?php if (empty($sessions)): ?>
  <p class="text-secondary text-center padding:40px;"><?php echo t('noResults'); ?></p>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($sessions as $session): ?>
      <div class="col-md-6 col-lg-4">
        <div class="item-card">
          <h5><?= htmlspecialchars($session['title']) ?></h5>
          <p class="text-secondary margin-bottom-12"><?= htmlspecialchars($session['description'] ?? '') ?></p>
          <div class="flex-space-between align-items-center margin-bottom-12">
            <small class="text-secondary">
              <i class="fas fa-calendar me-1"></i>
              <?= date('Y-m-d H:i', strtotime($session['session_date'])) ?>
            </small>
            <span class="chip">
              <i class="fas fa-users me-1"></i><?= $session['registered_count'] ?> <?php echo t('registered'); ?>
            </span>
          </div>
          <?php if (in_array($session['id'], $userRegs)): ?>
            <button class="btn btn-outline-neon w-100" onclick="RecLise.registerForTraining(<?= $session['id'] ?>)">
              <i class="fas fa-check me-1"></i><?php echo t('registered'); ?>
            </button>
          <?php else: ?>
            <button class="btn btn-neon w-100" onclick="RecLise.registerForTraining(<?= $session['id'] ?>)">
              <i class="fas fa-plus me-1"></i><?php echo t('register'); ?>
            </button>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
