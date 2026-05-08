<?php
$currentView = 'technical-issues';
require_once '../includes/header.php';

// Fetch technical issues
$issues = to_camel_all($pdo->query("SELECT * FROM technical_issues ORDER BY created_at DESC")->fetchAll());
?>

<div class="section-header">
  <h2><i class="fas fa-bug me-2"></i><?php echo t('technicalProblems'); ?></h2>
  <button class="btn btn-neon btn-sm" onclick="showAddIssueModal()">
    <i class="fas fa-plus me-1"></i><?php echo t('addIssue'); ?>
  </button>
</div>

<div class="glass-card" style="padding:20px;">
  <?php if (empty($issues)): ?>
    <p class="text-secondary text-center padding:40px;"><?php echo t('noResults'); ?></p>
  <?php else: ?>
    <table class="table-glass">
      <thead>
        <tr>
          <th><?php echo t('id'); ?></th>
          <th><?php echo t('description'); ?></th>
          <th><?php echo t('status'); ?></th>
          <th><?php echo t('date'); ?></th>
          <th><?php echo t('actions'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($issues as $issue): ?>
          <tr>
            <td><?= $issue['id'] ?></td>
            <td><?= htmlspecialchars($issue['description']) ?></td>
            <td><span class="status-pill <?= $issue['status'] === 'open' ? 'status-new' : 'status-resolved' ?>">
              <?php echo t($issue['status']); ?>
            </span></td>
            <td><?= date('Y-m-d', strtotime($issue['createdAt'])) ?></td>
            <td>
              <?php if ($issue['status'] === 'open'): ?>
                <button class="btn btn-sm btn-outline-neon" onclick="resolveIssue(<?= $issue['id'] ?>)">
                  <i class="fas fa-check"></i> <?php echo t('resolve'); ?>
                </button>
              <?php endif; ?>
              <button class="btn btn-sm btn-danger" onclick="deleteIssue(<?= $issue['id'] ?>)">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<!-- Add Issue Modal -->
<div class="modal-overlay" id="addIssueModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);z-index:1060;align-items:center;justify-content:center;padding:20px;">
  <div class="modal-box" style="background:var(--modal-bg);backdrop-filter:blur(16px);border:1px solid var(--glass-border);border-radius:20px;padding:32px;width:100%;max-width:600px;max-height:85vh;overflow-y:auto;position:relative;">
    <button class="modal-close" onclick="closeModalById('addIssueModal')"><i class="fas fa-times"></i></button>
    <h3><i class="fas fa-plus me-2"></i><?php echo t('addIssue'); ?></h3>
    <div class="mb-3">
      <label class="form-label"><?php echo t('issueDescription'); ?></label>
      <textarea class="form-control" id="issueDesc" rows="4" required></textarea>
    </div>
    <div style="display:flex;gap:8px;justify-content:flex-end;">
      <button type="button" onclick="closeModalById('addIssueModal')" style="background:var(--btn-bg);color:var(--btn-text);border:1px solid var(--btn-border);border-radius:12px;padding:10px 24px;font-weight:600;cursor:pointer;">Cancel</button>
      <button type="button" class="btn btn-neon" onclick="addIssue()"><?php echo t('save'); ?></button>
    </div>
  </div>
</div>

<script>
window.showAddIssueModal = function() {
    document.getElementById('addIssueModal').style.display = 'flex';
};

window.addIssue = function() {
    var desc = document.getElementById('issueDesc').value.trim();
    if (!desc) return alert('Description required');
    fetch('/pfeeeee/PhtP/ajax/api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'addTechnicalIssue', description: desc })
    })
    .then(r => r.json())
    .then(function(data) {
        if (data.success) { location.reload(); } else { alert(data.message || 'Error'); }
    })
    .catch(function() { alert('Error'); });
};

window.resolveIssue = function(id) {
    if (!confirm('Mark this issue as resolved?')) return;
    fetch('/pfeeeee/PhtP/ajax/api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'resolveTechnicalIssue', id: id })
    })
    .then(r => r.json())
    .then(function(data) {
        if (data.success) { location.reload(); } else { alert(data.message || 'Error'); }
    })
    .catch(function() { alert('Error'); });
};

window.deleteIssue = function(id) {
    if (!confirm('Delete this issue?')) return;
    fetch('/pfeeeee/PhtP/ajax/api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'deleteTechnicalIssue', id: id })
    })
    .then(r => r.json())
    .then(function(data) {
        if (data.success) { location.reload(); } else { alert(data.message || 'Error'); }
    })
    .catch(function() { alert('Error'); });
};
</script>

<?php require_once '../includes/footer.php'; ?>

