<?php
$currentView = 'user-management';
require_once '../includes/header.php';

// Fetch pending registrations
$registrations = to_camel_all($pdo->query("SELECT * FROM user_registrations ORDER BY created_at DESC")->fetchAll());
?>

<div class="section-header">
  <h2><i class="fas fa-user-plus me-2"></i><?php echo t('userManagement'); ?></h2>
</div>

<!-- Pending Registrations -->
<div class="glass-card mb-4" style="padding:20px;">
  <h5 class="mb-3"><i class="fas fa-clock me-2"></i>Pending Registrations</h5>
  <?php if (empty($registrations)): ?>
    <p class="text-secondary"><?php echo t('noResults'); ?></p>
  <?php else: ?>
    <table class="table-glass">
      <thead>
        <tr>
          <th><?php echo t('fullName'); ?></th>
          <th><?php echo t('email'); ?></th>
          <th><?php echo t('department'); ?></th>
          <th><?php echo t('role'); ?></th>
          <th><?php echo t('actions'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($registrations as $reg): ?>
          <tr>
            <td><?= htmlspecialchars($reg['full_name']) ?></td>
            <td><?= htmlspecialchars($reg['email']) ?></td>
            <td><?= htmlspecialchars($reg['department'] ?? '') ?></td>
            <td><span class="status-pill status-new"><?= htmlspecialchars($reg['requested_role']) ?></span></td>
            <td>
              <button class="btn btn-sm btn-neon" onclick="RecLise.approveRegistration(<?= $reg['id'] ?>)">
                <i class="fas fa-check"></i> Approve
              </button>
              <button class="btn btn-sm btn-danger" onclick="RecLise.rejectRegistration(<?= $reg['id'] ?>)">
                <i class="fas fa-times"></i> Reject
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
