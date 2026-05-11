<?php
$currentView = 'audit-logs';
require_once '../includes/header.php';

// Fetch audit logs
$logs = to_camel_all($pdo->query("SELECT l.*, u.full_name, u.role, l.created_at AS timestamp FROM audit_logs l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC LIMIT 100")->fetchAll());
?>

<div class="section-header">
  <h2><i class="fas fa-scroll me-2"></i><?php echo t('auditLogs'); ?></h2>
</div>

<div class="glass-card overflow-auto">
  <table class="table-glass">
    <thead>
      <tr>
        <th style="text-align:center;width:80px;"><?php echo t('id'); ?></th>
        <th style="text-align:center;width:120px;"><?php echo t('action'); ?></th>
        <th style="text-align:left;min-width:180px;"><?php echo t('fullName'); ?></th>
        <th style="text-align:left;"><?php echo t('details'); ?></th>
        <th style="text-align:center;width:180px;"><?php echo t('timestamp'); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($logs as $log): ?>
        <tr>
          <td style="text-align:center;">#<?= $log['id'] ?></td>
          <td style="text-align:center;"><span class="status-action"><?= htmlspecialchars($log['action']) ?></span></td>
          <td style="text-align:left;"><?= htmlspecialchars($log['fullName'] ?? 'System') ?></td>
          <td style="text-align:left;"><?= htmlspecialchars($log['details']) ?></td>
          <td style="text-align:center;white-space:nowrap;"><?= date('Y-m-d H:i:s', strtotime($log['timestamp'])) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once '../includes/footer.php'; ?>
