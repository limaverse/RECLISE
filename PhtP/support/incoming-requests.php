<?php
$currentView = 'incoming-requests';
require_once '../includes/header.php';

// Escalated requests assigned to support
$stmtEsc = $pdo->query("SELECT r.*, u.full_name AS requesterName
    FROM requests r
    LEFT JOIN users u ON r.user_id = u.id
    WHERE r.escalated_to_admin = 1 AND r.status NOT IN ('resolved','closed')
     ORDER BY r.created_at DESC");
$escalatedReqs = to_camel_all($stmtEsc->fetchAll());

// Fix: convert created_at to createdAt for consistency
foreach ($escalatedReqs as &$req) {
    $req['createdAt'] = $req['createdAt'] ?? $req['created_at'] ?? null;
}

// Pending requests (not resolved/closed, not escalated)
$stmtPend = $pdo->query("SELECT r.*, u.full_name AS requesterName
    FROM requests r
    LEFT JOIN users u ON r.user_id = u.id
    WHERE r.status NOT IN ('resolved','closed') AND (r.escalated_to_admin IS NULL OR r.escalated_to_admin = 0)
     ORDER BY r.created_at DESC");
$pendingReqs = to_camel_all($stmtPend->fetchAll());

// Fix: convert created_at to createdAt for consistency
foreach ($pendingReqs as &$req) {
    $req['createdAt'] = $req['createdAt'] ?? $req['created_at'] ?? null;
}

// Fetch attachments for all requests
$allReqs = array_merge($escalatedReqs, $pendingReqs);
$allIds = array_column($allReqs, 'id');
if (!empty($allIds)) {
    $placeholders = implode(',', array_fill(0, count($allIds), '?'));
    $stmtAtt = $pdo->prepare("SELECT request_id, file_name, file_path FROM request_attachments WHERE request_id IN ($placeholders)");
    $stmtAtt->execute($allIds);
    $atts = $stmtAtt->fetchAll();
    $attsByReq = [];
    foreach ($atts as $a) {
        $attsByReq[$a['request_id']][] = $a;
    }
    // Map attachments back to each request array
    foreach ($escalatedReqs as &$req) {
        $req['attachments'] = $attsByReq[$req['id']] ?? [];
    }
    foreach ($pendingReqs as &$req) {
        $req['attachments'] = $attsByReq[$req['id']] ?? [];
    }
}

function statusLabel($status) {
    $map = ['new' => 'statusNew', 'in_progress' => 'statusInProgress', 'resolved' => 'statusResolved', 'escalated' => 'statusEscalated', 'closed' => 'statusClosed'];
    return t($map[$status] ?? 'statusNew');
}
?>

<div class="section-header">
  <h2><i class="fas fa-inbox me-2"></i><?php echo t('incomingRequests'); ?></h2>
</div>

<?php if (!empty($escalatedReqs)): ?>
  <div class="glass-card mb-3" style="overflow-x:auto;">
    <h5 style="color:var(--warning);margin-bottom:16px;"><i class="fas fa-arrow-up me-2"></i><?php echo t('escalatedRequests'); ?></h5>
    <table class="table-glass">
      <thead>
        <tr>
          <th><?php echo t('id'); ?></th>
          <th><?php echo t('title'); ?></th>
          <th><?php echo t('type'); ?></th>
          <th><?php echo t('priority'); ?></th>
          <th><?php echo t('status'); ?></th>
          <th><?php echo t('date'); ?></th>
          <th><?php echo t('actions'); ?></th>
        </tr>
      </thead>
      <tbody>
         <?php foreach ($escalatedReqs as $req): ?>
          <tr>
            <td>#<?= $req['id'] ?></td>
            <td><?= htmlspecialchars($req['title']) ?></td>
            <td><?= $req['type'] === 'complaint' ? t('complaint') : t('request') ?></td>
            <td><span class="priority-<?= $req['priority'] ?>"><?php echo t($req['priority']); ?></span></td>
            <td><span class="status-pill status-escalated"><?php echo t('escalated'); ?></span></td>
            <td><?= $req['createdAt'] ? date('M d, H:i', strtotime($req['createdAt'])) : 'N/A' ?></td>
             <td>
              <button class="btn btn-outline-neon btn-sm" onclick="RecLise.showReplyModal(<?= $req['id'] ?>)"><?php echo t('view'); ?></button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<div class="glass-card" style="overflow-x:auto;">
    <table class="table-glass">
      <thead>
        <tr>
          <th><?php echo t('id'); ?></th>
          <th><?php echo t('title'); ?></th>
          <th><?php echo t('type'); ?></th>
          <th><?php echo t('priority'); ?></th>
          <th><?php echo t('status'); ?></th>
          <th><?php echo t('date'); ?></th>
          <th><?php echo t('actions'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($pendingReqs)): ?>
          <tr><td colspan="7" class="text-center" style="padding:30px;color:var(--text-secondary)"><?php echo t('noResults'); ?></td></tr>
        <?php else: ?>
          <?php foreach ($pendingReqs as $req): ?>
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
                    <?php endforeach; endif; ?>
                </div>
             </td>
              <td><?= $req['type'] === 'complaint' ? t('complaint') : t('request') ?></td>
              <td><span class="priority-<?= $req['priority'] ?>"><?php echo t($req['priority']); ?></span></td>
              <td><span class="status-pill status-<?= str_replace('_', '-', $req['status']) ?>"><?php echo statusLabel($req['status']); ?></span></td>
              <td><?= $req['createdAt'] ? date('M d, H:i', strtotime($req['createdAt'])) : 'N/A' ?></td>
            <td>
              <button class="btn btn-neon btn-sm" onclick="RecLise.showReplyModal(<?= $req['id'] ?>)"><i class="fas fa-reply me-1"></i><?php echo t('reply'); ?></button>
            </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

<?php require_once '../includes/footer.php'; ?>
