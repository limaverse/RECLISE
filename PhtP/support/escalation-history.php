<?php
$currentView = 'escalation-history';
require_once '../includes/header.php';

// Fetch escalated requests assigned to this user or managed by them
$userId = $_SESSION['user_id'];
$requests = to_camel_all($pdo->query("SELECT r.*, u.full_name AS requester_name 
    FROM requests r 
    LEFT JOIN users u ON r.user_id = u.id 
    WHERE r.escalated_to_admin = 1 
    ORDER BY r.updated_at DESC")->fetchAll());
?>

<div class="section-header">
  <h2><i class="fas fa-history me-2"></i><?php echo t('escalationHistory'); ?></h2>
</div>

<div class="glass-card overflow-auto" style="padding:20px;">
  <?php if (empty($requests)): ?>
    <p class="text-secondary text-center padding:40px;"><?php echo t('noResults'); ?></p>
  <?php else: ?>
    <table class="table-glass">
      <thead>
        <tr>
          <th><?php echo t('id'); ?></th>
          <th><?php echo t('title'); ?></th>
          <th><?php echo t('priority'); ?></th>
          <th><?php echo t('status'); ?></th>
          <th><?php echo t('date'); ?></th>
          <th><?php echo t('actions'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($requests as $r): ?>
          <tr>
            <td>#<?= $r['id'] ?></td>
            <td><?= htmlspecialchars($r['title']) ?></td>
            <td><span class="priority-<?= $r['priority'] ?>"><?php echo t($r['priority']); ?></span></td>
            <td><span class="status-pill status-escalated"><?php echo t('escalated'); ?></span></td>
            <td><?= date('Y-m-d', strtotime($r['updated_at'])) ?></td>
            <td>
              <button class="btn btn-outline-neon btn-sm" onclick="RecLise.viewRequestDetail(<?= $r['id'] ?>)">
                <?php echo t('view'); ?>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
