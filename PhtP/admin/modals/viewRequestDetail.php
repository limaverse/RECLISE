<?php
$id = (int)($_GET['id'] ?? 0);
if (!$id) { echo '<p>Invalid request ID</p>'; exit; }

$stmt = $pdo->prepare("SELECT r.*, u.full_name AS requester_name, u.email AS requester_email, u.role AS requester_role FROM requests r LEFT JOIN users u ON r.user_id = u.id WHERE r.id = ?");
$stmt->execute([$id]);
$req = to_camel($stmt->fetch());

if (!$req) { echo '<p>Request not found</p>'; exit; }

$isSupportRequest = ($req['requestFrom'] === 'support');
$roleBadge = $isSupportRequest
    ? '<span class="badge badge-support"><i class="fas fa-headset me-1"></i>Support</span>'
    : '<span class="badge badge-user"><i class="fas fa-user me-1"></i>User</span>';
?>
<div class="modal-overlay" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);z-index:1060;display:flex;align-items:center;justify-content:center;padding:20px;">
  <div class="modal-box modal-lg" style="background:var(--modal-bg);backdrop-filter:blur(16px);border:1px solid var(--glass-border);border-radius:20px;padding:32px;width:100%;max-width:800px;max-height:85vh;overflow-y:auto;position:relative;">
    <button class="modal-close" onclick="RecLise.closeModal()"><i class="fas fa-times"></i></button>

    <h3><i class="fas fa-eye me-2"></i>Request Details — #<?= $req['id'] ?></h3>

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
          <div style="font-weight:600;"><i class="fas fa-exclamation-triangle me-1"></i><?= htmlspecialchars($req['type'] ?? 'Request') ?></div>
        </div>
        <div class="col-4">
          <div style="font-size:0.82rem;color:var(--text-secondary);margin-bottom:4px;">Priority</div>
          <div style="font-weight:600;"><i class="fas fa-flag me-1"></i><?= htmlspecialchars($req['priority'] ?? 'Normal') ?></div>
        </div>
        <div class="col-4">
          <div style="font-size:0.82rem;color:var(--text-secondary);margin-bottom:4px;">Category</div>
          <div style="font-weight:600;"><i class="fas fa-cogs me-1"></i><?= htmlspecialchars($req['category'] ?? 'Other') ?></div>
        </div>
      </div>
      <div class="mb-2"><strong>Title:</strong> <?= htmlspecialchars($req['title'] ?? '') ?></div>
      <div class="mb-2">
        <strong>From:</strong>
        <?php if ($isSupportRequest): ?>
          <span class="badge badge-support"><i class="fas fa-headset me-1"></i>Support</span>
        <?php else: ?>
          <span class="badge badge-user"><i class="fas fa-user me-1"></i>User</span>
        <?php endif; ?>
        <?php if (!empty($req['requesterName'])): ?>
          <?= htmlspecialchars($req['requesterName']) ?>
        <?php endif; ?>
      </div>
      <div class="mb-2"><strong>Assigned To:</strong> Admin</div>
      <div><strong>Created At:</strong> <?= date('M j, Y, h:i A', strtotime($req['createdAt'])) ?></div>
    </div>

    <div class="mb-3">
      <strong>Description:</strong>
      <p style="margin-top:6px;color:var(--text-secondary);padding:12px;background:rgba(0,0,0,0.1);border-radius:8px;"><?= nl2br(htmlspecialchars($req['description'] ?? 'No description')) ?></p>
    </div>

    <div class="mb-3">
      <strong>Attachments:</strong>
      <div style="margin-top:6px;">
        <span class="chip"><i class="fas fa-paperclip me-1"></i>None</span>
      </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
      <button class="btn btn-outline-neon" onclick="RecLise.closeModal()">Close</button>
    </div>
  </div>
</div>
      <div class="row text-center mb-3">
        <div class="col-4">
          <div style="font-size:0.82rem;color:var(--text-secondary);margin-bottom:4px;">Type</div>
          <div style="font-weight:600;"><i class="fas fa-exclamation-triangle me-1"></i><?= htmlspecialchars($req['type'] ?? 'Request') ?></div>
        </div>
        <div class="col-4">
          <div style="font-size:0.82rem;color:var(--text-secondary);margin-bottom:4px;">Priority</div>
          <div style="font-weight:600;"><i class="fas fa-flag me-1"></i><?= htmlspecialchars($req['priority'] ?? 'Normal') ?></div>
        </div>
        <div class="col-4">
          <div style="font-size:0.82rem;color:var(--text-secondary);margin-bottom:4px;">Category</div>
          <div style="font-weight:600;"><i class="fas fa-cogs me-1"></i><?= htmlspecialchars($req['category'] ?? 'Other') ?></div>
        </div>
      </div>
      <div class="mb-2"><strong>Title:</strong> <?= htmlspecialchars($req['title'] ?? '') ?></div>
      <div class="mb-2"><strong>From:</strong> <?= htmlspecialchars($req['requesterName'] ?? 'N/A') ?> (<?= htmlspecialchars($req['requesterEmail'] ?? '') ?>)</div>
      <div class="mb-2"><strong>Assigned To:</strong> Admin</div>
      <div><strong>Created At:</strong> <?= date('M j, Y, h:i A', strtotime($req['createdAt'])) ?></div>
    </div>
    
    <div class="mb-3">
      <strong>Description:</strong>
      <p style="margin-top:6px;color:var(--text-secondary);padding:12px;background:rgba(0,0,0,0.1);border-radius:8px;"><?= nl2br(htmlspecialchars($req['description'] ?? 'No description')) ?></p>
    </div>
    
    <div class="mb-3">
      <strong>Attachments:</strong>
      <div style="margin-top:6px;">
        <span class="chip"><i class="fas fa-paperclip me-1"></i>None</span>
      </div>
    </div>
    
    <div class="d-flex justify-content-end gap-2">
      <button class="btn btn-outline-neon" onclick="RecLise.closeModal()">Close</button>
    </div>
  </div>
</div>
