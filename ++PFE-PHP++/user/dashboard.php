<?php
$currentView = 'dashboard';
require_once '../includes/header.php';

// Fetch stats for dashboard
$totalRequests = $pdo->query("SELECT COUNT(*) FROM requests WHERE user_id = {$_SESSION['user_id']}")->fetchColumn();
$newRequests = $pdo->query("SELECT COUNT(*) FROM requests WHERE user_id = {$_SESSION['user_id']} AND status = 'new'")->fetchColumn();
$inProgress = $pdo->query("SELECT COUNT(*) FROM requests WHERE user_id = {$_SESSION['user_id']} AND status = 'in_progress'")->fetchColumn();
$resolved = $pdo->query("SELECT COUNT(*) FROM requests WHERE user_id = {$_SESSION['user_id']} AND status = 'resolved'")->fetchColumn();

// Recent requests
$recentRequests = to_camel_all($pdo->query("SELECT * FROM requests WHERE user_id = {$_SESSION['user_id']} ORDER BY created_at DESC LIMIT 5")->fetchAll());

// Recent activity (audit logs for this user)
$recentActivity = to_camel_all($pdo->query("SELECT * FROM audit_logs WHERE user_id = {$_SESSION['user_id']} ORDER BY created_at DESC LIMIT 10")->fetchAll());
?>

<div class="section-header">
  <h2><i class="fas fa-gauge-high me-2"></i><?php echo t('dashboard'); ?></h2>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="stat-card">
      <div class="stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="7" height="7" rx="1"/>
          <rect x="14" y="3" width="7" height="7" rx="1"/>
          <rect x="3" y="14" width="7" height="7" rx="1"/>
          <rect x="14" y="14" width="7" height="7" rx="1"/>
        </svg>
      </div>
      <div class="stat-value"><?= $totalRequests ?></div>
      <div class="stat-label"><?php echo t('totalRequests'); ?></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card">
      <div class="stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="9"/>
          <path d="M12 8v4l3 3"/>
        </svg>
      </div>
      <div class="stat-value"><?= $newRequests ?></div>
      <div class="stat-label"><?php echo t('newRequests'); ?></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card">
      <div class="stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 2v4m0 12v4m-7.07-3.93l2.83-2.83m8.48 0l2.83 2.83M2 12h4m12 0h4"/>
        </svg>
      </div>
      <div class="stat-value"><?= $inProgress ?></div>
      <div class="stat-label"><?php echo t('inProgress'); ?></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card">
      <div class="stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 12l2 2 4-4"/>
          <circle cx="12" cy="12" r="9"/>
        </svg>
      </div>
      <div class="stat-value"><?= $resolved ?></div>
      <div class="stat-label"><?php echo t('resolved'); ?></div>
    </div>
  </div>
</div>

<!-- Recent Requests -->
<div class="glass-card mb-4" style="padding: 20px;">
  <div class="section-header">
    <h3><i class="fas fa-inbox me-2"></i><?php echo t('recentActivity'); ?></h3>
    <a href="track-status.php" class="btn btn-outline-neon btn-sm"><i class="fas fa-eye me-1"></i><?php echo t('viewAll'); ?></a>
  </div>
  <?php if (empty($recentRequests)): ?>
    <p class="text-secondary"><?php echo t('noResults'); ?></p>
  <?php else: ?>
    <table class="table-glass">
      <thead>
        <tr>
          <th><?php echo t('id'); ?></th>
          <th><?php echo t('title'); ?></th>
          <th><?php echo t('status'); ?></th>
          <th><?php echo t('date'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentRequests as $req): ?>
        <tr>
          <td>#<?= $req['id'] ?></td>
          <td><?php echo htmlspecialchars($req['title']); ?></td>
          <td><span class="status-pill <?= 'status-' . $req['status'] ?>"><?php echo t('status' . ucfirst($req['status'])); ?></span></td>
          <td><?= date('Y-m-d', strtotime($req['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<!-- Quick Actions -->
<div class="glass-card" style="padding: 20px;">
  <h3 class="mb-3"><i class="fas fa-bolt me-2"></i><?php echo t('quickActions'); ?></h3>
  <div class="d-flex gap-2">
    <a href="submit-request.php" class="btn btn-neon"><i class="fas fa-plus me-2"></i><?php echo t('submitRequest'); ?></a>
    <a href="messages.php" class="btn btn-outline-neon"><i class="fas fa-comments me-2"></i><?php echo t('messages'); ?></a>
    <a href="training.php" class="btn btn-outline-neon"><i class="fas fa-graduation-cap me-2"></i><?php echo t('training'); ?></a>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
