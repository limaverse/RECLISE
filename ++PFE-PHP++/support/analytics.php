<?php
$currentView = 'analytics';
require_once '../includes/header.php';

// Fetch analytics data
$byStatus = $pdo->query("SELECT status, COUNT(*) as cnt FROM requests GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$byPriority = $pdo->query("SELECT priority, COUNT(*) as cnt FROM requests GROUP BY priority")->fetchAll();
$byCategory = $pdo->query("SELECT category, COUNT(*) as cnt FROM requests GROUP BY category")->fetchAll();
$totalRequests = $pdo->query("SELECT COUNT(*) FROM requests")->fetchColumn();
$resolved = $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'resolved'")->fetchColumn();
$resolutionRate = $totalRequests > 0 ? round(($resolved / $totalRequests) * 100, 1) : 0;
?>

<div class="section-header">
  <h2><i class="fas fa-chart-line me-2"></i><?php echo t('analytics'); ?></h2>
</div>

<!-- Resolution Rate -->
<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="stat-card">
      <div class="stat-icon"><i class="fas fa-chart-pie"></i></div>
      <div class="stat-value"><?= $resolutionRate ?>%</div>
      <div class="stat-label"><?php echo t('resolutionRate'); ?></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card">
      <div class="stat-icon"><i class="fas fa-bullseye"></i></div>
      <div class="stat-value"><?= $resolved ?></div>
      <div class="stat-label"><?php echo t('resolved'); ?></div>
    </div>
  </div>
</div>

<!-- By Status -->
<div class="glass-card mb-3" style="padding:20px;">
  <h5 class="mb-3"><?php echo t('requestsByStatus'); ?></h5>
  <div class="bar-chart">
    <?php foreach ($byStatus as $status => $cnt): ?>
      <div class="bar-col">
        <div class="bar-value"><?= $cnt ?></div>
        <div class="bar" style="height: <?= $cnt * 20 ?>px;"></div>
        <div class="bar-label"><?php echo t('status' . ucfirst($status)); ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- By Priority -->
<div class="glass-card mb-3" style="padding:20px;">
  <h5 class="mb-3"><?php echo t('byPriority'); ?></h5>
  <div class="bar-chart">
    <?php foreach ($byPriority as $row): ?>
      <div class="bar-col">
        <div class="bar-value"><?= $row['cnt'] ?></div>
        <div class="bar" style="height: <?= $row['cnt'] * 20 ?>px; background: var(--<?= $row['priority'] === 'high' ? 'danger' : ($row['priority'] === 'medium' ? 'warning' : 'success') ?>);"></div>
        <div class="bar-label"><?php echo t($row['priority']); ?></div>
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
