<?php
$currentView = 'assist';
require_once '../includes/header.php';

// Fetch assist guides
$guides = to_camel_all($pdo->query("SELECT * FROM assist_guides ORDER BY created_at DESC")->fetchAll());
?>

<div class="section-header">
  <h2><i class="fas fa-life-ring me-2"></i><?php echo t('assist'); ?></h2>
</div>

<?php if (empty($guides)): ?>
  <p class="text-secondary text-center padding:40px;"><?php echo t('noResults'); ?></p>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($guides as $guide): ?>
      <div class="col-md-6 col-lg-4">
        <div class="item-card">
          <h5><?= htmlspecialchars($guide['title']) ?></h5>
          <p class="text-secondary margin-bottom-12"><?= htmlspecialchars(substr($guide['content'], 0, 100)) ?>...</p>
          <span class="chip"><?php echo t($guide['category']); ?></span>
          <button class="btn btn-outline-neon btn-sm mt-2 w-100" onclick="RecLise.viewAssistGuide(<?= $guide['id'] ?>)">
            <i class="fas fa-book-open me-1"></i><?php echo t('view'); ?>
          </button>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
