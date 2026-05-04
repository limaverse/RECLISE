<?php
$currentView = 'requests';
require_once '../includes/header.php';

// Fetch all requests
$requests = to_camel_all($pdo->query("SELECT r.*, u.full_name AS requester_name, u.department AS requester_dept, a.full_name AS assigned_name 
    FROM requests r 
    LEFT JOIN users u ON r.user_id = u.id 
    LEFT JOIN users a ON r.assigned_to = a.id 
    ORDER BY r.created_at DESC")->fetchAll());
?>

<div class="section-header">
  <h2><i class="fas fa-inbox me-2"></i><?php echo t('requests'); ?></h2>
  <div class="flex-gap-8">
    <select class="form-select" style="width: auto;" id="statusFilter" onchange="RecLise.filterByStatus()">
      <option value=""><?php echo t('allStatuses'); ?></option>
      <option value="new"><?php echo t('statusNew'); ?></option>
      <option value="in_progress"><?php echo t('statusInProgress'); ?></option>
      <option value="resolved"><?php echo t('statusResolved'); ?></option>
      <option value="escalated"><?php echo t('statusEscalated'); ?></option>
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
          <th><?php echo t('priority'); ?></th>
          <th><?php echo t('status'); ?></th>
          <th><?php echo t('assignedTo'); ?></th>
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
            <td><span class="priority-<?= $req['priority'] ?>"><?php echo t($req['priority']); ?></span></td>
            <td><span class="status-pill status-<?= $req['status'] ?>"><?php echo t('status' . ucfirst($req['status'])); ?></span></td>
            <td><?= htmlspecialchars($req['assigned_name'] ?? 'Unassigned') ?></td>
            <td><?= date('Y-m-d', strtotime($req['created_at'])) ?></td>
            <td>
              <button class="btn btn-outline-neon btn-sm" onclick="RecLise.viewRequestDetail(<?= $req['id'] ?>)">
                <i class="fas fa-eye me-1"></i><?php echo t('view'); ?>
              </button>
              <?php if ($req['status'] !== 'resolved' && $req['status'] !== 'closed'): ?>
                <button class="btn btn-neon btn-sm" style="margin-left:4px;" onclick="RecLise.processRequest(<?= $req['id'] ?>)">
                  <i class="fas fa-cog me-1"></i><?php echo t('process'); ?>
                </button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
