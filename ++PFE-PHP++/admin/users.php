<?php
$currentView = 'users';
require_once '../includes/header.php';

// Fetch all users
$users = to_camel_all($pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll());
?>

<div class="section-header">
  <h2><i class="fas fa-users me-2"></i><?php echo t('userManagement'); ?></h2>
  <button class="btn btn-neon btn-sm" onclick="RecLise.showAddUserModal()">
    <i class="fas fa-plus me-1"></i><?php echo t('addUser'); ?>
  </button>
</div>

<div class="glass-card" style="padding:20px;">
  <div class="mb-3">
    <input type="text" class="form-control" placeholder="<?php echo t('searchFilter'); ?>..." onkeyup="RecLise.filterUsers(this.value)">
  </div>
  <?php if (empty($users)): ?>
    <p class="text-secondary text-center padding:40px;"><?php echo t('noResults'); ?></p>
  <?php else: ?>
    <table class="table-glass" id="usersTable">
      <thead>
        <tr>
          <th><?php echo t('id'); ?></th>
          <th><?php echo t('fullName'); ?></th>
          <th><?php echo t('email'); ?></th>
          <th><?php echo t('department'); ?></th>
          <th><?php echo t('role'); ?></th>
          <th><?php echo t('status'); ?></th>
          <th><?php echo t('actions'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $user): ?>
          <tr>
            <td><?= $user['id'] ?></td>
            <td><?= htmlspecialchars($user['full_name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['department'] ?? '') ?></td>
            <td><span class="status-pill <?= $user['role'] === 'admin' ? 'status-new' : ($user['role'] === 'support' ? 'status-in-progress' : 'status-resolved') ?>">
              <?php echo t('role' . ucfirst($user['role'])); ?>
            </span></td>
            <td><span class="status-pill <?= $user['status'] === 'active' ? 'status-resolved' : 'status-closed' ?>">
              <?php echo t($user['status']); ?>
            </span></td>
            <td>
              <button class="btn btn-outline-neon btn-sm" onclick="RecLise.showEditUserModal(<?= $user['id'] ?>)">
                <i class="fas fa-pen"></i>
              </button>
              <button class="btn btn-sm btn-warning" onclick="RecLise.showResetPasswordModal(<?= $user['id'] ?>)">
                <i class="fas fa-key"></i>
              </button>
              <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                <button class="btn btn-sm btn-danger" onclick="RecLise.deleteUser(<?= $user['id'] ?>)">
                  <i class="fas fa-trash"></i>
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
