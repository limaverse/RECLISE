<?php
$currentView = 'correspondences';
require_once '../includes/header.php';

// Fetch correspondences
$correspondences = to_camel_all($pdo->query("SELECT * FROM correspondences ORDER BY created_at DESC")->fetchAll());
?>

<div class="section-header">
  <h2><i class="fas fa-envelope me-2"></i><?php echo t('correspondences'); ?></h2>
  <button class="btn btn-neon btn-sm" onclick="RecLise.showAddCorrespondenceModal()">
    <i class="fas fa-plus me-1"></i><?php echo t('addTemplate'); ?>
  </button>
</div>

<div class="glass-card" style="padding:20px;">
  <?php if (empty($correspondences)): ?>
    <p class="text-secondary text-center padding:40px;"><?php echo t('noResults'); ?></p>
  <?php else: ?>
    <table class="table-glass">
      <thead>
        <tr>
          <th><?php echo t('id'); ?></th>
          <th><?php echo t('templateName'); ?></th>
          <th><?php echo t('templateType'); ?></th>
          <th><?php echo t('actions'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($correspondences as $c): ?>
          <tr>
            <td><?= $c['id'] ?></td>
            <td><?= htmlspecialchars($c['name']) ?></td>
            <td><span class="chip"><?= htmlspecialchars($c['type']) ?></span></td>
            <td>
              <button class="btn btn-sm btn-neon" style="margin-right:4px;margin-bottom:4px;" onclick="RecLise.saveCorrespondenceReassign(<?= $c['id'] ?>)">
                <i class="fas fa-exchange-alt"></i> <?php echo t('saveReassignment'); ?>
              </button>
              <button class="btn btn-sm btn-neon" style="margin-right:4px;margin-bottom:4px;" onclick="RecLise.saveCorrespondenceTemplate(<?= $c['id'] ?>)">
                <i class="fas fa-save"></i> <?php echo t('saveTemplate'); ?>
              </button>
              <button class="btn btn-sm btn-danger" onclick="RecLise.deleteCorrespondence(<?= $c['id'] ?>)">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
