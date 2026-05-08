<?php
$currentView = 'correspondences';
require_once '../includes/header.php';

// Fetch templates and users for reassigning
$templates = to_camel_all($pdo->query("SELECT * FROM correspondences ORDER BY id")->fetchAll());
$allUsers = to_camel_all($pdo->query("SELECT id, full_name FROM users ORDER BY full_name")->fetchAll());
?>

<div class="section-header">
  <h2><i class="fas fa-file-lines me-2"></i><?php echo t('correspondences'); ?></h2>
  <button class="btn btn-neon btn-sm" onclick="document.getElementById('addCorrespondenceModal').style.display='flex'">
    <i class="fas fa-plus me-1"></i><?php echo t('addTemplate'); ?>
  </button>
</div>

<div class="glass-card overflow-auto">
  <table class="table-glass">
    <thead>
      <tr>
        <th><?php echo t('id'); ?></th>
        <th><?php echo t('templateName'); ?></th>
        <th><?php echo t('templateType'); ?></th>
        <th><?php echo t('reassignTo'); ?></th>
        <th><?php echo t('fileModelTemplate'); ?></th>
        <th><?php echo t('actions'); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($templates as $c): ?>
        <tr>
          <td>#<?= $c['id'] ?></td>
          <td><?= htmlspecialchars($c['name']) ?></td>
          <td><?= htmlspecialchars($c['type']) ?></td>
          <td>
            <select class="form-select form-select-sm" id="reassign_<?= $c['id'] ?>" style="min-width:140px;padding:6px 10px;font-size:0.82rem;">
              <option value=""><?php echo t('all'); ?></option>
              <?php foreach ($allUsers as $u): ?>
                <option value="<?= $u['id'] ?>" <?= (isset($c['assignee']) && $c['assignee'] == $u['id'] ? 'selected' : '') ?>><?= htmlspecialchars($u['fullName']) ?></option>
              <?php endforeach; ?>
            </select>
          </td>
          <td>
            <textarea class="form-control form-control-sm" id="template_<?= $c['id'] ?>" rows="2" style="min-width:180px;padding:6px 10px;font-size:0.82rem;"><?= htmlspecialchars($c['template'] ?? '') ?></textarea>
          </td>
          <td>
              <button class="btn btn-sm btn-neon" style="margin-right:4px;margin-bottom:4px;" onclick="saveCorrespondenceReassign(<?= $c['id'] ?>)">
                <i class="fas fa-exchange-alt me-1"></i><?php echo t('saveReassignment'); ?>
              </button>
              <button class="btn btn-sm btn-neon" style="margin-right:4px;margin-bottom:4px;" onclick="saveCorrespondenceTemplate(<?= $c['id'] ?>)">
                <i class="fas fa-save me-1"></i><?php echo t('saveTemplate'); ?>
              </button>
              <button class="btn btn-sm btn-danger" style="margin-bottom:4px;" onclick="deleteCorrespondence(<?= $c['id'] ?>)">
                <i class="fas fa-trash"></i>
              </button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Add Correspondence Modal -->
<div class="modal-overlay" id="addCorrespondenceModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);z-index:1060;align-items:center;justify-content:center;padding:20px;">
  <div class="modal-box" style="background:var(--modal-bg);backdrop-filter:blur(16px);border:1px solid var(--glass-border);border-radius:20px;padding:32px;width:100%;max-width:600px;max-height:85vh;overflow-y:auto;position:relative;">
    <button class="modal-close" onclick="closeModalById('addCorrespondenceModal')"><i class="fas fa-times"></i></button>
    
    <h3><i class="fas fa-plus me-2"></i><?php echo t('addTemplate'); ?></h3>
    
    <form onsubmit="event.preventDefault();var name=document.getElementById('newCorrespondenceName').value,type=document.getElementById('newCorrespondenceType').value;if(!name){alert('Enter name');return;}fetch('/pfeeeee/PhtP/ajax/api.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action: 'addCorrespondence',name:name,type:type})}).then(r=>r.json()).then(function(r){if(!r.success)alert(r.message);else{alert('Template added!');closeModalById('addCorrespondenceModal');setTimeout(function(){location.reload();},500);}});">
      <div class="mb-3">
        <label class="form-label"><?php echo t('templateName'); ?></label>
        <input type="text" class="form-control" id="newCorrespondenceName" required>
      </div>
      <div class="mb-3">
        <label class="form-label"><?php echo t('templateType'); ?></label>
        <select class="form-select" id="newCorrespondenceType">
          <option value="Interne"><?php echo t('internal'); ?></option>
          <option value="Externe"><?php echo t('external'); ?></option>
        </select>
      </div>
      <div style="display:flex;gap:8px;justify-content:flex-end;">
        <button type="button" onclick="closeModalById('addCorrespondenceModal')" style="background:var(--btn-bg);color:var(--btn-text);border:1px solid var(--btn-border);border-radius:12px;padding:10px 24px;font-weight:600;cursor:pointer;">Cancel</button>
        <button type="submit" class="btn btn-neon"><?php echo t('save'); ?></button>
      </div>
    </form>
  </div>
</div>

<script>
window.saveCorrespondenceReassign = function(id) {
    var user = document.getElementById('reassign_' + id).value;
    fetch('/pfeeeee/PhtP/ajax/api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'saveCorrespondenceReassign', id: id, assignee: user })
    })
    .then(r => r.json())
    .then(function(data) {
        if (data.success) { alert('Reassignment saved!'); } else { alert(data.message || 'Error'); }
    })
    .catch(function() { alert('Error'); });
};

window.saveCorrespondenceTemplate = function(id) {
    var tmpl = document.getElementById('template_' + id).value;
    fetch('/pfeeeee/PhtP/ajax/api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'saveCorrespondenceTemplate', id: id, template: tmpl })
    })
    .then(r => r.json())
    .then(function(data) {
        if (data.success) { alert('Template saved!'); } else { alert(data.message || 'Error'); }
    })
    .catch(function() { alert('Error'); });
};

window.deleteCorrespondence = function(id) {
    if (!confirm('Delete this correspondence?')) return;
    fetch('/pfeeeee/PhtP/ajax/api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'deleteCorrespondence', id: id })
    })
    .then(r => r.json())
    .then(function(data) {
        if (data.success) { location.reload(); } else { alert(data.message || 'Error'); }
    })
    .catch(function() { alert('Error'); });
};
</script>

<?php require_once '../includes/footer.php'; ?>

