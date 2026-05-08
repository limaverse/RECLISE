<?php
$currentView = 'escalated-requests';
require_once '../includes/header.php';

$stmt = $pdo->query("SELECT r.*, u.full_name AS requester_name, u.email AS requester_email, u.role AS requester_role
    FROM requests r
    LEFT JOIN users u ON r.user_id = u.id
    WHERE r.escalated_to_admin = 1
    ORDER BY r.updated_at DESC");
$requests = to_camel_all($stmt->fetchAll());

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
    foreach ($requests as &$r) {
        $r['attachments'] = $attsByReq[$r['id']] ?? [];
    }
}
?>

<div class="section-header">
  <h2><i class="fas fa-arrow-up-right-dots me-2"></i><?php echo t('escalatedRequests'); ?></h2>
</div>

<div class="glass-card" style="padding:20px; overflow-x:auto;">
  <?php if (empty($requests)): ?>
    <p class="text-secondary text-center padding:40px;"><?php echo t('noResults'); ?></p>
  <?php else: ?>
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
        <?php foreach ($requests as $r):
          $isSupportRequest = ($r['requestFrom'] === 'support');
        ?>
          <tr>
            <td>#<?= $r['id'] ?></td>
             <td>
               <?= htmlspecialchars($r['title']) ?>
                <div style="margin-top:6px;">
                  <?php if (!empty($r['attachments'])):
                    foreach ($r['attachments'] as $att): ?>
                      <span class="chip" style="cursor:pointer;margin-right:4px;" onclick="window.open('/pfeeeee/PhtP/<?= htmlspecialchars($att['file_path']) ?>', '_blank')">
                        <i class="fas fa-paperclip me-1"></i><?= htmlspecialchars($att['file_name']) ?>
                      </span>
                    <?php endforeach; endif; ?>
                </div>
             </td>
            <td><?= t($r['type'] === 'complaint' ? 'complaint' : 'request') ?></td>
            <td><span class="priority-<?= $r['priority'] ?>"><?php echo t($r['priority']); ?></span></td>
            <td>
              <?php if ($isSupportRequest): ?>
                <span class="status-pill status-support-request">
                  <i class="fas fa-headset me-1"></i><?php echo t('fromSupport'); ?>
                </span>
              <?php else: ?>
                <span class="status-pill status-escalated"><?php echo t('statusEscalated'); ?></span>
              <?php endif; ?>
            </td>
            <td><?= date('Y-m-d', strtotime($r['updatedAt'])) ?></td>
            <td>
              <button class="btn btn-outline-neon btn-sm" onclick="document.getElementById('viewRequestModal<?= $r['id'] ?>').style.display='flex'">View</button>
              <?php if ($r['status'] !== 'resolved' && $r['status'] !== 'closed'): ?>
                <button class="btn btn-neon btn-sm" style="margin-left:4px;" onclick="document.getElementById('resolveEscalatedModal<?= $r['id'] ?>').style.display='flex'">Resolve</button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php foreach ($requests as $r):
  $isSupportRequest = ($r['requestFrom'] === 'support');
?>
<!-- View Request Modal for #<?= $r['id'] ?> -->
<div class="modal-overlay" id="viewRequestModal<?= $r['id'] ?>" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);z-index:1060;align-items:center;justify-content:center;padding:20px;">
  <div class="modal-box modal-lg" style="background:var(--modal-bg);backdrop-filter:blur(16px);border:1px solid var(--glass-border);border-radius:20px;padding:32px;width:100%;max-width:800px;max-height:85vh;overflow-y:auto;position:relative;margin:auto;">
   <button class="modal-close" onclick="closeModalById('viewRequestModal<?= $r['id'] ?>')"><i class="fas fa-times"></i></button>

    <h3><i class="fas fa-eye me-2"></i>Request Details — #<?= $r['id'] ?></h3>

    <!-- Request Overview Card -->
    <div class="glass-card mb-3" style="padding:16px;border-left:4px solid <?= $isSupportRequest ? 'var(--neon-accent)' : 'var(--warning)' ?>;">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 style="margin:0;"><i class="fas fa-info-circle me-2"></i>Request Details</h5>
        <?php if ($isSupportRequest): ?>
          <span class="status-pill status-support-request"><i class="fas fa-headset me-1"></i><?php echo t('fromSupport'); ?></span>
        <?php else: ?>
          <span class="status-pill status-escalated"><?php echo t('statusEscalated'); ?></span>
        <?php endif; ?>
      </div>
      <div class="row text-center mb-3">
        <div class="col-4">
          <div style="font-size:0.82rem;color:var(--text-secondary);margin-bottom:4px;">Type</div>
          <div style="font-weight:600;"><i class="fas fa-exclamation-triangle me-1"></i><?= htmlspecialchars($r['type'] ?? 'Request') ?></div>
        </div>
        <div class="col-4">
          <div style="font-size:0.82rem;color:var(--text-secondary);margin-bottom:4px;">Priority</div>
          <div style="font-weight:600;"><i class="fas fa-flag me-1"></i><?= htmlspecialchars($r['priority'] ?? 'Normal') ?></div>
        </div>
        <div class="col-4">
          <div style="font-size:0.82rem;color:var(--text-secondary);margin-bottom:4px;">Category</div>
          <div style="font-weight:600;"><i class="fas fa-cogs me-1"></i><?= htmlspecialchars($r['category'] ?? 'Other') ?></div>
        </div>
      </div>
      <div class="mb-2"><strong>Title:</strong> <?= htmlspecialchars($r['title']) ?></div>
      <div class="mb-2">
        <strong>From:</strong>
        <?php if ($isSupportRequest): ?>
          <span class="badge badge-support"><i class="fas fa-headset me-1"></i>Support</span>
          <?php if (!empty($r['requester_name'])): ?>
            <?= htmlspecialchars($r['requester_name']) ?>
          <?php endif; ?>
        <?php else: ?>
          <span class="badge badge-user"><i class="fas fa-user me-1"></i>User</span>
          <?php if (!empty($r['requester_name'])): ?>
            <?= htmlspecialchars($r['requester_name']) ?>
          <?php endif; ?>
        <?php endif; ?>
      </div>
      <div class="mb-2"><strong>Assigned To:</strong> Admin</div>
      <div><strong>Created At:</strong> <?= date('M j, Y, h:i A', strtotime($r['createdAt'])) ?></div>
    </div>

    <div class="mb-3">
      <strong>Description:</strong>
      <p style="margin-top:6px;color:var(--text-secondary);padding:12px;background:rgba(0,0,0,0.1);border-radius:8px;"><?= nl2br(htmlspecialchars($r['description'] ?? 'No description')) ?></p>
    </div>

     <div class="mb-3"><strong>Attachments:</strong>
       <div style="margin-top:6px;">
         <?php if (!empty($r['attachments'])):
            foreach ($r['attachments'] as $att): ?>
              <span class="chip" style="cursor:pointer;margin-right:4px;" onclick="window.open('/pfeeeee/<?= htmlspecialchars($att['file_path']) ?>', '_blank')">
                <i class="fas fa-paperclip me-1"></i><?= htmlspecialchars($att['file_name']) ?>
              </span>
            <?php endforeach; endif; ?>
        </div>
     </div>

    <div class="d-flex justify-content-end gap-2">
      <button class="btn btn-outline-neon" onclick="closeModalById('viewRequestModal<?= $r['id'] ?>')">Close</button>
    </div>
  </div>
</div>

<!-- Resolve Escalated Modal for #<?= $r['id'] ?> -->
<div class="modal-overlay" id="resolveEscalatedModal<?= $r['id'] ?>" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);z-index:1060;align-items:center;justify-content:center;padding:20px;">
  <div class="modal-box" style="background:var(--modal-bg);backdrop-filter:blur(16px);border:1px solid var(--glass-border);border-radius:20px;padding:32px;width:100%;max-width:600px;max-height:85vh;overflow-y:auto;position:relative;margin:auto;">
   <button class="modal-close" onclick="closeModalById('resolveEscalatedModal<?= $r['id'] ?>')"><i class="fas fa-times"></i></button>

    <h3><i class="fas fa-check-circle me-2"></i>Resolve — #<?= $r['id'] ?></h3>

    <!-- Request Summary -->
    <div class="glass-card mb-3" style="padding:16px;border-left:4px solid <?= $isSupportRequest ? 'var(--neon-accent)' : 'var(--warning)' ?>;">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <h5 style="margin:0;"><i class="fas fa-info-circle me-2"></i><?= htmlspecialchars($r['title']) ?></h5>
        <?php if ($isSupportRequest): ?>
          <span class="status-pill status-support-request"><?php echo t('fromSupport'); ?></span>
        <?php else: ?>
          <span class="status-pill status-escalated">Escalated</span>
        <?php endif; ?>
      </div>
      <div style="font-size:1rem;margin-bottom:8px;"><?= htmlspecialchars($r['title']) ?></div>
      <div style="font-size:0.85rem;color:var(--text-secondary);">
        <span>Type: <i class="fas fa-exclamation-triangle me-1"></i><?= htmlspecialchars($r['type'] ?? 'Request') ?></span>
        <span class="mx-2">|</span>
        <span>Priority: <i class="fas fa-flag me-1"></i><?= htmlspecialchars($r['priority'] ?? 'Normal') ?></span>
        <span class="mx-2">|</span>
        <span>From: <?php if ($isSupportRequest): ?><span class="badge badge-support"><i class="fas fa-headset me-1"></i>Support</span><?php else: ?><span class="badge badge-user"><i class="fas fa-user me-1"></i>User</span><?php endif; ?> <?php if (!empty($r['requester_name'])): ?><?= htmlspecialchars($r['requester_name']) ?><?php endif; ?></span>
      </div>
    </div>

    <!-- Resolution Form -->
    <div class="glass-card mb-3" style="padding:16px;">
      <h5 style="margin-bottom:14px;"><i class="fas fa-clipboard-check me-2"></i>Final Response</h5>
        <form onsubmit="event.preventDefault();var msg=document.getElementById('closeResponse<?= $r['id'] ?>').value;if(!msg){alert('Enter response');return;}fetch('/pfeeeee/PhtP/ajax/api.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action: 'resolveTechnicalIssue',id:<?= $r['id'] ?>,message:msg})}).then(r=>r.json()).then(function(r){if(!r.success)alert(r.message);else{alert('Resolved!');closeModalById('resolveEscalatedModal<?= $r['id'] ?>');setTimeout(function(){location.reload();},500);}});">
        <div class="mb-3">
          <label class="form-label">Final Response</label>
          <textarea class="form-control" id="closeResponse<?= $r['id'] ?>" rows="3" placeholder="Write your resolution..." required></textarea>
        </div>
        <button type="submit" class="btn btn-neon w-100"><i class="fas fa-check me-2"></i>Resolve</button>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>

<?php require_once '../includes/footer.php'; ?>

