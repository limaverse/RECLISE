<?php
$currentView = 'statistics';
require_once '../includes/header.php';

// Fetch statistics
$totalRequests = $pdo->query("SELECT COUNT(*) FROM requests")->fetchColumn();
$byStatus = $pdo->query("SELECT status, COUNT(*) as cnt FROM requests GROUP BY status")->fetchAll();
$byType = $pdo->query("SELECT type, COUNT(*) as cnt FROM requests GROUP BY type")->fetchAll();
$byCategory = $pdo->query("SELECT category, COUNT(*) as cnt FROM requests GROUP BY category")->fetchAll();
?>

<div class="section-header">
  <h2><i class="fas fa-chart-bar me-2"></i><?php echo t('statistics'); ?></h2>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="stat-card">
      <div class="stat-icon"><i class="fas fa-inbox"></i></div>
      <div class="stat-value"><?= $totalRequests ?></div>
      <div class="stat-label"><?php echo t('totalRequests'); ?></div>
    </div>
  </div>
</div>

<!-- By Status -->
<div class="glass-card mb-3" style="padding:20px;">
  <h5 class="mb-3"><?php echo t('requestsByStatus'); ?></h5>
  <div class="bar-chart">
    <?php foreach ($byStatus as $row): ?>
      <div class="bar-col">
        <div class="bar-value"><?= $row['cnt'] ?></div>
        <div class="bar" style="height: <?= $row['cnt'] * 20 ?>px; background: var(--<?= $row['status'] === 'new' ? 'warning' : ($row['status'] === 'resolved' ? 'success' : 'info') ?>);"></div>
        <div class="bar-label"><?php echo t_status($row['status']); ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- By Type -->
<div class="glass-card mb-3" style="padding:20px;">
  <h5 class="mb-3"><?php echo t('requestsByType'); ?></h5>
  <div class="bar-chart">
    <?php foreach ($byType as $row): ?>
      <div class="bar-col">
        <div class="bar-value"><?= $row['cnt'] ?></div>
        <div class="bar" style="height: <?= $row['cnt'] * 20 ?>px;"></div>
        <div class="bar-label"><?php echo t($row['type']); ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- By Category -->
<div class="glass-card" style="padding:20px;">
  <h5 class="mb-3"><?php echo t('requestsByCategory'); ?></h5>
  <div class="bar-chart">
    <?php foreach ($byCategory as $row): ?>
      <div class="bar-col">
        <div class="bar-value"><?= $row['cnt'] ?></div>
        <div class="bar" style="height: <?= $row['cnt'] * 20 ?>px;"></div>
        <div class="bar-label"><?php echo t($row['category']); ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
