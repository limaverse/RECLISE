<?php
$currentView = 'messages';
require_once '../includes/header.php';

// Fetch user's requests with messages
$requests = to_camel_all($pdo->query("SELECT r.*, u.full_name AS requester_name 
    FROM requests r 
    LEFT JOIN users u ON r.user_id = u.id 
    WHERE r.user_id = {$_SESSION['user_id']} 
    ORDER BY r.updated_at DESC")->fetchAll());

foreach ($requests as &$req) {
    $stmt = $pdo->prepare("SELECT * FROM messages WHERE request_id = ? ORDER BY created_at ASC");
    $stmt->execute([$req['id']]);
    $req['messages'] = to_camel_all($stmt->fetchAll());
}
?>

<div class="section-header">
  <h2><i class="fas fa-comments me-2"></i><?php echo t('messages'); ?></h2>
</div>

<?php if (empty($requests)): ?>
  <p class="text-secondary text-center padding:40px;"><?php echo t('noMessages'); ?></p>
<?php else: ?>
  <?php foreach ($requests as $req): ?>
    <div class="glass-card mb-3" id="reqMsg-<?= $req['id'] ?>" style="border-left: 3px solid <?php 
        $colors = ['new'=>'#F59E0B','in_progress'=>'#3B82F6','resolved'=>'#10B981','escalated'=>'#EF4444','closed'=>'#94A3B8'];
        echo $colors[$req['status']] ?? '#94A3B8';
    ?>;">
      <div class="flex-space-between align-items-center mb-3">
        <h5 style="margin:0;"><i class="fas fa-comment me-2"></i>#<?= $req['id'] ?> - <?= htmlspecialchars($req['title']) ?></h5>
        <div class="flex-gap-8">
          <span class="status-pill status-<?= $req['status'] ?>"><?php echo t('status' . ucfirst($req['status'])); ?></span>
          <?php if ($req['status'] !== 'resolved' && $req['status'] !== 'closed'): ?>
            <button class="btn btn-sm btn-outline-neon" onclick="RecLise.scrollToReply(<?= $req['id'] ?>)">
              <i class="fas fa-reply me-1"></i><?php echo t('reply'); ?>
            </button>
          <?php endif; ?>
        </div>
      </div>
      
      <!-- Timeline -->
      <div class="timeline">
        <?php if (empty($req['messages'])): ?>
          <p class="text-secondary"><?php echo t('noMessages'); ?></p>
        <?php else: ?>
          <?php foreach ($req['messages'] as $msg): ?>
            <div class="timeline-item">
              <div class="timeline-marker <?= $msg['sender_type'] ?>">
                <i class="fas fa-<?php echo $msg['sender_type'] === 'user' ? 'user' : ($msg['sender_type'] === 'system' ? 'cog' : 'headset'); ?>"></i>
              </div>
              <div class="timeline-content">
                <div class="timeline-header">
                  <span class="timeline-author <?= $msg['sender_type'] ?>">
                    <i class="fas fa-<?php echo $msg['sender_type'] === 'user' ? 'user' : ($msg['sender_type'] === 'system' ? 'cog' : 'headset'); ?> me-1"></i>
                    <?php 
                      if ($msg['sender_type'] === 'system') echo t('system');
                      else if ($msg['sender_type'] === 'user') {
                          $sender = $pdo->query("SELECT full_name FROM users WHERE id = {$msg['sender_id']}")->fetchColumn();
                          echo htmlspecialchars($sender ?? t('user'));
                      } else {
                          echo t('support');
                      }
                    ?>
                  </span>
                  <span class="timeline-date"><?= date('Y-m-d H:i', strtotime($msg['created_at'])) ?></span>
                </div>
                <div class="timeline-message"><?= htmlspecialchars($msg['body']) ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>

  <!-- Send Message Section -->
  <div class="glass-card mt-4" style="padding:16px;border-left:4px solid var(--info);">
    <h5 style="margin-bottom:14px;"><i class="fas fa-paper-plane me-2"></i><?php echo t('sendMessage') ?: 'Send a Message'; ?></h5>
    <div class="mb-3">
      <label class="form-label"><?php echo t('request'); ?></label>
      <select class="form-select" id="messageRequestSelect">
        <option value=""><?php echo t('selectRequest') ?: 'Select a request...'; ?></option>
        <?php foreach ($requests as $r): ?>
          <option value="<?= $r['id'] ?>">#<?= $r['id'] ?> - <?= htmlspecialchars($r['title']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label"><?php echo t('yourReply'); ?></label>
      <textarea class="form-control" id="messageText" rows="3" placeholder="<?php echo t('writeReplyPlaceholder'); ?>"></textarea>
    </div>
    <button class="btn btn-neon w-100" onclick="RecLise.sendUserMessage()">
      <i class="fas fa-paper-plane me-2"></i><?php echo t('sendMessage') ?: 'Send Message'; ?>
    </button>
  </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
