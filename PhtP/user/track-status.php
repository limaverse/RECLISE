<?php
$currentView = 'track-status';
require_once '../includes/header.php';

// Fetch user's requests
$stmtRequests = $pdo->prepare("SELECT r.*, u.full_name AS requester_name, u.department AS requester_dept
    FROM requests r
    LEFT JOIN users u ON r.user_id = u.id
    WHERE r.user_id = ? AND r.status NOT IN ('resolved', 'closed')
    ORDER BY r.created_at DESC");
$stmtRequests->execute([$_SESSION['user_id']]);
$requests = to_camel_all($stmtRequests->fetchAll());

// Fix: convert created_at to createdAt for consistency  
foreach ($requests as &$req) {
    $req['createdAt'] = $req['createdAt'] ?? $req['created_at'] ?? null;
}

// Fetch attachments for these requests
$reqIds = array_column($requests, 'id');
if (!empty($reqIds)) {
    $placeholders = implode(',', array_fill(0, count($reqIds), '?'));
    $stmtAtt = $pdo->prepare("SELECT request_id, file_name, file_path FROM request_attachments WHERE request_id IN ($placeholders)");
    $stmtAtt->execute($reqIds);
    $atts = $stmtAtt->fetchAll();
    $attsByReq = [];
    foreach ($atts as $a) {
        $attsByReq[$a['request_id']][] = $a;
    }
    foreach ($requests as &$req) {
        $req['attachments'] = $attsByReq[$req['id']] ?? [];
    }
}
?>

<div class="section-header">
  <h2><i class="fas fa-search me-2"></i><?php echo t('trackStatus'); ?></h2>
  <div class="flex-gap-8">
    <select class="form-select" style="width: auto;" id="statusFilter" onchange="RecLise.filterByStatus()">
      <option value=""><?php echo t('allStatuses'); ?></option>
      <option value="new"><?php echo t('statusNew'); ?></option>
      <option value="in_progress"><?php echo t('statusInProgress'); ?></option>
      <option value="escalated"><?php echo t('statusEscalated'); ?></option>
    </select>
  </div>
</div>

<div class="glass-card" style="padding: 20px;">
  <?php if (empty($requests)): ?>
    <p class="text-secondary text-center" style="padding: 40px;"><?php echo t('noResults'); ?></p>
  <?php else: ?>
    <table class="table-glass">
      <thead>
        <tr>
          <th><?php echo t('id'); ?></th>
          <th><?php echo t('title'); ?></th>
          <th><?php echo t('category'); ?></th>
          <th><?php echo t('status'); ?></th>
          <th><?php echo t('date'); ?></th>
          <th><?php echo t('actions'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($requests as $req): ?>
          <tr data-status="<?= $req['status'] ?>">
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
            <td><span class="status-pill status-<?= $req['category'] ?>"><?php echo t($req['category']); ?></span></td>
            <td><span class="status-pill status-<?= $req['status'] ?>"><?php echo t('status' . ucfirst($req['status'])); ?></span></td>
             <td><?= $req['createdAt'] ? date('Y-m-d', strtotime($req['createdAt'])) : 'N/A' ?></td>
            <td>
              <button class="btn btn-outline-neon btn-sm" onclick="RecLise.viewRequestDetail(<?= $req['id'] ?>)">
                <i class="fas fa-eye me-1"></i><?php echo t('view'); ?>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
