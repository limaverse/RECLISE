<?php
$currentView = 'audit-logs';
require_once '../includes/header.php';

// Fetch audit logs
$logs = to_camel_all($pdo->query("SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 100")->fetchAll());
?>

<div class="section-header">
  <h2><i class="fas fa-clipboard-list me-2"></i><?php echo t('auditLogs'); ?></h2>
</div>

<div class="glass-card" style="padding:20px;">
  <?php if (empty($logs)): ?>
    <p class="text-secondary text-center padding:40px;"><?php echo t('noResults'); ?></p>
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
        <?php foreach ($logs as $log): ?>
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
