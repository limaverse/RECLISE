<?php
$currentView = 'users';
require_once '../includes/header.php';

// Fetch all users
$users = to_camel_all($pdo->query("SELECT * FROM users ORDER BY joined_at DESC")->fetchAll());

// Fetch pending registrations
$pendingRegs = to_camel_all($pdo->query("SELECT * FROM user_registrations WHERE status = 'pending' ORDER BY created_at ASC")->fetchAll());
?>

<div class="section-header">
  <h2><i class="fas fa-users-gear me-2"></i><?php echo t('userManagement'); ?></h2>
    <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
      <div style="min-width:250px;">
        <input type="text" class="form-control" id="userSearchInput" placeholder="<?php echo t('search'); ?>..." oninput="filterUsers(this.value)" autocomplete="off">
      </div>
      <button class="btn btn-neon btn-sm" onclick="document.getElementById('addUserModal').style.display='flex'">
        <i class="fas fa-plus me-1"></i><?php echo t('addUser'); ?>
      </button>
    </div>
</div>

<?php if (!empty($pendingRegs)): ?>
<div class="glass-card mb-4" style="padding:20px; border-left:4px solid var(--warning);">
  <h4 class="mb-3"><i class="fas fa-clock me-2"></i>Pending Registrations</h4>
  <div class="overflow-auto">
    <table class="table-glass">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Department</th>
          <th>Requested At</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pendingRegs as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['fullName']) ?></td>
            <td><?= htmlspecialchars($r['email']) ?></td>
            <td><?= htmlspecialchars($r['department']) ?></td>
            <td><?= htmlspecialchars($r['createdAt']) ?></td>
            <td>
              <button class="btn btn-sm btn-success me-2" onclick="approveReg(<?= $r['id'] ?>)">Approve</button>
              <button class="btn btn-sm btn-danger" onclick="rejectReg(<?= $r['id'] ?>)">Reject</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<div class="glass-card" style="padding:20px;">
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
                <button class="btn btn-outline-neon btn-sm" onclick="showEditModal(<?= $u['id'] ?>,'<?= htmlspecialchars(addslashes($u['fullName'])) ?>','<?= htmlspecialchars(addslashes($u['email'])) ?>','<?= htmlspecialchars(addslashes($u['department'] ?? '')) ?>','<?= htmlspecialchars(addslashes($u['phone'] ?? '')) ?>','<?= $u['status'] ?>')" title="<?php echo t('edit'); ?>">
                  <i class="fas fa-pen"></i>
                </button>
                <button class="btn btn-sm btn-warning" onclick="showDelegateModal(<?= $u['id'] ?>, '<?= htmlspecialchars(addslashes($u['fullName'])) ?>')" title="<?php echo t('delegateRole'); ?>">
                  <i class="fas fa-user-shield"></i>
                </button>
                <button class="btn btn-sm btn-info" onclick="showResetPasswordModal(<?= $u['id'] ?>)" title="Reset Password">
                  <i class="fas fa-key"></i>
                </button>
                <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                  <button class="btn btn-sm btn-danger" onclick="deleteUser(<?= $u['id'] ?>, '<?= htmlspecialchars(addslashes($u['fullName'])) ?>')" title="<?php echo t('delete'); ?>">
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

<!-- Reset Password Modal -->
<div class="modal-overlay" id="resetPasswordModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);z-index:1060;align-items:center;justify-content:center;padding:20px;">
  <div class="modal-box" style="background:var(--modal-bg);backdrop-filter:blur(16px);border:1px solid var(--glass-border);border-radius:20px;padding:32px;width:100%;max-width:600px;max-height:85vh;overflow-y:auto;position:relative;">
    <button class="modal-close" onclick="closeModalById('resetPasswordModal')" style="position:absolute;top:16px;right:16px;background:none;border:none;color:var(--text-secondary);font-size:1.3rem;cursor:pointer;"><i class="fas fa-times"></i></button>
    <h3 style="margin-bottom:20px;font-family:'Inter',sans-serif;font-weight:800;"><i class="fas fa-key me-2"></i>Reset Password</h3>
    <form onsubmit="event.preventDefault();var p1=document.getElementById('newPassword').value,p2=document.getElementById('confirmPassword').value;if(!p1||!p2){alert('Fill both fields');return;}if(p1!==p2){alert('Passwords dont match');return;}fetch('/pfeeeee/PhtP/ajax/api.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action: 'resetPassword',id:parseInt(document.getElementById('resetUserId').value),password:p1})}).then(r=>r.json()).then(r=>{if(!r.success)alert(r.message);else{alert('Password reset!');closeModalById('resetPasswordModal');setTimeout(()=>location.reload(),500);}});">
      <input type="hidden" name="id" id="resetUserId" value="">
      <div class="mb-3">
        <label class="form-label">New Password</label>
        <input type="password" class="form-control" name="password" id="newPassword" required placeholder="Enter new password">
      </div>
      <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <input type="password" class="form-control" id="confirmPassword" required placeholder="Confirm new password">
      </div>
      <div style="display:flex;gap:8px;justify-content:flex-end;">
        <button type="button" onclick="closeModalById('resetPasswordModal')" style="background:var(--btn-bg);color:var(--btn-text);border:1px solid var(--btn-border);border-radius:12px;padding:10px 24px;font-weight:600;cursor:pointer;">Cancel</button>
        <button type="submit" class="btn btn-neon">Reset Password</button>
      </div>
    </form>
  </div>
</div>

<!-- Add User Modal -->
<div class="modal-overlay" id="addUserModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);z-index:1060;align-items:center;justify-content:center;padding:20px;">
  <div class="modal-box" style="background:var(--modal-bg);backdrop-filter:blur(16px);border:1px solid var(--glass-border);border-radius:20px;padding:32px;width:100%;max-width:600px;max-height:85vh;overflow-y:auto;position:relative;">
    <button class="modal-close" onclick="closeModalById('addUserModal')" style="position:absolute;top:16px;right:16px;background:none;border:none;color:var(--text-secondary);font-size:1.3rem;cursor:pointer;"><i class="fas fa-times"></i></button>
    <h3 style="margin-bottom:20px;font-family:'Inter',sans-serif;font-weight:800;"><i class="fas fa-user-plus me-2"></i>Add User</h3>
    <form onsubmit="event.preventDefault();var n=document.getElementById('modalFullName').value,e=document.getElementById('modalEmail').value,r=document.getElementById('modalRole').value,d=document.getElementById('modalDept').value,p=document.getElementById('modalPhone').value,pw=document.getElementById('modalPass').value;if(!n||!e){alert('Name and email required');return;}fetch('/pfeeeee/PhtP/ajax/api.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action: 'addUser',full_name:n,email:e,role:r,department:d,phone:p,password:pw})}).then(r=>r.json()).then(r=>{if(!r.success)alert(r.message);else{alert('User added!');closeModalById('addUserModal');setTimeout(()=>location.reload(),500);}});">
      <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" class="form-control" id="modalFullName" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" id="modalEmail" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Role</label>
        <select class="form-select" id="modalRole">
          <option value="user">User</option>
          <option value="support">Support</option>
          <option value="admin">Admin</option>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Department</label>
        <input type="text" class="form-control" id="modalDept">
      </div>
      <div class="mb-3">
        <label class="form-label">Phone</label>
        <input type="text" class="form-control" id="modalPhone">
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" class="form-control" id="modalPass" required>
      </div>
      <div style="display:flex;gap:8px;justify-content:flex-end;">
        <button type="button" onclick="closeModalById('addUserModal')" style="background:var(--btn-bg);color:var(--btn-text);border:1px solid var(--btn-border);border-radius:12px;padding:10px 24px;font-weight:600;cursor:pointer;">Cancel</button>
        <button type="submit" class="btn btn-neon">Add User</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit User Modal -->
<div class="modal-overlay" id="editUserModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);z-index:1060;align-items:center;justify-content:center;padding:20px;">
  <div class="modal-box" style="background:var(--modal-bg);backdrop-filter:blur(16px);border:1px solid var(--glass-border);border-radius:20px;padding:32px;width:100%;max-width:600px;max-height:85vh;overflow-y:auto;position:relative;">
    <button class="modal-close" onclick="closeModalById('editUserModal')" style="position:absolute;top:16px;right:16px;background:none;border:none;color:var(--text-secondary);font-size:1.3rem;cursor:pointer;"><i class="fas fa-times"></i></button>
    <h3 style="margin-bottom:20px;font-family:'Inter',sans-serif;font-weight:800;"><i class="fas fa-user-edit me-2"></i>Edit User</h3>
    <form onsubmit="event.preventDefault();var id=document.getElementById('editUserId').value,n=document.getElementById('editFullName').value,e=document.getElementById('editEmail').value,d=document.getElementById('editDept').value,p=document.getElementById('editPhone').value,s=document.getElementById('editStatus').value;if(!n||!e){alert('Name and email required');return;}fetch('/pfeeeee/PhtP/ajax/api.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action: 'updateUser',id:parseInt(id),full_name:n,email:e,department:d,phone:p,status:s})}).then(r=>r.json()).then(r=>{if(!r.success)alert(r.message);else{alert('User updated!');closeModalById('editUserModal');setTimeout(()=>location.reload(),500);}});">
      <input type="hidden" id="editUserId" value="">
      <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" class="form-control" id="editFullName" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" id="editEmail" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Department</label>
        <input type="text" class="form-control" id="editDept">
      </div>
      <div class="mb-3">
        <label class="form-label">Phone</label>
        <input type="text" class="form-control" id="editPhone">
      </div>
      <div class="mb-3">
        <label class="form-label">Status</label>
        <select class="form-select" id="editStatus">
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>
      <div style="display:flex;gap:8px;justify-content:flex-end;">
        <button type="button" onclick="closeModalById('editUserModal')" style="background:var(--btn-bg);color:var(--btn-text);border:1px solid var(--btn-border);border-radius:12px;padding:10px 24px;font-weight:600;cursor:pointer;">Cancel</button>
        <button type="submit" class="btn btn-neon">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- Delegate Role Modal -->
<div class="modal-overlay" id="delegateModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);z-index:1060;align-items:center;justify-content:center;padding:20px;">
  <div class="modal-box" style="background:var(--modal-bg);backdrop-filter:blur(16px);border:1px solid var(--glass-border);border-radius:20px;padding:32px;width:100%;max-width:600px;max-height:85vh;overflow-y:auto;position:relative;">
    <button class="modal-close" onclick="closeModalById('delegateModal')" style="position:absolute;top:16px;right:16px;background:none;border:none;color:var(--text-secondary);font-size:1.3rem;cursor:pointer;"><i class="fas fa-times"></i></button>
    <h3 style="margin-bottom:20px;font-family:'Inter',sans-serif;font-weight:800;"><i class="fas fa-user-shield me-2"></i>Delegate Role</h3>
    <form onsubmit="event.preventDefault();var id=document.getElementById('delegateUserId').value,r=document.getElementById('newRole').value;fetch('/pfeeeee/PhtP/ajax/api.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action: 'delegateRole',id:parseInt(id),role:r})}).then(r=>r.json()).then(r=>{if(!r.success)alert(r.message);else{alert('Role updated!');closeModalById('delegateModal');setTimeout(()=>location.reload(),500);}});">
      <input type="hidden" id="delegateUserId" value="">
      <p>Change role for user: <strong id="delegateUserName"></strong></p>
      <div class="mb-3">
        <label class="form-label">New Role</label>
        <select class="form-select" id="newRole">
          <option value="user">User</option>
          <option value="support">Support</option>
          <option value="admin">Admin</option>
        </select>
      </div>
      <div style="display:flex;gap:8px;justify-content:flex-end;">
        <button type="button" onclick="closeModalById('delegateModal')" style="background:var(--btn-bg);color:var(--btn-text);border:1px solid var(--btn-border);border-radius:12px;padding:10px 24px;font-weight:600;cursor:pointer;">Cancel</button>
        <button type="submit" class="btn btn-neon">Update Role</button>
      </div>
    </form>
  </div>
</div>

<script>
window.closeModalById = function(id) { 
    var el = document.getElementById(id); 
    if (el) el.style.display = 'none'; 
};

window.approveReg = function(id) {
    if (!confirm('Approve this registration?')) return;
    const fd = new FormData();
    fd.append('action', 'approveRegistration');
    fd.append('id', id);
    fetch('/pfeeeee/PhtP/ajax/api.php', { method: 'POST', body: fd })
      .then(r => r.json())
      .then(d => {
        if(d.success) { alert(d.message); location.reload(); }
        else alert(d.error || 'Error');
      });
};

window.rejectReg = function(id) {
    if (!confirm('Reject this registration?')) return;
    const fd = new FormData();
    fd.append('action', 'rejectRegistration');
    fd.append('id', id);
    fetch('/pfeeeee/PhtP/ajax/api.php', { method: 'POST', body: fd })
      .then(r => r.json())
      .then(d => {
        if(d.success) { location.reload(); }
        else alert(d.error || 'Error');
      });
};

window.showEditModal = function(id, name, email, dept, phone, status) {
    document.getElementById('editUserId').value = id;
    document.getElementById('editFullName').value = name;
    document.getElementById('editEmail').value = email;
    document.getElementById('editDept').value = dept || '';
    document.getElementById('editPhone').value = phone || '';
    document.getElementById('editStatus').value = status;
    document.getElementById('editUserModal').style.display = 'flex';
};
window.showDelegateModal = function(id, name) {
    document.getElementById('delegateUserId').value = id;
    document.getElementById('delegateUserName').textContent = name;
    document.getElementById('delegateModal').style.display = 'flex';
};
window.showResetPasswordModal = function(id) {
    document.getElementById('resetUserId').value = id;
    document.getElementById('newPassword').value = '';
    document.getElementById('confirmPassword').value = '';
    document.getElementById('resetPasswordModal').style.display = 'flex';
};
window.deleteUser = function(id, name) {
    if (!confirm('Delete user "' + name + '"? This cannot be undone.')) return;
    fetch('/pfeeeee/PhtP/ajax/api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'deleteUser', id: id })
    })
    .then(r => r.json())
    .then(function(data) {
        if (data.success) {
            alert('User deleted!');
            location.reload();
        } else {
            alert(data.message || 'Error deleting user');
        }
    })
    .catch(function() { alert('Error deleting user'); });
};
</script>

<?php require_once '../includes/footer.php'; ?>

