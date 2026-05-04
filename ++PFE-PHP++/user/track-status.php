<?php
$currentView = 'track-status';
require_once '../includes/header.php';

// Fetch user's requests
$requests = to_camel_all($pdo->query("SELECT r.*, u.full_name AS requester_name, u.department AS requester_dept 
    FROM requests r 
    LEFT JOIN users u ON r.user_id = u.id 
    WHERE r.user_id = {$_SESSION['user_id']} 
    ORDER BY r.created_at DESC")->fetchAll());
?>

<div class="section-header">
  <h2><i class="fas fa-search me-2"></i><?php echo t('trackStatus'); ?></h2>
  <div class="flex-gap-8">
    <select class="form-select" style="width: auto;" id="statusFilter" onchange="RecLise.filterByStatus()">
      <option value=""><?php echo t('allStatuses'); ?></option>
      <option value="new"><?php echo t('statusNew'); ?></option>
      <option value="in_progress"><?php echo t('statusInProgress'); ?></option>
      <option value="resolved"><?php echo t('statusResolved'); ?></option>
      <option value="escalated"><?php echo t('statusEscalated'); ?></option>
      <option value="closed"><?php echo t('statusClosed'); ?></option>
    </select>
  </div>
</div>

<div class="glass-card" style="padding: 20px;">
  <?php if (empty($requests)): ?>
    <p class="text-secondary text-center padding: 40px;"><?php echo t('noResults'); ?></p>
  <?php else: ?>
    <table class="table-glass">
      <thead>
        <tr>
          <th><?php echo t('id'); ?></th>
          <th><?php echo t('requester'); ?></th>
          <th><?php echo t('title'); ?></th>
          <th><?php echo t('category'); ?></th>
          <th><?php echo t('status'); ?></th>
          <th><?php echo t('date'); ?></th>
          <th><?php echo t('actions'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($requests as $req): ?>
          <tr data-status="<?= $req['status'] ?>">
            <td>#<?= $req['id'] ?></td>
            <td><?= htmlspecialchars($req['requester_name'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($req['title']) ?></td>
            <td><span class="status-pill status-<?= $req['category'] ?>"><?php echo t($req['category']); ?></span></td>
            <td><span class="status-pill status-<?= $req['status'] ?>"><?php echo t('status' . ucfirst($req['status'])); ?></span></td>
            <td><?= date('Y-m-d', strtotime($req['created_at'])) ?></td>
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
