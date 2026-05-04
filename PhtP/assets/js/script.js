const RecLise = (function () {
  'use strict';

  // Initialize
  function init() {
    const theme = document.documentElement.getAttribute('data-bs-theme') || 'dark';
    
    // Initialize tooltips
    if (typeof bootstrap !== 'undefined') {
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
    }

    // Initialize background animation
    initBackgroundAnimation();
  }

  function initBackgroundAnimation() {
    const cvs = document.getElementById('particleCanvas');
    if (!cvs) return;
    const pctx = cvs.getContext('2d');
    let pts = [], W, H;

    function resize() {
      W = cvs.width = window.innerWidth;
      H = cvs.height = window.innerHeight;
    }

    resize();
    window.addEventListener('resize', () => { resize(); initPts(); });

    class Pt {
      constructor() { this.reset(); }
      reset() {
        this.x = Math.random() * W;
        this.y = Math.random() * H;
        this.vx = (Math.random() - .5) * .48;
        this.vy = (Math.random() - .5) * .48;
        this.r = Math.random() * 1.8 + 0.6;
      }
      step() {
        this.x += this.vx; this.y += this.vy;
        if (this.x < 0 || this.x > W) this.vx *= -1;
        if (this.y < 0 || this.y > H) this.vy *= -1;
      }
      draw() {
        const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        pctx.beginPath(); pctx.arc(this.x, this.y, this.r, 0, Math.PI * 2);
        const color = isDark ? 'rgba(255, 255, 255, 0.9)' : 'rgba(2, 92, 132, 0.9)';
        pctx.fillStyle = color;
        pctx.shadowBlur = 10;
        pctx.shadowColor = isDark ? '#FFFFFF' : '#025C84';
        pctx.fill();
        pctx.shadowBlur = 0;
      }
    }

    function initPts() {
      pts = [];
      const n = Math.min(Math.floor(W * H / 8000), 130);
      for (let i = 0; i < n; i++) pts.push(new Pt());
    }

    initPts();

    const LDIST = 155;
    function drawLinks() {
      const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
      const base = isDark ? '255, 255, 255,' : '2, 92, 132,';
      for (let i = 0; i < pts.length; i++) {
        for (let j = i + 1; j < pts.length; j++) {
          const dx = pts[i].x - pts[j].x, dy = pts[i].y - pts[j].y, d = Math.sqrt(dx * dx + dy * dy);
          if (d < LDIST) {
            pctx.beginPath(); pctx.moveTo(pts[i].x, pts[i].y); pctx.lineTo(pts[j].x, pts[j].y);
            pctx.strokeStyle = `rgba(${base}${(1 - d / LDIST) * 0.45})`;
            pctx.lineWidth = 0.8; pctx.stroke();
          }
        }
      }
    }

    function loop() {
      pctx.clearRect(0, 0, W, H);
      pts.forEach(p => { p.step(); p.draw(); });
      drawLinks();
      requestAnimationFrame(loop);
    }
    loop();
  }

  // Theme toggle
  function toggleTheme() {
    const html = document.documentElement;
    const current = html.getAttribute('data-bs-theme');
    const next = current === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-bs-theme', next);
    
    const icon = document.getElementById('themeIcon');
    if (icon) {
      icon.className = next === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }
    
    // Save preference
    fetch('ajax/api.php?action=setTheme', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'theme=' + next
    }).catch(err => console.error('Failed to save theme:', err));
  }

  // Language toggle
  function toggleLangMenu() {
    const menu = document.getElementById('langMenu');
    if (menu) menu.classList.toggle('show');
  }

  function setLanguage(lang) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = '<input type="hidden" name="set_language" value="' + lang + '">';
    document.body.appendChild(form);
    form.submit();
  }

  // Sidebar toggle (mobile)
  function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (sidebar) sidebar.classList.toggle('show');
    if (overlay) overlay.classList.toggle('show');
  }

  // Toast notification
  function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    
    const toast = document.createElement('div');
    toast.className = 'toast-msg ' + type;
    toast.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle') + ' me-2"></i>' + message;
    container.appendChild(toast);
    
    setTimeout(() => {
      toast.remove();
    }, 3000);
  }

  // Close modal
  function closeModal() {
    const overlay = document.querySelector('.modal-overlay');
    if (overlay) overlay.remove();
  }

  // Filter users table
  function filterUsers(value) {
    const table = document.getElementById('usersTable');
    if (!table) return;
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(value.toLowerCase()) ? '' : 'none';
    });
  }

  // Filter by status
  function filterByStatus() {
    const select = document.getElementById('statusFilter');
    if (!select) return;
    const status = select.value;
    const rows = document.querySelectorAll('tbody tr[data-status]');
    rows.forEach(row => {
      if (!status) {
        row.style.display = '';
      } else {
        row.style.display = row.getAttribute('data-status') === status ? '' : 'none';
      }
    });
  }

  // Scroll to reply section
  function scrollToReply(reqId) {
    const target = document.getElementById('reqMsg-' + reqId);
    if (target) {
      target.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    const select = document.getElementById('messageRequestSelect');
    if (select) select.value = reqId;
    const replyBox = document.getElementById('messageText') || document.getElementById('messageText');
    if (replyBox) replyBox.focus();
  }

  // Send user message (from messages page)
  function sendUserMessage() {
    const reqId = parseInt(document.getElementById('messageRequestSelect')?.value);
    if (!reqId) {
      showToast('Please select a request', 'error');
      return;
    }
    const text = document.getElementById('messageText')?.value.trim();
    if (!text) {
      showToast('Please type a message', 'error');
      return;
    }
    
    fetch('ajax/api.php?action=replyToRequest', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'id=' + reqId + '&body=' + encodeURIComponent(text)
    })
    .then(r => r.json())
    .then(resp => {
      if (resp.error) {
        showToast(resp.error, 'error');
      } else {
        showToast('Message sent successfully', 'success');
        const replyBox = document.getElementById('messageText');
        if (replyBox) replyBox.value = '';
        setTimeout(() => location.reload(), 500);
      }
    })
    .catch(err => {
      showToast('Failed to send message', 'error');
      console.error(err);
    });
  }

  // Submit new request
  function submitNewRequest() {
    const type = document.getElementById('reqType')?.value;
    const title = document.getElementById('reqTitle')?.value.trim();
    const category = document.getElementById('reqCategory')?.value;
    const priority = document.getElementById('reqPriority')?.value;
    const desc = document.getElementById('reqDesc')?.value.trim();
    
    if (!title || !desc) {
      showToast('Please fill required fields', 'error');
      return;
    }
    
    fetch('ajax/api.php?action=createRequest', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'type=' + type + '&title=' + encodeURIComponent(title) + '&category=' + category + '&priority=' + priority + '&description=' + encodeURIComponent(desc)
    })
    .then(r => r.json())
    .then(resp => {
      if (resp.error) {
        showToast(resp.error, 'error');
      } else {
        showToast('Request created successfully', 'success');
        setTimeout(() => window.location.href = 'track-status.php', 500);
      }
    })
    .catch(err => {
      showToast('Failed to create request', 'error');
      console.error(err);
    });
  }

  // View request detail (modal)
  function viewRequestDetail(id) {
    fetch('ajax/api.php?action=getRequest&id=' + id)
    .then(r => r.json())
    .then(req => {
      if (req.error) {
        showToast(req.error, 'error');
        return;
      }
      
      let messagesHtml = '';
      if (req.messages && req.messages.length) {
        req.messages.forEach(msg => {
          const senderType = msg.senderType || 'user';
          const icon = senderType === 'user' ? 'user' : (senderType === 'system' ? 'cog' : 'headset');
          messagesHtml += `
            <div class="timeline-item">
              <div class="timeline-marker ${senderType}">
                <i class="fas fa-${icon}"></i>
              </div>
              <div class="timeline-content">
                <div class="timeline-header">
                  <span class="timeline-author ${senderType}">
                    <i class="fas fa-${icon} me-1"></i>${msg.senderType === 'system' ? 'System' : (msg.senderType === 'user' ? 'User' : 'Support')}
                  </span>
                  <span class="timeline-date">${new Date(msg.createdAt).toLocaleString()}</span>
                </div>
                <div class="timeline-message">${msg.body}</div>
              </div>
            </div>
          `;
        });
      } else {
        messagesHtml = '<p class="text-secondary">No messages yet.</p>';
      }
      
      const content = `
        <button class="modal-close" onclick="RecLise.closeModal()"><i class="fas fa-times"></i></button>
        <h3><i class="fas fa-eye me-2"></i>Request #${req.id}</h3>
        <div class="glass-card mb-3" style="padding: 16px;">
          <h5>${req.title}</h5>
          <div class="mb-2">
            <span class="status-pill status-${req.status}">${req.status}</span>
            <span class="chip">${req.category}</span>
            <span class="chip">${req.priority} priority</span>
          </div>
          <p>${req.description}</p>
        </div>
        <h5 class="mb-3"><i class="fas fa-comments me-2"></i>Messages</h5>
        <div class="timeline">
          ${messagesHtml}
        </div>
      `;
      
      showModal(content);
    })
    .catch(err => {
      showToast('Failed to load request', 'error');
      console.error(err);
    });
  }

  // Show modal
  function showModal(content) {
    let overlay = document.querySelector('.modal-overlay');
    if (!overlay) {
      overlay = document.createElement('div');
      overlay.className = 'modal-overlay';
      document.body.appendChild(overlay);
    }
    overlay.innerHTML = `<div class="modal-box">${content}</div>`;
    overlay.style.display = 'flex';
    overlay.onclick = function(e) {
      if (e.target === overlay) closeModal();
    };
  }

  // Handle logout
  function handleLogout() {
    if (confirm('Are you sure you want to logout?')) {
      window.location.href = 'logout.php';
    }
  }

  // Show Add User Modal
  function showAddUserModal() {
    const content = `
      <button class="modal-close" onclick="RecLise.closeModal()"><i class="fas fa-times"></i></button>
      <h3><i class="fas fa-user-plus me-2"></i>Add User</h3>
      <form onsubmit="event.preventDefault(); RecLise.addUser();">
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" class="form-control" id="modalFullName" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" id="modalEmail" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Role</label>
          <select class="form-select" id="modalRole">
            <option value="user">User</option>
            <option value="support">Support</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Department</label>
          <input type="text" class="form-control" id="modalDept">
        </div>
        <div class="mb-3">
          <label class="form-label">Phone</label>
          <input type="text" class="form-control" id="modalPhone">
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" class="form-control" id="modalPass" placeholder="Leave blank for default">
        </div>
        <button type="submit" class="btn btn-neon w-100">Save</button>
      </form>
    `;
    showModal(content);
  }

  // Add User
  function addUser() {
    const name = document.getElementById('modalFullName')?.value.trim();
    const email = document.getElementById('modalEmail')?.value.trim();
    const role = document.getElementById('modalRole')?.value;
    const dept = document.getElementById('modalDept')?.value.trim();
    const phone = document.getElementById('modalPhone')?.value.trim();
    const pass = document.getElementById('modalPass')?.value;

    if (!name || !email) {
      showToast('Name and email required', 'error');
      return;
    }

    fetch('ajax/api.php?action=addUser', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'fullName=' + encodeURIComponent(name) + '&email=' + encodeURIComponent(email) + '&role=' + role + '&department=' + encodeURIComponent(dept) + '&phone=' + encodeURIComponent(phone) + '&password=' + encodeURIComponent(pass)
    })
    .then(r => r.json())
    .then(resp => {
      if (resp.error) {
        showToast(resp.error, 'error');
      } else {
        showToast('User added successfully', 'success');
        closeModal();
        setTimeout(() => location.reload(), 500);
      }
    })
    .catch(err => {
      showToast('Failed to add user', 'error');
      console.error(err);
    });
  }

  // Show Edit User Modal
  function showEditUserModal(id) {
    fetch('ajax/api.php?action=getUser&id=' + id)
    .then(r => r.json())
    .then(user => {
      if (user.error) {
        showToast(user.error, 'error');
        return;
      }
      const content = `
        <button class="modal-close" onclick="RecLise.closeModal()"><i class="fas fa-times"></i></button>
        <h3><i class="fas fa-user-edit me-2"></i>Edit User</h3>
        <form onsubmit="event.preventDefault(); RecLise.editUser(' + id + ')">
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" class="form-control" id="modalFullName" value="${user.full_name || ''}" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" id="modalEmail" value="${user.email || ''}" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Department</label>
            <input type="text" class="form-control" id="modalDept" value="${user.department || ''}">
          </div>
          <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" class="form-control" id="modalPhone" value="${user.phone || ''}">
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select class="form-select" id="modalStatus">
              <option value="active" ${user.status === 'active' ? 'selected' : ''}>Active</option>
              <option value="inactive" ${user.status === 'inactive' ? 'selected' : ''}>Inactive</option>
            </select>
          </div>
          <button type="submit" class="btn btn-neon w-100">Save</button>
        </form>
      `;
      showModal(content);
    })
    .catch(err => {
      showToast('Failed to load user', 'error');
      console.error(err);
    });
  }

  // Edit User
  function editUser(id) {
    const name = document.getElementById('modalFullName')?.value.trim();
    const email = document.getElementById('modalEmail')?.value.trim();
    const dept = document.getElementById('modalDept')?.value.trim();
    const phone = document.getElementById('modalPhone')?.value.trim();
    const status = document.getElementById('modalStatus')?.value;

    fetch('ajax/api.php?action=updateUser', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'id=' + id + '&fullName=' + encodeURIComponent(name) + '&email=' + encodeURIComponent(email) + '&department=' + encodeURIComponent(dept) + '&phone=' + encodeURIComponent(phone) + '&status=' + status
    })
    .then(r => r.json())
    .then(resp => {
      if (resp.error) {
        showToast(resp.error, 'error');
      } else {
        showToast('User updated successfully', 'success');
        closeModal();
        setTimeout(() => location.reload(), 500);
      }
    })
    .catch(err => {
      showToast('Failed to update user', 'error');
      console.error(err);
    });
  }

  // Delete User
  function deleteUser(id) {
    if (!confirm('Are you sure you want to delete this user?')) return;

    fetch('ajax/api.php?action=deleteUser', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'id=' + id
    })
    .then(r => r.json())
    .then(resp => {
      if (resp.error) {
        showToast(resp.error, 'error');
      } else {
        showToast('User deleted successfully', 'success');
        setTimeout(() => location.reload(), 500);
      }
    })
    .catch(err => {
      showToast('Failed to delete user', 'error');
      console.error(err);
    });
  }

  // Initialize on load
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Show Add Training Modal
  function showAddTrainingModal() {
    showModal('<h3>Add Training Session</h3><form onsubmit="event.preventDefault(); RecLise.addTraining();"><div class="mb-3"><label class="form-label">Title</label><input type="text" class="form-control" id="trainingTitle" required></div><div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" id="trainingDesc" rows="3"></textarea></div><div class="mb-3"><label class="form-label">Date</label><input type="date" class="form-control" id="trainingDate" required></div><div class="mb-3"><label class="form-label">Duration (hours)</label><input type="number" class="form-control" id="trainingDuration" min="1" value="1"></div><button type="submit" class="btn btn-neon w-100">Add Session</button></form>');
  }

  // Add Training
  function addTraining() {
    var title = document.getElementById('trainingTitle').value;
    var desc = document.getElementById('trainingDesc').value;
    var date = document.getElementById('trainingDate').value;
    var duration = document.getElementById('trainingDuration').value;
    fetch('ajax/api.php?action=addTrainingSession', {
      method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'title=' + encodeURIComponent(title) + '&description=' + encodeURIComponent(desc) + '&date=' + encodeURIComponent(date) + '&duration=' + encodeURIComponent(duration)
    })
    .then(r => r.json())
    .then(resp => {
      if (resp.error) { showToast(resp.error, 'error'); } else { showToast('Training added', 'success'); closeModal(); setTimeout(() => location.reload(), 500); }
    })
    .catch(err => { showToast('Failed to add training', 'error'); console.error(err); });
  }

  // Edit Training
  function editTraining(id) {
    fetch('ajax/api.php?action=getTrainingSession&id=' + id)
    .then(r => r.json())
    .then(session => {
      showModal('<h3>Edit Training Session</h3><form onsubmit="event.preventDefault(); RecLise.saveEditSession(' + id + ');"><div class="mb-3"><label class="form-label">Title</label><input type="text" class="form-control" id="editTrainingTitle" value="' + (session.title || '') + '" required></div><div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" id="editTrainingDesc" rows="3">' + (session.description || '') + '</textarea></div><div class="mb-3"><label class="form-label">Date</label><input type="date" class="form-control" id="editTrainingDate" value="' + (session.date || '') + '" required></div><div class="mb-3"><label class="form-label">Duration (hours)</label><input type="number" class="form-control" id="editTrainingDuration" min="1" value="' + (session.duration || 1) + '"></div><button type="submit" class="btn btn-neon w-100">Save Changes</button></form>');
    });
  }

  function saveEditSession(id) {
    var title = document.getElementById('editTrainingTitle').value;
    var desc = document.getElementById('editTrainingDesc').value;
    var date = document.getElementById('editTrainingDate').value;
    var duration = document.getElementById('editTrainingDuration').value;
    fetch('ajax/api.php?action=updateTrainingSession', {
      method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'id=' + id + '&title=' + encodeURIComponent(title) + '&description=' + encodeURIComponent(desc) + '&date=' + encodeURIComponent(date) + '&duration=' + encodeURIComponent(duration)
    })
    .then(r => r.json())
    .then(resp => {
      if (resp.error) { showToast(resp.error, 'error'); } else { showToast('Session updated', 'success'); closeModal(); setTimeout(() => location.reload(), 500); }
    })
    .catch(err => { showToast('Failed to save session', 'error'); });
  }

  // Delete Training
  function deleteTraining(id) {
    if (!confirm('Delete this training session?')) return;
    fetch('ajax/api.php?action=deleteTrainingSession', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'id=' + id })
    .then(r => r.json())
    .then(resp => { if (resp.error) { showToast(resp.error, 'error'); } else { showToast('Training deleted', 'success'); setTimeout(() => location.reload(), 500); } })
    .catch(err => { showToast('Failed to delete', 'error'); });
  }

  // Register for Training
  function registerForTraining(id) {
    fetch('ajax/api.php?action=registerTraining', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'id=' + id })
    .then(r => r.json())
    .then(resp => { if (resp.error) { showToast(resp.error, 'error'); } else { showToast('Registered successfully', 'success'); setTimeout(() => location.reload(), 500); } })
    .catch(err => { showToast('Failed to register', 'error'); });
  }

  // View Registrations
  function viewRegistrations(id) {
    fetch('ajax/api.php?action=getTrainingRegistrations&id=' + id)
    .then(r => r.json())
    .then(regs => {
      var html = '<h3>Registrations</h3><table class="table-glass"><thead><tr><th>User</th><th>Email</th><th>Status</th></tr></thead><tbody>';
      regs.forEach(r => { html += '<tr><td>' + r.fullName + '</td><td>' + r.email + '</td><td><span class="status-pill status-' + r.status + '">' + r.status + '</span></td></tr>'; });
      html += '</tbody></table><button class="btn btn-outline-neon w-100 mt-3" onclick="RecLise.closeModal()">Close</button>';
      showModal(html);
    });
  }

  // Show Add Guide Modal
  function showAddGuideModal() {
    showModal('<h3>Add Guide/Coordinator</h3><form onsubmit="event.preventDefault(); RecLise.addGuide();"><div class="mb-3"><label class="form-label">Full Name</label><input type="text" class="form-control" id="guideName" required></div><div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" id="guideEmail" required></div><div class="mb-3"><label class="form-label">Type</label><select class="form-select" id="guideType"><option value="guide">Guide</option><option value="coordinator">Coordinator</option></select></div><button type="submit" class="btn btn-neon w-100">Add</button></form>');
  }

  // Add Guide
  function addGuide() {
    var name = document.getElementById('guideName').value;
    var email = document.getElementById('guideEmail').value;
    var type = document.getElementById('guideType').value;
    fetch('ajax/api.php?action=addGuide', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'name=' + encodeURIComponent(name) + '&email=' + encodeURIComponent(email) + '&type=' + type })
    .then(r => r.json())
    .then(resp => { if (resp.error) { showToast(resp.error, 'error'); } else { showToast('Guide added', 'success'); closeModal(); setTimeout(() => location.reload(), 500); } })
    .catch(err => { showToast('Failed to add guide', 'error'); });
  }

  // Edit Guide
  function editGuide(id) {
    fetch('ajax/api.php?action=getGuide&id=' + id)
    .then(r => r.json())
    .then(g => {
      showModal('<h3>Edit Guide</h3><form onsubmit="event.preventDefault(); RecLise.saveEditGuide(' + id + ');"><div class="mb-3"><label class="form-label">Full Name</label><input type="text" class="form-control" id="editGuideName" value="' + g.fullName + '" required></div><div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" id="editGuideEmail" value="' + g.email + '" required></div><button type="submit" class="btn btn-neon w-100">Save</button></form>');
    });
  }

  // Save Edit Guide
  function saveEditGuide(id) {
    var name = document.getElementById('editGuideName').value;
    var email = document.getElementById('editGuideEmail').value;
    fetch('ajax/api.php?action=editGuide', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'id=' + id + '&name=' + encodeURIComponent(name) + '&email=' + encodeURIComponent(email) })
    .then(r => r.json())
    .then(resp => { if (resp.error) { showToast(resp.error, 'error'); } else { showToast('Guide updated', 'success'); closeModal(); setTimeout(() => location.reload(), 500); } })
    .catch(err => { showToast('Failed to update', 'error'); });
  }

  // Delete Guide
  function deleteGuide(id) {
    if (!confirm('Delete this guide?')) return;
    fetch('ajax/api.php?action=deleteGuide', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'id=' + id })
    .then(r => r.json())
    .then(resp => { if (resp.error) { showToast(resp.error, 'error'); } else { showToast('Guide deleted', 'success'); setTimeout(() => location.reload(), 500); } })
    .catch(err => { showToast('Failed to delete', 'error'); });
  }

  // Process Request (Support)
  function processRequest(id) {
    fetch('ajax/api.php?action=updateRequestStatus', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'id=' + id + '&status=in_progress' })
    .then(r => r.json())
    .then(resp => {
      if (resp.error) { showToast(resp.error, 'error'); }
      else { location.href = 'support/request-detail.php?id=' + id; }
    });
  }

  // View Assist Guide
  function viewAssistGuide(id) {
    fetch('ajax/api.php?action=getAssistGuide&id=' + id)
    .then(r => r.json())
    .then(g => {
      showModal('<h3>' + g.title + '</h3><div class="glass-card p-3 mt-2"><p>' + g.content.replace(/\n/g, '<br>') + '</p></div><button class="btn btn-outline-neon w-100 mt-3" onclick="RecLise.closeModal()">Close</button>');
    });
  }

  // Show Add Correspondence Modal
  function showAddCorrespondenceModal() {
    showModal('<h3>Add Correspondence</h3><form onsubmit="event.preventDefault(); RecLise.addCorrespondence();"><div class="mb-3"><label class="form-label">Title</label><input type="text" class="form-control" id="corrTitle" required></div><div class="mb-3"><label class="form-label">Type</label><input type="text" class="form-control" id="corrType"></div><div class="mb-3"><label class="form-label">Content</label><textarea class="form-control" id="corrContent" rows="4"></textarea></div><button type="submit" class="btn btn-neon w-100">Add</button></form>');
  }

  // Add Correspondence
  function addCorrespondence() {
    var title = document.getElementById('corrTitle').value;
    var type = document.getElementById('corrType').value;
    var content = document.getElementById('corrContent').value;
    fetch('ajax/api.php?action=addCorrespondence', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'title=' + encodeURIComponent(title) + '&type=' + encodeURIComponent(type) + '&content=' + encodeURIComponent(content) })
    .then(r => r.json())
    .then(resp => { if (resp.error) { showToast(resp.error, 'error'); } else { showToast('Correspondence added', 'success'); closeModal(); setTimeout(() => location.reload(), 500); } })
    .catch(err => { showToast('Failed to add', 'error'); });
  }

  // Delete Correspondence
  function deleteCorrespondence(id) {
    if (!confirm('Delete this correspondence?')) return;
    fetch('ajax/api.php?action=deleteCorrespondence', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'id=' + id })
    .then(r => r.json())
    .then(resp => { if (resp.error) { showToast(resp.error, 'error'); } else { showToast('Deleted', 'success'); setTimeout(() => location.reload(), 500); } })
    .catch(err => { showToast('Failed to delete', 'error'); });
  }

  // Save Correspondence Reassign
  function saveCorrespondenceReassign(id) {
    var assignee = document.getElementById('reassign_' + id).value;
    fetch('ajax/api.php?action=saveCorrespondenceReassign', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'id=' + id + '&assignee=' + assignee })
    .then(r => r.json())
    .then(resp => { if (resp.error) { showToast(resp.error, 'error'); } else { showToast('Reassigned successfully', 'success'); } })
    .catch(err => { showToast('Failed to reassign', 'error'); });
  }

  // Save Correspondence Template
  function saveCorrespondenceTemplate(id) {
    var template = document.getElementById('template_' + id).value;
    fetch('ajax/api.php?action=saveCorrespondenceTemplate', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'id=' + id + '&template=' + encodeURIComponent(template) })
    .then(r => r.json())
    .then(resp => { if (resp.error) { showToast(resp.error, 'error'); } else { showToast('Template saved', 'success'); } })
    .catch(err => { showToast('Failed to save', 'error'); });
  }

  // Show Add Box Modal
  function showAddBoxModal() {
    showModal('<h3>Add Distribution Box</h3><form onsubmit="event.preventDefault(); RecLise.addBox();"><div class="mb-3"><label class="form-label">Name</label><input type="text" class="form-control" id="boxName" required></div><div class="mb-3"><label class="form-label">Location</label><input type="text" class="form-control" id="boxLocation"></div><button type="submit" class="btn btn-neon w-100">Add</button></form>');
  }

  // Add Box
  function addBox() {
    var name = document.getElementById('boxName').value;
    var loc = document.getElementById('boxLocation').value;
    fetch('ajax/api.php?action=addBox', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'name=' + encodeURIComponent(name) + '&location=' + encodeURIComponent(loc) })
    .then(r => r.json())
    .then(resp => { if (resp.error) { showToast(resp.error, 'error'); } else { showToast('Box added', 'success'); closeModal(); setTimeout(() => location.reload(), 500); } })
    .catch(err => { showToast('Failed to add', 'error'); });
  }

  // Delete Box
  function deleteBox(id) {
    if (!confirm('Delete this box?')) return;
    fetch('ajax/api.php?action=deleteBox', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'id=' + id })
    .then(r => r.json())
    .then(resp => { if (resp.error) { showToast(resp.error, 'error'); } else { showToast('Deleted', 'success'); setTimeout(() => location.reload(), 500); } })
    .catch(err => { showToast('Failed to delete', 'error'); });
  }

  // Show Add Issue Modal
  function showAddIssueModal() {
    showModal('<h3>Add Technical Issue</h3><form onsubmit="event.preventDefault(); RecLise.addIssue();"><div class="mb-3"><label class="form-label">Title</label><input type="text" class="form-control" id="issueTitle" required></div><div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" id="issueDesc" rows="3"></textarea></div><div class="mb-3"><label class="form-label">Priority</label><select class="form-select" id="issuePriority"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option></select></div><button type="submit" class="btn btn-neon w-100">Add Issue</button></form>');
  }

  // Add Issue
  function addIssue() {
    var title = document.getElementById('issueTitle').value;
    var desc = document.getElementById('issueDesc').value;
    var priority = document.getElementById('issuePriority').value;
    fetch('ajax/api.php?action=addTechnicalIssue', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'title=' + encodeURIComponent(title) + '&description=' + encodeURIComponent(desc) + '&priority=' + priority })
    .then(r => r.json())
    .then(resp => { if (resp.error) { showToast(resp.error, 'error'); } else { showToast('Issue added', 'success'); closeModal(); setTimeout(() => location.reload(), 500); } })
    .catch(err => { showToast('Failed to add', 'error'); });
  }

  // Resolve Issue
  function resolveIssue(id) {
    fetch('ajax/api.php?action=resolveTechnicalIssue', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'id=' + id })
    .then(r => r.json())
    .then(resp => { if (resp.error) { showToast(resp.error, 'error'); } else { showToast('Issue resolved', 'success'); setTimeout(() => location.reload(), 500); } })
    .catch(err => { showToast('Failed to resolve', 'error'); });
  }

  // Delete Issue
  function deleteIssue(id) {
    if (!confirm('Delete this issue?')) return;
    fetch('ajax/api.php?action=deleteTechnicalIssue', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'id=' + id })
    .then(r => r.json())
    .then(resp => { if (resp.error) { showToast(resp.error, 'error'); } else { showToast('Deleted', 'success'); setTimeout(() => location.reload(), 500); } })
    .catch(err => { showToast('Failed to delete', 'error'); });
  }

  // Add Reference
  function addRef(type) {
    var inputId = 'refInput' + type.charAt(0).toUpperCase() + type.slice(1);
    var val = document.getElementById(inputId).value.trim();
    if (!val) return;
    fetch('ajax/api.php?action=addReferential', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'type=' + type + '&value=' + encodeURIComponent(val) })
    .then(r => r.json())
    .then(resp => { if (resp.error) { showToast(resp.error, 'error'); } else { document.getElementById(inputId).value = ''; showToast('Added', 'success'); setTimeout(() => location.reload(), 500); } })
    .catch(err => { showToast('Failed to add', 'error'); });
  }

  // Remove Reference
  function removeRef(type, value) {
    if (!confirm('Remove this item?')) return;
    fetch('ajax/api.php?action=removeReferential', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'type=' + type + '&value=' + encodeURIComponent(value) })
    .then(r => r.json())
    .then(resp => { if (resp.error) { showToast(resp.error, 'error'); } else { showToast('Removed', 'success'); setTimeout(() => location.reload(), 500); } })
    .catch(err => { showToast('Failed to remove', 'error'); });
  }

  // Save Common Customization
  function saveCommonCustomization() {
    var cols = document.getElementById('commonColumns').value;
    fetch('ajax/api.php?action=saveCommonCustomization', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'columns=' + encodeURIComponent(cols) })
    .then(r => r.json())
    .then(resp => { if (resp.error) { showToast(resp.error, 'error'); } else { showToast('Saved', 'success'); } })
    .catch(err => { showToast('Failed to save', 'error'); });
  }

  // Save Workflow Customization
  function saveWorkflowCustomization() {
    var steps = document.getElementById('workflowSteps').value;
    fetch('ajax/api.php?action=saveWorkflowCustomization', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'steps=' + encodeURIComponent(steps) })
    .then(r => r.json())
    .then(resp => { if (resp.error) { showToast(resp.error, 'error'); } else { showToast('Saved', 'success'); } })
    .catch(err => { showToast('Failed to save', 'error'); });
  }

  // Save Dashboard Customization
  function saveDashboardCustomization() {
    var wr = document.getElementById('widgetRequests')?.checked ? 1 : 0;
    var wu = document.getElementById('widgetUsers')?.checked ? 1 : 0;
    var ws = document.getElementById('widgetStats')?.checked ? 1 : 0;
    var wa = document.getElementById('widgetActivity')?.checked ? 1 : 0;
    fetch('ajax/api.php?action=saveWidgetCustomization', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'widgetRequests=' + wr + '&widgetUsers=' + wu + '&widgetStats=' + ws + '&widgetActivity=' + wa })
    .then(r => r.json())
    .then(resp => { if (resp.error) { showToast(resp.error, 'error'); } else { showToast('Dashboard saved', 'success'); } })
    .catch(err => { showToast('Failed to save', 'error'); });
  }

  // Show Reset Password Modal
  function showResetPasswordModal(id) {
    showModal('<h3>Reset Password</h3><form onsubmit="event.preventDefault(); RecLise.resetPassword(' + id + ');"><div class="mb-3"><label class="form-label">New Password</label><input type="password" class="form-control" id="newPassword" required minlength="6"></div><button type="submit" class="btn btn-neon w-100">Reset Password</button></form>');
  }

  // Reset Password
  function resetPassword(id) {
    var pw = document.getElementById('newPassword').value;
    fetch('ajax/api.php?action=resetPassword', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'id=' + id + '&password=' + encodeURIComponent(pw) })
    .then(r => r.json())
    .then(resp => { if (resp.error) { showToast(resp.error, 'error'); } else { showToast('Password reset', 'success'); closeModal(); } })
    .catch(err => { showToast('Failed to reset', 'error'); });
  }

  // Approve Registration
  function approveRegistration(id) {
    if (!confirm('Approve this registration?')) return;
    fetch('ajax/api.php?action=approveRegistration', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'id=' + id })
    .then(r => r.json())
    .then(resp => { if (resp.error) { showToast(resp.error, 'error'); } else { showToast('Approved', 'success'); setTimeout(() => location.reload(), 500); } })
    .catch(err => { showToast('Failed', 'error'); });
  }

  // Reject Registration
  function rejectRegistration(id) {
    if (!confirm('Reject this registration?')) return;
    fetch('ajax/api.php?action=rejectRegistration', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'id=' + id })
    .then(r => r.json())
    .then(resp => { if (resp.error) { showToast(resp.error, 'error'); } else { showToast('Rejected', 'success'); setTimeout(() => location.reload(), 500); } })
    .catch(err => { showToast('Failed', 'error'); });
  }

  // Show Delegate Modal
  function showDelegateModal(id) {
    showModal('<h3>Delegate Role</h3><form onsubmit="event.preventDefault(); RecLise.delegateRole(' + id + ');"><div class="mb-3"><label class="form-label">New Role</label><select class="form-select" id="newRole"><option value="user">Final User</option><option value="support">Support Team</option><option value="admin">Administrator</option></select></div><button type="submit" class="btn btn-neon w-100">Update Role</button></form>');
  }

  // Delegate Role
  function delegateRole(id) {
    var role = document.getElementById('newRole').value;
    fetch('ajax/api.php?action=delegateRole', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'id=' + id + '&role=' + role })
    .then(r => r.json())
    .then(resp => { if (resp.error) { showToast(resp.error, 'error'); } else { showToast('Role updated', 'success'); closeModal(); setTimeout(() => location.reload(), 500); } })
    .catch(err => { showToast('Failed to update', 'error'); });
  }

  // Show Resolve Escalated Modal
  function showResolveEscalatedModal(id) {
    if (!confirm('Mark this escalated request as resolved?')) return;
    fetch('ajax/api.php?action=updateRequestStatus', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'id=' + id + '&status=resolved' })
    .then(r => r.json())
    .then(resp => { if (resp.error) { showToast(resp.error, 'error'); } else { showToast('Resolved', 'success'); setTimeout(() => location.reload(), 500); } })
    .catch(err => { showToast('Failed to resolve', 'error'); });
  }

  // Render Content (history/audit log tabs)
  function renderContent(tab) {
    fetch('ajax/api.php?action=getContent&tab=' + tab)
    .then(r => r.json())
    .then(data => {
      document.getElementById(tab + 'Content').innerHTML = data.html;
    });
  }

  return {
    toggleTheme,
    toggleLangMenu,
    setLanguage,
    toggleSidebar,
    showToast,
    closeModal,
    filterUsers,
    filterByStatus,
    scrollToReply,
    sendUserMessage,
    submitNewRequest,
    viewRequestDetail,
    showAddUserModal,
    addUser,
    showEditUserModal,
    editUser,
    deleteUser,
    handleLogout,
    showAddTrainingModal,
    addTraining,
    editTraining,
    deleteTraining,
    registerForTraining,
    viewRegistrations,
    showAddGuideModal,
    addGuide,
    editGuide,
    saveEditGuide,
    deleteGuide,
    showAddCorrespondenceModal,
    addCorrespondence,
    deleteCorrespondence,
    showAddBoxModal,
    addBox,
    deleteBox,
    showAddIssueModal,
    addIssue,
    resolveIssue,
    deleteIssue,
    addRef,
    removeRef,
    saveCommonCustomization,
    saveWorkflowCustomization,
    saveDashboardCustomization,
    showResetPasswordModal,
    resetPassword,
    approveRegistration,
    rejectRegistration,
    renderContent,
    saveEditSession,
    showDelegateModal,
    delegateRole,
    viewAssistGuide,
    deleteGuideConfirm: deleteGuide,
    processRequest,
    saveCorrespondenceReassign,
    saveCorrespondenceTemplate,
    showResolveEscalatedModal,
    viewResolvedRequest: viewRequestDetail
  };
})();
