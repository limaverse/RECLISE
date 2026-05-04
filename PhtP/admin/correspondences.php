<?php
$currentView = 'correspondences';
require_once '../includes/header.php';

// Fetch templates and users for reassigning
$templates = to_camel_all($pdo->query("SELECT * FROM correspondences ORDER BY id")->fetchAll());
$allUsers = to_camel_all($pdo->query("SELECT id, full_name FROM users ORDER BY full_name")->fetchAll());
?>

<div class="section-header">
  <h2><i class="fas fa-file-lines me-2"></i><?php echo t('correspondences'); ?></h2>
  <button class="btn btn-neon btn-sm" onclick="RecLise.showAddCorrespondenceModal()">
    <i class="fas fa-plus me-1"></i><?php echo t('addTemplate'); ?>
  </button>
</div>

<div class="glass-card overflow-auto" style="padding:20px;">
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
            <select class="form-select form-select-sm" id="reassign_<?= $c['id'] ?>" style="min-width:140px; font-size:0.82rem;">
              <option value=""><?php echo t('all'); ?></option>
              <?php foreach ($allUsers as $u): ?>
                <option value="<?= $u['id'] ?>" <?= ($c['assignee'] == $u['id'] ? 'selected' : '') ?>><?= htmlspecialchars($u['fullName']) ?></option>
              <?php endforeach; ?>
            </select>
          </td>
          <td>
            <textarea class="form-control form-control-sm" id="template_<?= $c['id'] ?>" rows="2" style="min-width:180px; font-size:0.82rem;"><?= htmlspecialchars($c['template'] ?? '') ?></textarea>
          </td>
          <td>
            <div style="display:flex; flex-direction:column; gap:4px;">
              <button class="btn btn-sm btn-neon" onclick="RecLise.saveCorrespondenceReassign(<?= $c['id'] ?>)">
                <i class="fas fa-exchange-alt me-1"></i><?php echo t('saveReassignment'); ?>
              </button>
              <button class="btn btn-sm btn-neon" onclick="RecLise.saveCorrespondenceTemplate(<?= $c['id'] ?>)">
                <i class="fas fa-save me-1"></i><?php echo t('saveTemplate'); ?>
              </button>
              <button class="btn btn-sm btn-danger" onclick="RecLise.deleteCorrespondence(<?= $c['id'] ?>)">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once '../includes/footer.php'; ?>
