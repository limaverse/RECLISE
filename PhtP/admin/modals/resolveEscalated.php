<?php
$id = (int)($_GET['id'] ?? 0);
if (!$id) {
  echo '<p class="text-secondary">Invalid request ID</p>';
  exit;
}

// Fetch request details with user info
$stmt = $pdo->prepare("
  SELECT r.*, u.full_name AS requester_name, u.email AS requester_email
  FROM requests r 
  LEFT JOIN users u ON r.user_id = u.id 
  WHERE r.id = ?
");
$stmt->execute([$id]);
$req = to_camel($stmt->fetch());

if (!$req) {
  echo '<p class="text-secondary">Request not found</p>';
  exit;
}
?>
<!-- Complete Resolve Modal - EXACT reference match -->
<div class="modal-overlay" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);z-index:1060;display:flex;align-items:center;justify-content:center;padding:20px;">
  <div class="modal-box" style="background:var(--modal-bg);backdrop-filter:blur(16px);border:1px solid var(--glass-border);border-radius:20px;padding:32px;width:100%;max-width:600px;max-height:85vh;overflow-y:auto;position:relative;">
    <button class="modal-close" onclick="RecLise.closeModal()"><i class="fas fa-times"></i></button>
    
    <h3><i class="fas fa-check-circle me-2"></i>Resolve â€” #<?= $req['id'] ?></h3>
    
    <!-- Request Summary -->
    <div class="glass-card mb-3" style="padding:16px;border-left:4px solid var(--warning);">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <h5 style="margin:0;"><i class="fas fa-info-circle me-2"></i><?= htmlspecialchars($req['title'] ?? '') ?></h5>
        <span class="status-pill status-escalated">Escalated</span>
      </div>
      <div style="font-size:1rem;margin-bottom:8px;"><?= htmlspecialchars($req['title'] ?? '') ?></div>
      <div style="font-size:0.85rem;color:var(--text-secondary);">
        <span>Type: <i class="fas fa-exclamation-triangle me-1"></i><?= htmlspecialchars($req['type'] ?? 'Request') ?></span>
        <span class="mx-2">|</span>
        <span>Priority: <i class="fas fa-flag me-1"></i><?= htmlspecialchars($req['priority'] ?? 'Normal') ?></span>
      </div>
    </div>
    
    <!-- Resolution Form -->
    <div class="glass-card mb-3" style="padding:16px;">
      <h5 style="margin-bottom:14px;"><i class="fas fa-clipboard-check me-2"></i>Final Response</h5>
      <div class="mb-3">
        <label class="form-label">Final Response</label>
        <textarea class="form-control" id="closeResponse" rows="3" placeholder="Write your resolution..."></textarea>
      </div>
    </div>
    
    <button class="btn btn-neon w-100" onclick="RecLise.resolveEscalated(<?= $req['id'] ?>)"><i class="fas fa-check me-2"></i>Resolve</button>
  </div>
</div>
