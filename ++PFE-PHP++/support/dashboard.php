<?php
$currentView = 'dashboard';
require_once '../includes/header.php';

// Fetch stats for support dashboard
$totalRequests = $pdo->query("SELECT COUNT(*) FROM requests")->fetchColumn();
$newRequests = $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'new'")->fetchColumn();
$inProgress = $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'in_progress'")->fetchColumn();
$escalated = $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'escalated'")->fetchColumn();

// Recent requests
$recentRequests = to_camel_all($pdo->query("SELECT r.*, u.full_name AS requester_name 
    FROM requests r 
    LEFT JOIN users u ON r.user_id = u.id 
    ORDER BY r.created_at DESC LIMIT 10")->fetchAll());
?>

<div class="section-header">
  <h2><i class="fas fa-gauge-high me-2"></i><?php echo t('dashboard'); ?></h2>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
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
        <i class="fas fa-plus"></i>
      </div>
      <div class="stat-value"><?= $newRequests ?></div>
      <div class="stat-label"><?php echo t('newRequests'); ?></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card">
      <div class="stat-icon">
        <i class="fas fa-spinner"></i>
      </div>
      <div class="stat-value"><?= $inProgress ?></div>
      <div class="stat-label"><?php echo t('inProgress'); ?></div>
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

<!-- Recent Requests -->
<div class="glass-card mb-4" style="padding: 20px;">
  <div class="section-header">
    <h3><i class="fas fa-inbox me-2"></i><?php echo t('recentActivity'); ?></h3>
    <a href="requests.php" class="btn btn-outline-neon btn-sm"><i class="fas fa-eye me-1"></i><?php echo t('viewAll'); ?></a>
  </div>
  <?php if (empty($recentRequests)): ?>
    <p class="text-secondary"><?php echo t('noResults'); ?></p>
  <?php else: ?>
    <table class="table-glass">
      <thead>
        <tr>
          <th><?php echo t('id'); ?></th>
          <th><?php echo t('requester'); ?></th>
          <th><?php echo t('title'); ?></th>
          <th><?php echo t('status'); ?></th>
          <th><?php echo t('actions'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentRequests as $req): ?>
          <tr>
            <td>#<?= $req['id'] ?></td>
            <td><?= htmlspecialchars($req['requester_name'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($req['title']) ?></td>
            <td><span class="status-pill status-<?= $req['status'] ?>"><?php echo t('status' . ucfirst($req['status'])); ?></span></td>
            <td>
              <button class="btn btn-outline-neon btn-sm" onclick="RecLise.viewRequestDetail(<?= $req['id'] ?>)">
                <i class="fas fa-eye me-1"></i><?php echo t('view'); ?>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
