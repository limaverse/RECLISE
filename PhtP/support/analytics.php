<?php
$currentView = 'analytics';
require_once '../includes/header.php';

// Mock analytics data or real queries
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM requests")->fetchColumn(),
    'resolved' => $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'resolved'")->fetchColumn(),
    'avgTime' => '4.2h'
];
?>

<div class="section-header">
  <h2><i class="fas fa-chart-line me-2"></i><?php echo t('analytics'); ?></h2>
</div>

<div class="row g-3">
  <div class="col-md-4">
    <div class="glass-card text-center" style="padding:20px;">
      <div class="stat-label"><?php echo t('totalRequests'); ?></div>
      <div class="stat-value"><?= $stats['total'] ?></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="glass-card text-center" style="padding:20px;">
      <div class="stat-label"><?php echo t('statusResolved'); ?></div>
      <div class="stat-value"><?= $stats['resolved'] ?></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="glass-card text-center" style="padding:20px;">
      <div class="stat-label"><?php echo t('resolutionTime'); ?></div>
      <div class="stat-value"><?= $stats['avgTime'] ?></div>
    </div>
  </div>
</div>

<div class="glass-card mt-3" style="padding:20px;">
  <h5 class="mb-4"><?php echo t('performanceTrends'); ?></h5>
  <!-- Chart Placeholder -->
  <div style="height:200px; background:rgba(255,255,255,0.05); border-radius:12px; display:flex; align-items:center; justify-content:center;">
    <span class="text-secondary"><?php echo t('chartLoading'); ?>...</span>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
