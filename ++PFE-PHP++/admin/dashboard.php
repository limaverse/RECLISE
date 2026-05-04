<?php
$currentView = 'dashboard';
require_once '../includes/header.php';

// Fetch stats for admin dashboard
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalRequests = $pdo->query("SELECT COUNT(*) FROM requests")->fetchColumn();
$resolved = $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'resolved'")->fetchColumn();
$escalated = $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'escalated'")->fetchColumn();

// Recent activity
$recentActivity = to_camel_all($pdo->query("SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 10")->fetchAll());
?>

<div class="section-header">
  <h2><i class="fas fa-gauge-high me-2"></i><?php echo t('dashboard'); ?></h2>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="stat-card">
      <div class="stat-icon">
        <i class="fas fa-users"></i>
      </div>
      <div class="stat-value"><?= $totalUsers ?></div>
      <div class="stat-label"><?php echo t('totalUsers'); ?></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card">
      <div class="stat-icon">
        <i class="fas fa-inbox"></i>
      </div>
      <div class="stat-value"><?= $totalRequests ?></div>
      <div class="stat-label"><?php echo t('totalRequests'); ?></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card">
      <div class="stat-icon">
        <i class="fas fa-check"></i>
      </div>
      <div class="stat-value"><?= $resolved ?></div>
      <div class="stat-label"><?php echo t('resolved'); ?></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card">
      <div class="stat-icon">
        <i class="fas fa-exclamation-triangle"></i>
      </div>
      <div class="stat-value"><?= $escalated ?></div>
      <div class="stat-label"><?php echo t('escalated'); ?></div>
    </div>
  </div>
</div>

<!-- Recent Activity -->
<div class="glass-card mb-4" style="padding: 20px;">
  <div class="section-header">
    <h3><i class="fas fa-clock-rotate-left me-2"></i><?php echo t('recentActivity'); ?></h3>
    <a href="history.php" class="btn btn-outline-neon btn-sm"><i class="fas fa-eye me-1"></i><?php echo t('viewAll'); ?></a>
  </div>
  <?php if (empty($recentActivity)): ?>
    <p class="text-secondary"><?php echo t('noResults'); ?></p>
  <?php else: ?>
    <table class="table-glass">
      <thead>
        <tr>
          <th><?php echo t('action'); ?></th>
          <th><?php echo t('userId'); ?></th>
          <th><?php echo t('details'); ?></th>
          <th><?php echo t('timestamp'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentActivity as $log): ?>
          <tr>
            <td><span class="status-pill status-new"><?= htmlspecialchars($log['action']) ?></span></td>
            <td><?= $log['user_id'] ?></td>
            <td><?= htmlspecialchars($log['details']) ?></td>
            <td><?= date('Y-m-d H:i', strtotime($log['created_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
