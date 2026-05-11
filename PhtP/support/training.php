<?php
$currentView = 'training';
require_once '../includes/header.php';

// Fetch training sessions with registration count
$sessions = to_camel_all($pdo->query("
    SELECT t.*, 
           (SELECT COUNT(*) FROM user_training_registrations WHERE session_id = t.id) as registered_count 
    FROM training_sessions t 
    ORDER BY t.session_date ASC")->fetchAll());
?>

<div class="section-header">
  <h2><i class="fas fa-graduation-cap me-2"></i><?php echo t('training'); ?></h2>
  <button class="btn btn-neon btn-sm" onclick="RecLise.showAddTrainingModal()">
    <i class="fas fa-plus me-1"></i><?php echo t('addSession'); ?>
  </button>
</div>

<?php if (empty($sessions)): ?>
  <p class="text-secondary text-center p-4"><?php echo t('noResults'); ?></p>
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
              <?= $session['sessionDate'] ? date('Y-m-d H:i', strtotime($session['sessionDate'])) : 'N/A' ?>
            </small>
            <span class="chip">
              <i class="fas fa-users me-1"></i><?= $session['registeredCount'] ?> <?php echo t('registered'); ?>
            </span>
          </div>
          <div class="flex-gap-4">
            <button class="btn btn-outline-neon btn-sm" onclick="RecLise.editTraining(<?= $session['id'] ?>)">
              <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-danger btn-sm" onclick="RecLise.deleteTrainingConfirm(<?= $session['id'] ?>)">
              <i class="fas fa-trash"></i>
            </button>
            <button class="btn btn-outline-neon btn-sm" onclick="RecLise.viewRegistrations(<?= $session['id'] ?>)">
              <i class="fas fa-list me-1"></i><?php echo t('viewRegistrations'); ?>
            </button>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
