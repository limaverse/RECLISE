<?php
$currentView = 'messages';
require_once '../includes/header.php';

// Fetch user's requests
$stmtRequests = $pdo->prepare("SELECT r.*, u.full_name AS requester_name
    FROM requests r
    LEFT JOIN users u ON r.user_id = u.id
    WHERE r.user_id = ? AND r.status != 'resolved' AND r.status != 'closed'
    ORDER BY r.updated_at DESC");
$stmtRequests->execute([$_SESSION['user_id']]);
$requests = to_camel_all($stmtRequests->fetchAll());

// Get support agent names
$stmtUsers = $pdo->query("SELECT id, full_name, role FROM users");
$userMap = [];
while ($u = $stmtUsers->fetch()) {
    $userMap[$u['id']] = $u;
}

// Fetch messages per request
$messageThreads = [];
foreach ($requests as $req) {
    $stmt = $pdo->prepare("SELECT m.*, su.full_name AS senderName, su.role AS senderRole
        FROM messages m
        LEFT JOIN users su ON m.sender_id = su.id
        WHERE m.request_id = ?
        ORDER BY m.created_at ASC");
    $stmt->execute([$req['id']]);
    $messageThreads[$req['id']] = to_camel_all($stmt->fetchAll());
}

// Build thread summaries
$threads = [];
foreach ($requests as $req) {
    $reqId = $req['id'];
    $msgs = $messageThreads[$reqId] ?? [];
    $lastMsg = end($msgs) ?: null;
    // Count unread (messages not from this user)
    $unread = 0;
    foreach ($msgs as $m) {
        if (($m['senderId'] ?? 0) != $_SESSION['user_id']) $unread++;
    }
    $threads[] = [
        'req' => $req,
        'lastMsg' => $lastMsg,
        'msgCount' => count($msgs),
        'unread' => $unread,
    ];
}

// Sort by latest activity
usort($threads, function ($a, $b) {
    $aTime = $a['lastMsg'] ? strtotime($a['lastMsg']['createdAt'] ?? 0) : strtotime($a['req']['updatedAt'] ?? 0);
    $bTime = $b['lastMsg'] ? strtotime($b['lastMsg']['createdAt'] ?? 0) : strtotime($b['req']['updatedAt'] ?? 0);
    return $bTime - $aTime;
});

function statusLabel($status) {
    $map = ['new' => 'statusNew', 'in_progress' => 'statusInProgress', 'resolved' => 'statusResolved', 'escalated' => 'statusEscalated', 'closed' => 'statusClosed'];
    return t($map[$status] ?? 'statusNew');
}
?>

<style>
.msg-layout { display: flex; gap: 16px; height: calc(100vh - var(--topbar-height) - 120px); min-height: 500px; }
.msg-sidebar { width: 340px; flex-shrink: 0; overflow-y: auto; border-right: 1px solid var(--glass-border); padding-right: 12px; }
.msg-main { flex: 1; display: flex; flex-direction: column; }
.msg-thread-item {
    padding: 12px 14px;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
    margin-bottom: 6px;
    border-left: 3px solid transparent;
}
.msg-thread-item:hover { background: var(--focus-glow); }
.msg-thread-item.active { background: var(--focus-glow); border-left-color: var(--neon-accent); }
.msg-thread-item .thread-title { font-weight: 600; font-size: 0.9rem; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.msg-thread-item .thread-preview { font-size: 0.8rem; color: var(--text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.msg-thread-item .thread-meta { display: flex; justify-content: space-between; align-items: center; margin-top: 4px; }
.msg-thread-item .thread-time { font-size: 0.72rem; color: var(--text-secondary); }
.msg-thread-item .thread-badge { font-size: 0.7rem; background: var(--neon-accent); color: var(--primary-dark); border-radius: 10px; padding: 1px 7px; font-weight: 700; }
.msg-body { flex: 1; overflow-y: auto; padding: 16px; }
.msg-input-area { padding: 16px; border-top: 1px solid var(--glass-border); }
.msg-input-area textarea { resize: none; }
</style>

<div class="section-header">
  <h2><i class="fas fa-comments me-2"></i><?php echo t('messages'); ?></h2>
</div>

<?php if (empty($threads)): ?>
  <div class="glass-card">
    <div class="empty-state">
      <i class="fas fa-envelope-open"></i>
      <p><?php echo t('noMessages'); ?></p>
    </div>
  </div>
<?php else: ?>
  <div class="glass-card" style="padding: 0; overflow: hidden;">
    <div class="msg-layout">
      <!-- Sidebar: conversation list -->
      <div class="msg-sidebar" id="msgSidebar">
        <?php foreach ($threads as $i => $thread): 
            $req = $thread['req'];
            $reqId = $req['id'];
            $isActive = ($i === 0) ? 'active' : '';
            $preview = $thread['lastMsg'] ? htmlspecialchars(mb_substr($thread['lastMsg']['body'] ?? '', 0, 60)) : t('noMessages');
            $time = $thread['lastMsg'] ? date('H:i', strtotime($thread['lastMsg']['createdAt'])) : date('M d', strtotime($req['updatedAt']));
        ?>
          <div class="msg-thread-item <?= $isActive ?>" data-req-id="<?= $reqId ?>" onclick="selectThread(<?= $reqId ?>)">
            <div class="thread-title">
              <i class="fas <?= $req['status'] === 'new' ? 'fa-circle text-warning' : ($req['status'] === 'in_progress' ? 'fa-circle text-info' : 'fa-check-circle text-success') ?> me-1" style="font-size:0.65rem;"></i>
              #<?= $reqId ?> — <?= htmlspecialchars($req['title'] ?? '') ?>
            </div>
            <div class="thread-preview"><?= $preview ?></div>
            <div class="thread-meta">
              <span class="status-pill <?= 'status-' . str_replace('_', '-', $req['status'] ?? 'new') ?>" style="font-size:0.65rem; padding:2px 8px;"><?php echo statusLabel($req['status'] ?? 'new'); ?></span>
              <span class="thread-time"><?= $time ?></span>
              <?php if ($thread['unread'] > 0): ?>
                <!-- badge removed -->
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Main: active conversation -->
      <div class="msg-main" id="msgMain">
        <?php
        $activeReq = $threads[0]['req'];
        $activeId = $activeReq['id'];
        $activeMsgs = $messageThreads[$activeId] ?? [];
        ?>
        <div id="msgHeader" class="d-flex align-items-center justify-content-between" style="padding:12px 16px;border-bottom:1px solid var(--glass-border);">
          <div>
            <strong>#<?= $activeId ?> — <?= htmlspecialchars($activeReq['title'] ?? '') ?></strong>
          </div>
          <span class="status-pill <?= 'status-' . str_replace('_', '-', $activeReq['status'] ?? 'new') ?>"><?php echo statusLabel($activeReq['status'] ?? 'new'); ?></span>
        </div>

        <div class="msg-body" id="msgBody">
          <?php if (empty($activeMsgs)): ?>
            <div class="empty-state">
              <i class="fas fa-comment-slash"></i>
              <p><?php echo t('noMessages'); ?></p>
            </div>
          <?php else: ?>
            <div class="timeline" id="msgTimeline">
              <?php foreach ($activeMsgs as $msg): 
                $sid = $msg['senderId'] ?? 0;
                $sender = $userMap[$sid] ?? null;
                $sType = $sender ? ($sender['role'] === 'user' ? 'user' : 'support') : 'system';
                $sName = $sender ? htmlspecialchars($sender['full_name']) : t('system');
              ?>
                <div class="timeline-item <?= $sType ?>" style="margin-bottom:16px;">
                  <div class="timeline-marker <?= $sType ?>">
                    <i class="fas fa-<?php echo $sType === 'user' ? 'user' : ($sType === 'system' ? 'cog' : 'headset'); ?>"></i>
                  </div>
                  <div class="timeline-content">
                    <div class="timeline-header">
                      <span class="timeline-author <?= $sType ?>"><?= $sName ?></span>
                      <span class="timeline-date">
                        <i class="fas fa-clock me-1"></i>
                        <?= date('M d, H:i', strtotime($msg['createdAt'])) ?>
                      </span>
                    </div>
                    <div class="timeline-message"><?= htmlspecialchars($msg['body']) ?></div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <?php if ($activeReq['status'] !== 'resolved' && $activeReq['status'] !== 'closed'): ?>
         <div class="msg-input-area">
           <textarea class="form-control" id="userReplyText_<?= $activeId ?>" rows="2" 
             placeholder="<?= t('writeReplyPlaceholder'); ?>"></textarea>
           <button class="btn btn-sm btn-neon mt-2" onclick="RecLise.sendReply(<?= $activeId ?>)">
             <i class="fas fa-paper-plane me-1"></i><?= t('sendMessage'); ?>
           </button>
         </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endif; ?>

<script>
const threadsData = <?= json_encode($threads, JSON_UNESCAPED_UNICODE) ?>;
const messagesData = <?= json_encode($messageThreads, JSON_UNESCAPED_UNICODE) ?>;
let currentReqId = <?= $threads[0]['req']['id'] ?? 0 ?>;
const statusLabels = {
    'new': '<?= statusLabel("new") ?>',
    'in_progress': '<?= statusLabel("in_progress") ?>',
    'resolved': '<?= statusLabel("resolved") ?>',
    'escalated': '<?= statusLabel("escalated") ?>',
    'closed': '<?= statusLabel("closed") ?>'
};
const userMap = <?= json_encode(array_map(function($u) { return ['id' => $u['id'], 'name' => $u['full_name'], 'role' => $u['role']]; }, $userMap), JSON_UNESCAPED_UNICODE) ?>;

function selectThread(reqId) {
    currentReqId = reqId;
    document.querySelectorAll('.msg-thread-item').forEach(el => {
        el.classList.toggle('active', parseInt(el.dataset.reqId) === reqId);
    });

    const thread = threadsData.find(t => t.req.id === reqId);
    const msgs = messagesData[reqId] || [];
    const header = document.getElementById('msgHeader');
    const body = document.getElementById('msgBody');
    const inputArea = document.querySelector('.msg-input-area');

    header.innerHTML = `
        <div><strong>#${thread.req.id} — ${escHtml(thread.req.title)}</strong></div>
        <span class="status-pill status-${thread.req.status.replace('_','-')}">${statusLabels[thread.req.status] || thread.req.status}</span>
    `;

    if (msgs.length === 0) {
        body.innerHTML = '<div class="empty-state"><i class="fas fa-comment-slash"></i><p><?php echo t("noMessages"); ?></p></div>';
    } else {
        let html = '<div class="timeline">';
        msgs.forEach(m => {
            const sid = m.senderId || 0;
            const sender = userMap[sid] || null;
            const sType = sender ? (sender.role === 'user' ? 'user' : 'support') : 'system';
            const icon = sType === 'user' ? 'fa-user' : (sType === 'system' ? 'fa-cog' : 'fa-headset');
            const sName = sender ? sender.name : '<?= t("system") ?>';
            html += `<div class="timeline-item ${sType}" style="margin-bottom:16px;">
                <div class="timeline-marker ${sType}"><i class="fas ${icon}"></i></div>
                <div class="timeline-content">
                    <div class="timeline-header">
                        <span class="timeline-author ${sType}">${escHtml(sName)}</span>
                        <span class="timeline-date"><i class="fas fa-clock me-1"></i>${formatDate(m.createdAt)}</span>
                    </div>
                    <div class="timeline-message">${escHtml(m.body)}</div>
                </div>
            </div>`;
        });
        html += '</div>';
        body.innerHTML = html;
    }

    const isActive = thread.req.status !== 'resolved' && thread.req.status !== 'closed';
    if (inputArea) {
        if (isActive) {
            inputArea.innerHTML = `
                <textarea class="form-control" id="userReplyText_${reqId}" rows="2" placeholder="<?php echo t('writeReplyPlaceholder'); ?>"></textarea>
                <button class="btn btn-sm btn-neon mt-2" onclick="RecLise.sendReply(${reqId})">
                    <i class="fas fa-paper-plane me-1"></i><?php echo t('sendMessage'); ?>
                </button>
            `;
            inputArea.style.display = '';
        } else {
            inputArea.style.display = 'none';
        }
    }

    body.scrollTop = body.scrollHeight;
}

function sendUserReply(reqId) {
    const textarea = document.getElementById('userReplyText_' + reqId);
    if (!textarea) return;
    const text = textarea.value.trim();
    if (!text) return;

    fetch('/pfeeeee/PhtP/ajax/api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ action: 'replyToRequest', id: reqId, body: text })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            textarea.value = '';
            selectThread(reqId);
            showToast('<?php echo t("messageSent"); ?>', 'success');
        } else {
            showToast(data.message || 'Error sending message', 'error');
        }
    })
    .catch(() => showToast('Error sending message', 'error'));
}

function escHtml(str) {
    const div = document.createElement('div');
    div.textContent = str || '';
    return div.innerHTML;
}

function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    const d = new Date(dateStr);
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    return months[d.getMonth()] + ' ' + String(d.getDate()).padStart(2,'0') + ', ' + String(d.getHours()).padStart(2,'0') + ':' + String(d.getMinutes()).padStart(2,'0');
}

document.addEventListener('DOMContentLoaded', () => {
    const body = document.getElementById('msgBody');
    if (body) body.scrollTop = body.scrollHeight;
});
</script>

<?php require_once '../includes/footer.php'; ?>

