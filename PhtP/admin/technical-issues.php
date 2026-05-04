<?php
$currentView = 'technical-issues';
require_once '../includes/header.php';

// Fetch technical issues
$issues = to_camel_all($pdo->query("SELECT * FROM technical_issues ORDER BY created_at DESC")->fetchAll());
?>

<div class="section-header">
  <h2><i class="fas fa-bug me-2"></i><?php echo t('technicalProblems'); ?></h2>
  <button class="btn btn-neon btn-sm" onclick="RecLise.showAddIssueModal()">
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
            <td><?= date('Y-m-d', strtotime($issue['created_at'])) ?></td>
            <td>
              <?php if ($issue['status'] === 'open'): ?>
                <button class="btn btn-sm btn-outline-neon" onclick="RecLise.resolveIssue(<?= $issue['id'] ?>)">
                  <i class="fas fa-check"></i> <?php echo t('resolve'); ?>
                </button>
              <?php endif; ?>
              <button class="btn btn-sm btn-danger" onclick="RecLise.deleteIssue(<?= $issue['id'] ?>)">
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
