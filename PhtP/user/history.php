<?php
$currentView = 'history';
require_once '../includes/header.php';

// Fetch resolved/closed requests for history
$stmtHistory = $pdo->prepare("SELECT r.*, u.full_name AS requester_name
    FROM requests r
    LEFT JOIN users u ON r.user_id = u.id
    WHERE r.user_id = ?
    AND (r.status = 'resolved' OR r.status = 'closed')
    ORDER BY r.updated_at DESC");
$stmtHistory->execute([$_SESSION['user_id']]);
$history = to_camel_all($stmtHistory->fetchAll());

// Fetch attachments for these requests
$histIds = array_column($history, 'id');
if (!empty($histIds)) {
    $placeholders = implode(',', array_fill(0, count($histIds), '?'));
    $stmtAtt = $pdo->prepare("SELECT request_id, file_name, file_path FROM request_attachments WHERE request_id IN ($placeholders)");
    $stmtAtt->execute($histIds);
    $atts = $stmtAtt->fetchAll();
    $attsByReq = [];
    foreach ($atts as $a) {
        $attsByReq[$a['request_id']][] = $a;
    }
    foreach ($history as &$req) {
        $req['attachments'] = $attsByReq[$req['id']] ?? [];
    }
}
?>

<div class="section-header">
  <h2><i class="fas fa-clock-rotate-left me-2"></i><?php echo t('history'); ?></h2>
  <button class="btn btn-outline-neon btn-sm" onclick="RecLise.renderContent('history')">
    <i class="fas fa-rotate-right me-1"></i><?php echo t('refresh'); ?>
  </button>
</div>

<?php if (empty($history)): ?>
  <p class="text-secondary text-center" style="padding:40px;"><?php echo t('noResults'); ?></p>
<?php else: ?>
  <div class="glass-card" style="padding:20px;">
    <table class="table-glass">
      <thead>
        <tr>
          <th><?php echo t('id'); ?></th>
          <th><?php echo t('title'); ?></th>
          <th><?php echo t('status'); ?></th>
          <th><?php echo t('resolvedAt'); ?></th>
          <th><?php echo t('actions'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($history as $req): ?>
          <tr>
            <td>#<?= $req['id'] ?></td>
             <td>
               <?= htmlspecialchars($req['title']) ?>
               <div style="margin-top:6px;">
                 <?php if (!empty($req['attachments'])):
                   foreach ($req['attachments'] as $att): ?>
                     <span class="chip" style="cursor:pointer;margin-right:4px;" onclick="window.open('/pfeeeee/PhtP/<?= htmlspecialchars($att['file_path']) ?>', '_blank')">
                       <i class="fas fa-paperclip me-1"></i><?= htmlspecialchars($att['file_name']) ?>
                     </span>
                   <?php endforeach;
                 else: ?>
                   <span class="chip"><i class="fas fa-paperclip me-1"></i>None</span>
                 <?php endif; ?>
               </div>
             </td>
            <td><span class="status-pill status-<?= $req['status'] ?>"><?php echo t('status' . ucfirst($req['status'])); ?></span></td>
            <td><?= date('Y-m-d', strtotime($req['updatedAt'])) ?></td>
            <td>
              <button class="btn btn-outline-neon btn-sm" onclick="RecLise.viewResolvedRequest(<?= $req['id'] ?>)">
                <i class="fas fa-eye me-1"></i><?php echo t('view'); ?>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
