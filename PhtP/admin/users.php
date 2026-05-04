<?php
$currentView = 'users';
require_once '../includes/header.php';

// Fetch all users
$users = to_camel_all($pdo->query("SELECT * FROM users ORDER BY joined_at DESC")->fetchAll());
?>

<div class="section-header">
  <h2><i class="fas fa-users-gear me-2"></i><?php echo t('userManagement'); ?></h2>
  <button class="btn btn-neon btn-sm" onclick="RecLise.showAddUserModal()">
    <i class="fas fa-plus me-1"></i><?php echo t('addUser'); ?>
  </button>
</div>

<div class="glass-card" style="padding:20px;">
  <div class="mb-3">
    <input type="text" class="form-control" id="userSearchInput" placeholder="<?php echo t('search'); ?>..." oninput="RecLise.filterUsers(this.value)">
  </div>
  <div class="overflow-auto">
    <table class="table-glass" id="usersTable">
      <thead>
        <tr>
          <th><?php echo t('id'); ?></th>
          <th><?php echo t('fullName'); ?></th>
          <th><?php echo t('email'); ?></th>
          <th><?php echo t('role'); ?></th>
          <th><?php echo t('department'); ?></th>
          <th><?php echo t('status'); ?></th>
          <th><?php echo t('actions'); ?></th>
        </tr>
      </thead>
      <tbody id="userTableBody">
        <?php foreach ($users as $u): ?>
          <tr>
            <td>#<?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['fullName']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><span class="status-pill status-accent"><?= htmlspecialchars($u['role']) ?></span></td>
            <td><?= htmlspecialchars($u['department'] ?? '—') ?></td>
            <td><span class="status-pill <?= $u['status'] === 'active' ? 'status-resolved' : 'status-closed' ?>">
              <?php echo t($u['status'] === 'active' ? 'active' : 'inactive'); ?>
            </span></td>
            <td>
              <div style="display:flex;gap:4px;flex-wrap:wrap;">
                <button class="btn btn-outline-neon btn-sm" onclick="RecLise.showEditUserModal(<?= $u['id'] ?>)" title="<?php echo t('edit'); ?>">
                  <i class="fas fa-pen"></i>
                </button>
                <button class="btn btn-sm btn-warning" onclick="RecLise.showDelegateModal(<?= $u['id'] ?>)" title="<?php echo t('delegateRole'); ?>">
                  <i class="fas fa-user-shield"></i>
                </button>
                <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                  <button class="btn btn-sm btn-danger" onclick="RecLise.deleteUser(<?= $u['id'] ?>)" title="<?php echo t('delete'); ?>">
                    <i class="fas fa-trash"></i>
                  </button>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
