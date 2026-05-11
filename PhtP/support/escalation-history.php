<?php
$currentView = 'escalation-history';
require_once '../includes/header.php';

// Fetch escalated requests assigned to this user or managed by them
$userId = $_SESSION['user_id'];
$requests = to_camel_all($pdo->query("SELECT r.*, u.full_name AS requesterName 
    FROM requests r 
    LEFT JOIN users u ON r.user_id = u.id 
    WHERE r.escalated_to_admin = 1 
    ORDER BY r.created_at DESC")->fetchAll());
?>

<div class="section-header">
  <h2><i class="fas fa-history me-2"></i><?php echo t('escalationHistory'); ?></h2>
</div>

<?php if (empty($requests)): ?>
  <div class="empty-state">
    <i class="fas fa-inbox"></i>
    <p><?php echo t('noResults'); ?></p>
  </div>
<?php else: ?>
  <div class="glass-card overflow-auto" style="padding:20px;">
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
            <td>
              <span class="text-neon">#<?= $r['id'] ?></span>
            </td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <i class="fas fa-ticket-alt text-secondary"></i>
                <?= htmlspecialchars($r['title'] ?? 'N/A') ?>
              </div>
            </td>
            <td>
              <span class="priority-<?= $r['priority'] ?? '' ?>">
                <?php echo t($r['priority'] ?? ''); ?>
              </span>
            </td>
            <td>
              <span class="status-pill status-escalated">
                <?php echo t('escalated'); ?>
              </span>
            </td>
            <td>
              <small class="text-secondary">
                <i class="fas fa-calendar me-1"></i>
                <?= ($r['createdAt'] ?? null) ? date('Y-m-d', strtotime($r['createdAt'])) : 'N/A' ?>
              </small>
            </td>
            <td>
              <button class="btn btn-outline-neon btn-sm" onclick="RecLise.viewRequestDetail(<?= $r['id'] ?>)">
                <i class="fas fa-eye me-1"></i><?php echo t('view'); ?>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
