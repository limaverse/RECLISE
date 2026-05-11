<?php
$currentView = 'distribution-boxes';
require_once '../includes/header.php';

// Fetch distribution boxes
$boxes = to_camel_all($pdo->query("SELECT * FROM distribution_boxes ORDER BY created_at DESC")->fetchAll());

foreach ($boxes as &$box) {
    $stmt = $pdo->prepare("SELECT * FROM distribution_box_members WHERE box_id = ?");
    $stmt->execute([$box['id']]);
    $box['members'] = to_camel_all($stmt->fetchAll());
}
?>

<div class="section-header">
  <h2><i class="fas fa-box me-2"></i><?php echo t('distributionBoxes'); ?></h2>
  <button class="btn btn-neon btn-sm" onclick="showAddBoxModal()">
    <i class="fas fa-plus me-1"></i><?php echo t('addItem'); ?>
  </button>
</div>

<div class="row g-3">
  <?php if (empty($boxes)): ?>
    <p class="text-secondary text-center padding:40px;"><?php echo t('noResults'); ?></p>
  <?php else: ?>
    <?php foreach ($boxes as $box): ?>
      <div class="col-md-6 col-lg-4">
        <div class="item-card">
          <h5><?= htmlspecialchars($box['name']) ?></h5>
          <p class="text-secondary margin-bottom-12">
            <i class="fas fa-users me-1"></i><?= count($box['members']) ?> <?php echo t('members'); ?>
          </p>
          <div class="mb-3">
            <?php foreach ($box['members'] as $member): ?>
              <span class="chip"><?= htmlspecialchars($member['memberName']) ?></span>
            <?php endforeach; ?>
          </div>
          <button class="btn btn-sm btn-danger" onclick="deleteBox(<?= $box['id'] ?>)">
            <i class="fas fa-trash"></i>
          </button>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- Add Box Modal -->
<div class="modal-overlay" id="addBoxModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);z-index:1060;align-items:center;justify-content:center;padding:20px;">
  <div class="modal-box" style="background:var(--modal-bg);backdrop-filter:blur(16px);border:1px solid var(--glass-border);border-radius:20px;padding:32px;width:100%;max-width:600px;max-height:85vh;overflow-y:auto;position:relative;">
    <button class="modal-close" onclick="closeModalById('addBoxModal')"><i class="fas fa-times"></i></button>
    <h3><i class="fas fa-plus me-2"></i><?php echo t('addItem'); ?> - <?php echo t('distributionBoxes'); ?></h3>
    <div class="mb-3">
      <label class="form-label"><?php echo t('boxName'); ?></label>
      <input type="text" class="form-control" id="boxName" required>
    </div>
    <div class="mb-3">
      <label class="form-label"><?php echo t('members'); ?> (comma separated)</label>
      <input type="text" class="form-control" id="boxMembers" placeholder="member1, member2, ...">
    </div>
    <div style="display:flex;gap:8px;justify-content:flex-end;">
      <button type="button" onclick="closeModalById('addBoxModal')" style="background:var(--btn-bg);color:var(--btn-text);border:1px solid var(--btn-border);border-radius:12px;padding:10px 24px;font-weight:600;cursor:pointer;">Cancel</button>
      <button type="button" class="btn btn-neon" onclick="addBox()"><?php echo t('save'); ?></button>
    </div>
  </div>
</div>

<script>
window.showAddBoxModal = function() {
    document.getElementById('addBoxModal').style.display = 'flex';
};

window.addBox = function() {
    var name = document.getElementById('boxName').value.trim();
    var members = document.getElementById('boxMembers').value.trim();
    if (!name) return alert('Box name required');
    fetch('/pfeeeee/PhtP/ajax/api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'addBox', name: name, members: members })
    })
    .then(r => r.json())
    .then(function(data) {
        if (data.success) { location.reload(); } else { alert(data.message || 'Error'); }
    })
    .catch(function() { alert('Error'); });
};

window.deleteBox = function(id) {
    if (!confirm('Delete this box?')) return;
    fetch('/pfeeeee/PhtP/ajax/api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'deleteBox', id: id })
    })
    .then(r => r.json())
    .then(function(data) {
        if (data.success) { location.reload(); } else { alert(data.message || 'Error'); }
    })
    .catch(function() { alert('Error'); });
};
</script>

<?php require_once '../includes/footer.php'; ?>

