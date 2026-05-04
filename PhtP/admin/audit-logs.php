<?php
$currentView = 'audit-logs';
require_once '../includes/header.php';

// Fetch audit logs
$logs = to_camel_all($pdo->query("SELECT l.*, u.full_name FROM audit_logs l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.timestamp DESC LIMIT 100")->fetchAll());
?>

<div class="section-header">
  <h2><i class="fas fa-scroll me-2"></i><?php echo t('auditLogs'); ?></h2>
</div>

<div class="glass-card overflow-auto" style="padding:20px;">
  <table class="table-glass">
    <thead>
      <tr>
        <th><?php echo t('id'); ?></th>
        <th><?php echo t('action'); ?></th>
        <th><?php echo t('fullName'); ?></th>
        <th><?php echo t('details'); ?></th>
        <th><?php echo t('timestamp'); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($logs as $log): ?>
        <tr>
          <td>#<?= $log['id'] ?></td>
          <td><span class="status-action"><?= htmlspecialchars($log['action']) ?></span></td>
          <td><?= htmlspecialchars($log['fullName'] ?? 'System') ?></td>
          <td><?= htmlspecialchars($log['details']) ?></td>
          <td><?= date('Y-m-d H:i:s', strtotime($log['timestamp'])) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once '../includes/footer.php'; ?>
