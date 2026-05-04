<?php
$currentView = 'distribution-boxes';
require_once '../includes/header.php';

// Fetch distribution boxes
$boxes = to_camel_all($pdo->query("SELECT * FROM distribution_boxes ORDER BY created_at DESC")->fetchAll());

foreach ($boxes as &$box) {
    $stmt = $pdo->prepare("SELECT * FROM distribution_box_members WHERE box_id = ?");
    $stmt->execute([$box['id']]);
    $box['members'] = to_camel_all($stmt->fetchAll());
}
?>

<div class="section-header">
  <h2><i class="fas fa-box me-2"></i><?php echo t('distributionBoxes'); ?></h2>
  <button class="btn btn-neon btn-sm" onclick="RecLise.showAddBoxModal()">
    <i class="fas fa-plus me-1"></i><?php echo t('addItem'); ?>
  </button>
</div>

<div class="row g-3">
  <?php if (empty($boxes)): ?>
    <p class="text-secondary text-center padding:40px;"><?php echo t('noResults'); ?></p>
  <?php else: ?>
    <?php foreach ($boxes as $box): ?>
      <div class="col-md-6 col-lg-4">
        <div class="item-card">
          <h5><?= htmlspecialchars($box['name']) ?></h5>
          <p class="text-secondary margin-bottom-12">
            <i class="fas fa-users me-1"></i><?= count($box['members']) ?> <?php echo t('members'); ?>
          </p>
          <div class="mb-3">
            <?php foreach ($box['members'] as $member): ?>
              <span class="chip"><?= htmlspecialchars($member['member_name']) ?></span>
            <?php endforeach; ?>
          </div>
          <button class="btn btn-sm btn-danger" onclick="RecLise.deleteBox(<?= $box['id'] ?>)">
            <i class="fas fa-trash"></i>
          </button>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
