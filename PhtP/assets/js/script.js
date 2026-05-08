const RecLise = (function () {
  'use strict';

  // Initialize
  function init() {
    const theme = document.documentElement.getAttribute('data-bs-theme') || 'dark';
    
    const storedTheme = localStorage.getItem('theme');
    if (storedTheme) {
      document.documentElement.setAttribute('data-bs-theme', storedTheme);
    }
    
    // Load notifications
    fetchNotifications();
    // Poll every 30 seconds
    setInterval(fetchNotifications, 30000);
    
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
    const canvas = document.getElementById('particleCanvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;
    let pts = [], W, H;

    function resize() {
      W = canvas.width = window.innerWidth;
      H = canvas.height = window.innerHeight;
    }

    resize();
    window.addEventListener('resize', () => { resize(); initPts(); });

    function Pt() {
      this.x = Math.random() * W;
      this.y = Math.random() * H;
      this.vx = (Math.random() - 0.5) * 0.48;
      this.vy = (Math.random() - 0.5) * 0.48;
      this.r = Math.random() * 1.8 + 0.6;
      
      this.step = function() {
        this.x += this.vx; this.y += this.vy;
        if (this.x < 0 || this.x > W) this.vx *= -1;
        if (this.y < 0 || this.y > H) this.vy *= -1;
      };
      
      this.draw = function() {
        const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        ctx.beginPath(); 
        ctx.arc(this.x, this.y, this.r, 0, Math.PI * 2);
        const color = isDark ? 'rgba(255, 255, 255, 0.9)' : 'rgba(2, 92, 132, 0.9)';
        ctx.fillStyle = color;
        ctx.shadowBlur = 10;
        ctx.shadowColor = isDark ? '#FFFFFF' : '#025C84';
        ctx.fill();
        ctx.shadowBlur = 0;
      };
    }

    function initPts() {
      pts = [];
      const n = Math.min(Math.floor(W * H / 8000), 130);
      for (let i = 0; i < n; i++) pts.push(new Pt());
    }

    initPts();

    function drawLinks() {
      const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
      const base = isDark ? '255, 255, 255,' : '2, 92, 132,';
      for (let i = 0; i < pts.length; i++) {
        for (let j = i + 1; j < pts.length; j++) {
          const dx = pts[i].x - pts[j].x;
          const dy = pts[i].y - pts[j].y;
          const d = Math.sqrt(dx * dx + dy * dy);
          if (d < 155) {
            ctx.beginPath(); 
            ctx.moveTo(pts[i].x, pts[i].y); 
            ctx.lineTo(pts[j].x, pts[j].y);
            ctx.strokeStyle = 'rgba(' + base + (1 - d / 155) * 0.45 + ')';
            ctx.lineWidth = 0.8; ctx.stroke();
          }
        }
      }
    }

    function loop() {
      ctx.clearRect(0, 0, W, H);
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
    localStorage.setItem('theme', next);

    // Update icon
    const icon = document.getElementById('themeIcon');
    if (icon) {
      icon.className = next === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }

    // Update logo
    const logo = document.getElementById('sidebarLogo');
    const logoDark = document.getElementById('sidebarLogoDark');
    if (logo && logoDark) {
      if (next === 'dark') {
        logo.style.display = 'none';
        logoDark.style.display = 'block';
      } else {
        logo.style.display = 'block';
        logoDark.style.display = 'none';
      }
    }

    // Save preference
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = window.location.href;
    form.innerHTML = '<input type="hidden" name="set_theme" value="' + next + '">';
    document.body.appendChild(form);
    form.submit();
  }

  // Language toggle
  function toggleLangMenu() {
    const menu = document.getElementById('langMenu');
    if (menu) {
      menu.classList.toggle('show');
    }
  }

  // Notifications toggle
  function toggleNotifMenu() {
    const menu = document.getElementById('notifMenu');
    if (menu) menu.classList.toggle('show');
  }

  function fetchNotifications() {
    fetch('/pfeeeee/PhtP/ajax/api.php?action=getNotifications')
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          const badge = document.getElementById('notifBadge');
          if (badge) {
            badge.style.display = data.unreadCount > 0 ? 'inline-block' : 'none';
            badge.innerText = data.unreadCount;
          }
          const list = document.getElementById('notifList');
          if (list) {
            if (data.notifications.length === 0) {
              list.innerHTML = '<div class="p-3 text-center text-secondary">No notifications</div>';
            } else {
              list.innerHTML = data.notifications.map(n => `
                <div class="p-3 border-bottom" style="background:${n.isRead ? 'transparent' : 'rgba(74,58,255,0.05)'};">
                  <div style="font-weight:600;font-size:0.9rem;">${escHtml(n.title)}</div>
                  <div style="font-size:0.8rem;color:var(--text-secondary);">${escHtml(n.body)}</div>
                  <div style="font-size:0.7rem;color:var(--text-secondary);margin-top:4px;">${formatDate2(n.createdAt)}</div>
                </div>
              `).join('');
            }
          }
        }
      }).catch(e => console.error(e));
  }

  function markNotificationsRead() {
    fetch('/pfeeeee/PhtP/ajax/api.php?action=markNotificationsRead')
      .then(r => r.json())
      .then(data => {
        if (data.success) fetchNotifications();
      });
  }

  // Close menus when clicking outside
  document.addEventListener('click', function(e) {
    const langMenu = document.getElementById('langMenu');
    const notifMenu = document.getElementById('notifMenu');
    const langBtn = document.querySelector('.lang-dropdown button');
    const notifBtn = document.querySelector('.notif-dropdown button');
    
    if (langMenu && langBtn && !langBtn.contains(e.target) && !langMenu.contains(e.target)) {
      langMenu.classList.remove('show');
    }
    if (notifMenu && notifBtn && !notifBtn.contains(e.target) && !notifMenu.contains(e.target)) {
      notifMenu.classList.remove('show');
    }
  });

  function setLanguage(lang) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = window.location.href;
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

  // Handle logout
  function handleLogout() {
    if (confirm('Are you sure you want to logout?')) {
      window.location.href = '/pfeeeee/PhtP/logout.php';
    }
  }

  // Toast notification
  function showToast(message, type) {
    type = type || 'info';
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = 'toast-msg ' + type;
    toast.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle') + ' me-2"></i>' + message;
    container.appendChild(toast);

    setTimeout(function() { toast.remove(); }, 3000);
  }

  // Close modal
  function closeModal() {
    document.querySelectorAll('.modal-overlay').forEach(m => m.remove());
  }

    // Submit new request
    function submitNewRequest() {
      const type = document.getElementById('reqType')?.value || 'request';
      const title = document.getElementById('reqTitle')?.value.trim();
      const category = document.getElementById('reqCategory')?.value || 'technical';
      const priority = document.getElementById('reqPriority')?.value || 'medium';
      const desc = document.getElementById('reqDesc')?.value.trim();
      const files = document.getElementById('reqFiles')?.files || [];

      if (!title || !desc) return showToast('Please fill title and description', 'error');

      const formData = new FormData();
      formData.append('action', 'createRequest');
      formData.append('type', type);
      formData.append('title', title);
      formData.append('description', desc);
      formData.append('category', category);
      formData.append('priority', priority);
      
      // Append all files
      for (let i = 0; i < files.length; i++) {
        formData.append('attachments[]', files[i]);
      }

      fetch('/pfeeeee/PhtP/ajax/api.php', {
        method: 'POST',
        body: formData
        // No Content-Type header - browser sets it with boundary for FormData
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          showToast('Request submitted successfully', 'success');
          setTimeout(() => { window.location.href = '/pfeeeee/PhtP/user/track-status.php'; }, 1000);
        } else {
          showToast(data.message || 'Error submitting request', 'error');
        }
      })
      .catch(() => showToast('Error submitting request', 'error'));
    }

  // Submit request from support to admin
  function submitSupportRequest() {
    const type = document.getElementById('reqType')?.value || 'request';
    const title = document.getElementById('reqTitle')?.value.trim();
    const category = document.getElementById('reqCategory')?.value || 'technical';
    const priority = document.getElementById('reqPriority')?.value || 'medium';
    const desc = document.getElementById('reqDesc')?.value.trim();
    const files = document.getElementById('reqFiles')?.files || [];

    if (!title || !desc) return showToast('Please fill title and description', 'error');

    const formData = new FormData();
    formData.append('action', 'createRequest');
    formData.append('type', type);
    formData.append('title', title);
    formData.append('description', desc);
    formData.append('category', category);
    formData.append('priority', priority);
    
    // Append all files
    for (let i = 0; i < files.length; i++) {
      formData.append('attachments[]', files[i]);
    }

    fetch('/pfeeeee/PhtP/ajax/api.php', {
      method: 'POST',
      body: formData
      // No Content-Type header - browser sets it with boundary for FormData
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showToast('Request sent to admin successfully', 'success');
        setTimeout(() => { window.location.href = '/pfeeeee/PhtP/support/dashboard.php'; }, 1000);
      } else {
        showToast(data.message || 'Error submitting request', 'error');
      }
    })
    .catch(() => showToast('Error submitting request', 'error'));
  }

   // View request detail
   function viewRequestDetail(id) {
     const overlay = document.createElement('div');
     overlay.className = 'modal-overlay';
     overlay.innerHTML = `
       <div class="modal-box modal-lg">
         <button class="modal-close" onclick="RecLise.closeModal()"><i class="fas fa-times"></i></button>
         <h3><i class="fas fa-eye me-2"></i>Request #${id}</h3>
         <div id="requestDetailContent">
           <div class="text-center text-secondary p-4">Loading...</div>
         </div>
       </div>`;
     document.body.appendChild(overlay);
     
    fetch('/pfeeeee/PhtP/ajax/api.php?action=getRequest&id=' + id)
      .then(r => r.text())
      .then(text => {
        const el = overlay.querySelector('#requestDetailContent');
        try {
          const data = JSON.parse(text);
          if (data.status === 'success' || data.success) {
            const r = data.request;
            const typeIcon = r.type === 'complaint' ? 'fa-exclamation-triangle' : 'fa-question-circle';
            const catIcon = getRequestIcon(r.category);
            let html = `
              <div class="glass-card mb-3" style="padding:16px;border-left:4px solid var(--warning);">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <h5 style="margin:0;"><i class="fas fa-info-circle me-2"></i>Request Details</h5>
                  <span class="status-pill status-${r.status}">${r.status}</span>
                </div>
                <div class="row text-center mb-3">
                  <div class="col-4">
                    <div style="font-size:0.82rem;color:var(--text-secondary);margin-bottom:4px;">Type</div>
                    <div style="font-weight:600;"><i class="fas ${typeIcon} me-1"></i>${r.type || 'Request'}</div>
                  </div>
                  <div class="col-4">
                    <div style="font-size:0.82rem;color:var(--text-secondary);margin-bottom:4px;">Priority</div>
                    <div style="font-weight:600;"><i class="fas fa-flag me-1"></i><span class="priority-${r.priority}">${r.priority}</span></div>
                  </div>
                  <div class="col-4">
                    <div style="font-size:0.82rem;color:var(--text-secondary);margin-bottom:4px;">Category</div>
                    <div style="font-weight:600;"><i class="fas ${catIcon} me-1"></i>${r.category || 'N/A'}</div>
                  </div>
                </div>
                <div class="mb-2"><strong>Title:</strong> ${r.title || ''}</div>
                <div class="mb-2"><strong>From:</strong> ${r.requester_name || 'N/A'}</div>
                <div class="mb-2"><strong>Assigned To:</strong> ${r.assigned_to ? 'Agent #' + r.assigned_to : 'N/A'}</div>
                <div><strong>Created At:</strong> ${r.created_at ? formatDate2(r.created_at) : 'N/A'}</div>
              </div>
              
              <div class="mb-3">
                <strong>Description:</strong>
                <p style="margin-top:6px;color:var(--text-secondary);padding:12px;background:rgba(0,0,0,0.1);border-radius:8px;">${r.description || 'N/A'}</p>
              </div>
              <div class="mb-3">
                <strong>Attachments:</strong>
                <div style="margin-top:6px;">
                   ${r.attachments && r.attachments.length > 0 ? r.attachments.map(a => { const fp = a.filePath && a.filePath.startsWith('uploads/') ? '/pfeeeee/PhtP/' + a.filePath : '/pfeeeee/PhtP/uploads/' + (a.filePath || ''); return `<span class="chip" style="cursor:pointer;margin-right:4px;" onclick="window.open('${fp}', '_blank')"><i class="fas fa-paperclip me-1"></i>${escHtml(a.fileName || a.file_name)}</span>`; }).join('') : ''}
                </div>
              </div>`;
            if (r.messages && r.messages.length > 0) {
              html += '<h5 class="mt-3 mb-2"><i class="fas fa-comments me-2"></i>Message Thread</h5>';
              html += '<div class="timeline">';
              r.messages.forEach(m => {
                const isSup = m.sender_id !== r.user_id;
                const cls = isSup ? 'support' : 'user';
                const icon = isSup ? 'fa-user-shield' : 'fa-user';
                const author = m.sender_name || (isSup ? 'Support' : 'User');
                html += `<div class="timeline-item ${cls}" style="margin-bottom:16px;">
                  <div class="timeline-marker ${cls}"><i class="fas ${icon}"></i></div>
                  <div class="timeline-content">
                    <div class="timeline-header">
                      <span class="timeline-author ${cls}"><i class="fas ${icon} me-1"></i>${escHtml(author)}</span>
                      <span class="timeline-date"><i class="fas fa-clock me-1"></i>${m.created_at ? formatDate2(m.created_at) : ''}</span>
                    </div>
                    <div class="timeline-message">${escHtml(m.body)}</div>
                  </div>
                </div>`;
              });
              html += '</div>';
            }
            html += `
              <div class="d-flex justify-content-end gap-2 mt-3">`;
            if (typeof CURRENT_USER_ROLE !== 'undefined' && CURRENT_USER_ROLE === 'user' && r.status !== 'resolved' && r.status !== 'closed') {
              html += `
                <button class="btn btn-outline-neon" onclick="RecLise.editRequest(${r.id})">
                  <i class="fas fa-edit me-1"></i>Edit
                </button>
                <button class="btn btn-neon-danger" onclick="RecLise.deleteRequest(${r.id})">
                  <i class="fas fa-trash me-1"></i>Delete
                </button>`;
            }
            html += `
                <button class="btn btn-outline-neon" onclick="RecLise.closeModal()">Close</button>
              </div>`;
            el.innerHTML = html;
          } else {
            el.innerHTML = '<div class="text-danger">Error: ' + (data.message || 'Unknown error') + '</div>';
          }
        } catch (e) {
          el.innerHTML = '<div class="text-danger">Error parsing response: ' + e.message + '<br><small>' + text.substring(0, 200) + '</small></div>';
        }
      })
       .catch(e => {
        document.getElementById('requestDetailContent').innerHTML = '<div class="text-danger">Network error: ' + e.message + '</div>';
      });
  }

    // Show attachments modal
   function showAttachments(id, namesStr, pathsStr) {
     const names = namesStr.split('||').filter(n => n);
     const paths = pathsStr.split('||').filter(p => p);
     let filesHtml = '';
     if (names.length > 0) {
       names.forEach((name, idx) => {
         const path = paths[idx] || '';
          const fullPath = path && path.startsWith('uploads/') ? '/pfeeeee/PhtP/' + path : '/pfeeeee/PhtP/uploads/' + (path || '');
         filesHtml += `<span class="chip" style="cursor:pointer;margin:4px;" onclick="window.open('${fullPath}', '_blank')">
           <i class="fas fa-paperclip me-1"></i>${escHtml(name)}
         </span>`;
       });
      } else {
        filesHtml = '';
      }
     const overlay = document.createElement('div');
     overlay.className = 'modal-overlay';
     overlay.onclick = function(e) {
       if (e.target === overlay) RecLise.closeModal();
     };
     overlay.innerHTML = `
       <div class="modal-box">
         <button class="modal-close" onclick="RecLise.closeModal()"><i class="fas fa-times"></i></button>
         <h3><i class="fas fa-paperclip me-2"></i>Attachments — #${id}</h3>
         <div style="margin-top:16px;">
           ${filesHtml}
         </div>
         <div class="d-flex justify-content-end mt-3">
           <button class="btn btn-outline-neon" onclick="RecLise.closeModal()">Close</button>
         </div>
       </div>`;
     document.body.appendChild(overlay);
   }

   // Process request
   function processRequest(id) {
    if (!confirm('Process this request?')) return;
    fetch('/pfeeeee/PhtP/ajax/api.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({action: 'processRequest', id: id})
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showToast('Request processed successfully', 'success');
        setTimeout(() => location.reload(), 1000);
      } else {
        showToast(data.message || 'Error processing request', 'error');
      }
    })
    .catch(() => showToast('Error processing request', 'error'));
  }

  // Show add training modal
  function showAddTrainingModal() {
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    overlay.id = 'addTrainingOverlay';
    overlay.innerHTML = `
      <div class="modal-box">
        <button class="modal-close" onclick="RecLise.closeModal()"><i class="fas fa-times"></i></button>
        <h3><i class="fas fa-plus me-2"></i>Add Training Session</h3>
        <div>
          <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" class="form-control" id="newTitle" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" id="newDesc" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Date</label>
            <input type="datetime-local" class="form-control" id="newDate" required>
          </div>
          <button type="button" class="btn btn-neon" onclick="RecLise.doAddTraining()">Save</button>
        </div>
      </div>`;
    document.body.appendChild(overlay);
  }

  // Do add training
  function doAddTraining() {
    const title = document.getElementById('newTitle')?.value.trim();
    const desc = document.getElementById('newDesc')?.value.trim();
    const date = document.getElementById('newDate')?.value;

    if (!title || !date) {
      return showToast('Title and date required', 'error');
    }

    fetch('/pfeeeee/PhtP/ajax/api.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({action: 'addTrainingSession', title: title, description: desc, session_date: date.replace('T', ' ') + ':00'})
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showToast('Training session added', 'success');
        closeModal();
        setTimeout(() => location.reload(), 1000);
      } else {
        showToast(data.message || 'Error adding training', 'error');
      }
    })
    .catch(err => showToast('Error: ' + err.message, 'error'));
  }

  // Edit training
  function editTraining(id) {
    fetch('/pfeeeee/PhtP/ajax/api.php?action=getTrainingSession&id=' + id)
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          const t = data.session;
          const overlay = document.createElement('div');
          overlay.className = 'modal-overlay';
          overlay.innerHTML = `
            <div class="modal-box">
              <button class="modal-close" onclick="RecLise.closeModal()"><i class="fas fa-times"></i></button>
              <h3><i class="fas fa-edit me-2"></i>Edit Training Session</h3>
              <form id="editTrainingForm">
                <div class="mb-3">
                  <label class="form-label">Title</label>
                  <input type="text" class="form-control" name="title" value="${t.title || ''}" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Description</label>
                  <textarea class="form-control" name="description" rows="3">${t.description || ''}</textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label">Date</label>
                  <input type="datetime-local" class="form-control" name="date" value="${t.session_date ? t.session_date.replace(' ', 'T').substring(0, 16) : ''}" required>
                </div>
                <button type="button" class="btn btn-neon" onclick="RecLise.updateTraining(${id})">Update</button>
              </form>
            </div>`;
          document.body.appendChild(overlay);
        } else {
          RecLise.showToast('Error loading training', 'error');
        }
      })
    .catch(err => RecLise.showToast('Error: ' + err.message, 'error'));
  }

  // Update training
  function updateTraining(id) {
    const form = document.getElementById('editTrainingForm');
    if (!form) return;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    if (!data.title || !data.date) {
        RecLise.showToast('Title and date required', 'error');
        return;
    }
    
    fetch('/pfeeeee/PhtP/ajax/api.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({action: 'updateTrainingSession', id: id, title: data.title, description: data.description, session_date: data.date ? data.date.replace('T', ' ') + ':00' : ''})
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        RecLise.showToast('Training session updated', 'success');
        RecLise.closeModal();
        setTimeout(() => location.reload(), 1000);
      } else {
        RecLise.showToast(data.message || 'Error updating training', 'error');
      }
    })
    .catch(err => RecLise.showToast('Error: ' + err.message, 'error'));
  }

  // Delete training
  function deleteTraining(id) {
    if (!confirm('Delete this training session?')) return;
    fetch('/pfeeeee/PhtP/ajax/api.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({action: 'deleteTrainingSession', id: id})
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        RecLise.showToast('Training deleted', 'success');
        setTimeout(() => location.reload(), 1000);
      } else {
        RecLise.showToast(data.message || 'Error deleting training', 'error');
      }
    })
    .catch(err => RecLise.showToast('Error: ' + err.message, 'error'));
  }

  // Delete training confirmation
  function deleteTrainingConfirm(id) {
    if (!confirm('Delete this training session?')) return;
    deleteTraining(id);
  }

  // View registrations
  function viewRegistrations(sessionId) {
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    overlay.innerHTML = `
      <div class="modal-box">
        <button class="modal-close" onclick="RecLise.closeModal()"><i class="fas fa-times"></i></button>
        <h3><i class="fas fa-list me-2"></i>Registrations</h3>
        <div id="registrationsContent">
          <div class="text-center text-secondary p-4">Loading...</div>
        </div>
      </div>`;
    document.body.appendChild(overlay);
    
    fetch('/pfeeeee/PhtP/ajax/api.php?action=getTrainingRegistrations&session_id=' + sessionId)
      .then(r => r.json())
      .then(data => {
        const el = document.getElementById('registrationsContent');
        if (data.success && data.registrations && data.registrations.length > 0) {
          let html = '<table class="table-glass"><thead><tr><th>Name</th><th>Email</th><th>Date</th></tr></thead><tbody>';
          data.registrations.forEach(reg => {
            html += `<tr><td>${reg.full_name || ''}</td><td>${reg.email || ''}</td><td>${reg.registered_at || ''}</td></tr>`;
          });
          html += '</tbody></table>';
          el.innerHTML = html;
        } else {
          el.innerHTML = '<div class="text-secondary text-center p-4">No registrations yet</div>';
        }
      })
      .catch(() => {
        document.getElementById('registrationsContent').innerHTML = '<div class="text-danger">Error loading registrations</div>';
      });
  }

  // Show add guide modal
  function showAddGuideModal() {
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    overlay.innerHTML = `
      <div class="modal-box">
        <button class="modal-close" onclick="RecLise.closeModal()"><i class="fas fa-times"></i></button>
        <h3><i class="fas fa-plus me-2"></i>Add Guide</h3>
        <form onsubmit="RecLise.submitGuide(event)">
          <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" class="form-control" name="title" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea class="form-control" name="content" rows="5" required></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Category</label>
            <select class="form-control" name="category">
              <option value="technical">Technical</option>
              <option value="billing">Billing</option>
              <option value="general">General</option>
            </select>
          </div>
          <button type="submit" class="btn btn-neon">Save</button>
        </form>
      </div>`;
    document.body.appendChild(overlay);
  }

  // Submit guide
  function submitGuide(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    fetch('/pfeeeee/PhtP/ajax/api.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({action: 'addGuide', title: data.title, content: data.content, category: data.category})
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showToast('Guide added', 'success');
        closeModal();
        setTimeout(() => location.reload(), 1000);
      } else {
        showToast(data.message || 'Error adding guide', 'error');
      }
    })
    .catch(() => showToast('Error adding guide', 'error'));
  }

  // Edit guide
  function editGuide(id) {
    fetch('/pfeeeee/PhtP/ajax/api.php?action=getGuide&id=' + id)
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          const g = data.guide;
          const overlay = document.createElement('div');
          overlay.className = 'modal-overlay';
          overlay.innerHTML = `
            <div class="modal-box">
              <button class="modal-close" onclick="RecLise.closeModal()"><i class="fas fa-times"></i></button>
              <h3><i class="fas fa-edit me-2"></i>Edit Guide</h3>
              <form onsubmit="RecLise.updateGuide(event, ${id})">
                <div class="mb-3">
                  <label class="form-label">Title</label>
                  <input type="text" class="form-control" name="title" value="${g.title || ''}" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Content</label>
                  <textarea class="form-control" name="content" rows="5" required>${g.content || ''}</textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label">Category</label>
                  <select class="form-control" name="category">
                    <option value="technical" ${g.category === 'technical' ? 'selected' : ''}>Technical</option>
                    <option value="billing" ${g.category === 'billing' ? 'selected' : ''}>Billing</option>
                    <option value="general" ${g.category === 'general' ? 'selected' : ''}>General</option>
                  </select>
                </div>
                <button type="submit" class="btn btn-neon">Update</button>
              </form>
            </div>`;
          document.body.appendChild(overlay);
        } else {
          showToast('Error loading guide', 'error');
        }
      })
      .catch(() => showToast('Error loading guide', 'error'));
  }

  // Update guide
  function updateGuide(e, id) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    fetch('/pfeeeee/PhtP/ajax/api.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({action: 'editGuide', id: id, title: data.title, content: data.content, category: data.category})
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showToast('Guide updated', 'success');
        closeModal();
        setTimeout(() => location.reload(), 1000);
      } else {
        showToast(data.message || 'Error updating guide', 'error');
      }
    })
    .catch(() => showToast('Error updating guide', 'error'));
  }

  // Delete guide confirmation
  function deleteGuideConfirm(id) {
    if (!confirm('Delete this guide?')) return;
    deleteGuide(id);
  }

  // Delete guide
  function deleteGuide(id) {
    fetch('/pfeeeee/PhtP/ajax/api.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({action: 'deleteGuide', id: id})
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showToast('Guide deleted', 'success');
        setTimeout(() => location.reload(), 1000);
      } else {
        showToast(data.message || 'Error deleting guide', 'error');
      }
    })
    .catch(() => showToast('Error deleting guide', 'error'));
  }

  // View assist guide
  function viewAssistGuide(id) {
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    overlay.innerHTML = `
      <div class="modal-box">
        <button class="modal-close" onclick="RecLise.closeModal()"><i class="fas fa-times"></i></button>
        <h3><i class="fas fa-book-open me-2"></i>Guide</h3>
        <div id="guideContent">
          <div class="text-center text-secondary p-4">Loading...</div>
        </div>
      </div>`;
    document.body.appendChild(overlay);
    
    fetch('/pfeeeee/PhtP/ajax/api.php?action=getGuide&id=' + id)
      .then(r => r.json())
      .then(data => {
        const el = document.getElementById('guideContent');
        if (data.success) {
          const g = data.guide;
          el.innerHTML = `
            <h4>${g.title || ''}</h4>
            <span class="chip">${g.category || ''}</span>
            <div class="mt-3">${g.content || ''}</div>`;
        } else {
          el.innerHTML = '<div class="text-danger">Error loading guide</div>';
        }
      })
      .catch(() => {
        document.getElementById('guideContent').innerHTML = '<div class="text-danger">Error loading guide</div>';
      });
  }

  // View resolved request
  function viewResolvedRequest(id) {
    viewRequestDetail(id);
  }

  // Show reply modal for incoming requests
  function showReplyModal(id) {
    try {
      const overlay = document.createElement('div');
      overlay.className = 'modal-overlay';
      overlay.innerHTML = `
        <div class="modal-box modal-lg">
          <button class="modal-close" onclick="RecLise.closeModal()"><i class="fas fa-times"></i></button>
          <h3><i class="fas fa-eye me-2"></i>Request Details — #${id}</h3>
          <div id="replyModalContent">
            <div class="text-center text-secondary p-4">Loading...</div>
          </div>
        </div>`;
      document.body.appendChild(overlay);

      fetch('/pfeeeee/PhtP/ajax/api.php?action=getRequest&id=' + id)
        .then(r => r.json())
        .then(data => {
          const el = overlay.querySelector('#replyModalContent');
          if (!el) return;
          if (data.success) {
            const r = data.request;
            const typeIcon = r.type === 'complaint' ? 'fa-exclamation-triangle' : 'fa-question-circle';
            let html = `
              <div class="glass-card mb-3" style="padding:16px;border-left:4px solid var(${r.status === 'escalated' ? '--warning' : '--info'});">
                <div class="d-flex align-items-center justify-content-between mb-3">
                  <h5 style="margin:0;"><i class="fas fa-info-circle me-2"></i>Request Details</h5>
                  <span class="status-pill status-${r.status}">${(r.status || '').charAt(0).toUpperCase() + (r.status || '').slice(1)}</span>
                </div>
                <div class="row text-center mb-3">
                  <div class="col-4">
                    <div style="font-size:0.82rem;color:var(--text-secondary);margin-bottom:4px;">Type</div>
                    <div style="font-weight:600;"><i class="fas ${typeIcon} me-1"></i>${(r.type || 'Request').charAt(0).toUpperCase() + (r.type || 'Request').slice(1)}</div>
                  </div>
                  <div class="col-4">
                    <div style="font-size:0.82rem;color:var(--text-secondary);margin-bottom:4px;">Priority</div>
                    <div style="font-weight:600;"><i class="fas fa-flag me-1"></i>${(r.priority || 'Low').charAt(0).toUpperCase() + (r.priority || 'Low').slice(1)}</div>
                  </div>
                  <div class="col-4">
                    <div style="font-size:0.82rem;color:var(--text-secondary);margin-bottom:4px;">Category</div>
                    <div style="font-weight:600;"><i class="fas fa-cogs me-1"></i>${(r.category || 'N/A').charAt(0).toUpperCase() + (r.category || 'N/A').slice(1)}</div>
                  </div>
                </div>
                <div class="mb-2"><strong>Title:</strong> ${r.title || ''}</div>
                <div class="mb-2"><strong>From:</strong> ${r.requester_name || r.requesterName || 'N/A'}</div>
                <div class="mb-2"><strong>Assigned To:</strong> ${r.assignedName || r.assigned_to_name || 'N/A'}</div>
                <div><strong>Created At:</strong> ${r.created_at || r.createdAt ? formatDate2(r.created_at || r.createdAt) : 'N/A'}</div>
              </div>
              
              <div class="mb-3">
                <strong>Description:</strong>
                <p style="margin-top:6px;color:var(--text-secondary);padding:12px;background:rgba(0,0,0,0.1);border-radius:8px;">${r.description || 'N/A'}</p>
              </div>
              
              <div class="mb-3">
                <strong>Attachments:</strong>
                <div style="margin-top:6px;">
                  ${r.attachments && r.attachments.length > 0 ? r.attachments.map(a => { const fp = a.filePath && a.filePath.startsWith('uploads/') ? '/pfeeeee/PhtP/' + a.filePath : '/pfeeeee/PhtP/uploads/' + (a.filePath || ''); return `<span class="chip" style="cursor:pointer;margin-right:4px;" onclick="window.open('${fp}', '_blank')"><i class="fas fa-paperclip me-1"></i>${escHtml(a.fileName || a.file_name)}</span>`; }).join('') : ''}
                </div>
              </div>`;

            if (r.messages && r.messages.length > 0) {
              html += '<h5 class="mt-3 mb-2"><i class="fas fa-comments me-2"></i>Message Thread</h5>';
              html += '<div class="timeline" style="margin-bottom:16px;">';
              r.messages.forEach(m => {
                const isSup = m.senderType !== 'user';
                const cls = isSup ? 'support' : 'user';
                const icon = isSup ? 'fa-user-shield' : 'fa-user';
                const author = m.senderName || (isSup ? 'Support' : 'User');
                html += `<div class="timeline-item ${cls}" style="margin-bottom:16px;">
                  <div class="timeline-marker ${cls}"><i class="fas ${icon}"></i></div>
                  <div class="timeline-content">
                    <div class="timeline-header">
                      <span class="timeline-author ${cls}"><i class="fas ${icon} me-1"></i>${escHtml(author)}</span>
                      <span class="timeline-date"><i class="fas fa-clock me-1"></i>${m.createdAt ? formatDate2(m.createdAt) : ''}</span>
                    </div>
                    <div class="timeline-body">${m.body ? escHtml(m.body).replace(/\n/g, '<br>') : ''}</div>
                  </div>
                </div>`;
              });
              html += '</div>';
            }
              
            if (r.status === 'escalated') {
              html += `
              <div class="d-flex justify-content-end gap-2">
                <button class="btn btn-outline-neon" onclick="RecLise.closeModal()">Close</button>
              </div>`;
            } else {
              html += `
              <div class="glass-card mb-3" style="padding:16px;">
                <h5 style="margin-bottom:14px;"><i class="fas fa-pen me-2"></i>Your Reply</h5>
                <textarea class="form-control" id="replyText" rows="4" placeholder="Write your reply here..."></textarea>
              </div>
              
              <div class="d-flex gap-2">
                <button class="btn btn-neon" style="flex:1;" onclick="RecLise.submitReply(${id})"><i class="fas fa-check me-2"></i>Reply & Update</button>
                <button class="btn btn-outline-neon" style="flex:1;" onclick="RecLise.escalateToAdmin(${id})"><i class="fas fa-arrow-up me-1"></i>Escalate to Admin</button>
              </div>`;
            }
            el.innerHTML = html;
          } else {
            el.innerHTML = '<div class="text-danger">Error loading request</div>';
          }
        })
        .catch(err => {
          const el = document.getElementById('replyModalContent');
          if (el) {
            el.innerHTML = '<div class="text-danger">Error loading request: ' + err.message + '</div>';
          }
        });
    } catch (err) {
      showToast('Error opening modal: ' + err.message, 'error');
    }
  }

   // Send reply from messages page (User side)
   function sendReply(id) {
     const textarea = document.getElementById('userReplyText_' + id);
     const reply = textarea?.value.trim();
     if (!reply) return showToast('Please write a reply', 'error');
     
     fetch('/pfeeeee/PhtP/ajax/api.php?action=replyToRequest', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id + '&body=' + encodeURIComponent(reply)
      })
      .then(r => r.json())
      .then(resp => {
        if (resp.error) { showToast(resp.error, 'error'); }
        else {
          showToast('Reply sent successfully', 'success');
          setTimeout(() => location.reload(), 500);
        }
      })
      .catch(err => { showToast('Failed to send reply', 'error'); console.error(err); });
   }

    // Submit reply and resolve
    function submitReply(id) {
      let reply = document.getElementById('replyText')?.value.trim();
      if (!reply) {
        const textarea = document.getElementById('userReplyText_' + id);
        reply = textarea?.value.trim();
      }
      if (!reply) return showToast('Please write a reply', 'error');

      fetch('/pfeeeee/PhtP/ajax/api.php?action=updateRequestStatus', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id + '&status=resolved&reply=' + encodeURIComponent(reply)
      })
      .then(r => r.json())
      .then(resp => {
        if (resp.error) { showToast(resp.error, 'error'); }
        else {
          showToast('Reply sent successfully', 'success');
          closeModal();
          setTimeout(() => location.reload(), 500);
        }
      })
      .catch(err => { showToast('Failed to send reply', 'error'); console.error(err); });
    }

  // Escalate to admin
  function escalateToAdmin(id) {
    const reply = document.getElementById('replyText')?.value.trim();
    const body = reply ? '&body=' + encodeURIComponent(reply) : '';

    fetch('/pfeeeee/PhtP/ajax/api.php?action=escalateRequest', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'id=' + id + body
    })
    .then(r => r.json())
    .then(resp => {
      if (resp.error) { showToast(resp.error, 'error'); }
      else {
        showToast('Escalated to admin', 'success');
        closeModal();
        setTimeout(() => location.reload(), 500);
      }
    })
    .catch(err => { showToast('Failed to escalate', 'error'); console.error(err); });
  }

  // Save dashboard customization
  function saveDashboard() {
    const data = {
      action: 'saveDashboard',
      widget_requests: document.getElementById('uwRequests')?.checked ? 1 : 0,
      widget_users: document.getElementById('uwUsers')?.checked ? 1 : 0,
      widget_stats: document.getElementById('uwStats')?.checked ? 1 : 0,
      widget_activity: document.getElementById('uwActivity')?.checked ? 1 : 0
    };
    
    fetch('/pfeeeee/PhtP/ajax/api.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showToast('Dashboard settings saved', 'success');
      } else {
        showToast(data.message || 'Error saving settings', 'error');
      }
    })
    .catch(() => showToast('Error saving settings', 'error'));
  }

  // Render content (for messages page)
  function renderContent(view) {
    const contentEl = document.getElementById('contentArea');
    if (!contentEl) return;
    
    contentEl.innerHTML = '<div class="text-center text-secondary p-4">Loading...</div>';
    
    fetch('/pfeeeee/PhtP/ajax/api.php?action=getContent&view=' + view)
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          contentEl.innerHTML = data.html || '<div class="text-secondary text-center p-4">No content</div>';
        } else {
          contentEl.innerHTML = '<div class="text-danger">Error loading content</div>';
        }
      })
      .catch(() => {
        contentEl.innerHTML = '<div class="text-danger">Error loading content</div>';
      });
  }

  // Initialize on load
  function getRequestIcon(category) {
    const icons = {
      technical: 'fa-cogs',
      access: 'fa-key',
      training: 'fa-graduation-cap',
      complaint: 'fa-exclamation-triangle'
    };
    return icons[category] || 'fa-question-circle';
  }

  function formatDate2(dateStr) {
    if (!dateStr) return 'N/A';
    const d = new Date(dateStr);
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    return months[d.getMonth()] + ' ' + String(d.getDate()).padStart(2,'0') + ', ' + String(d.getHours()).padStart(2,'0') + ':' + String(d.getMinutes()).padStart(2,'0');
  }

  function escHtml(str) {
    const div = document.createElement('div');
    div.textContent = str || '';
    return div.innerHTML;
  }

  function registerForTraining(sessionId) {
    fetch('/pfeeeee/PhtP/ajax/api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'registerTraining', session_id: sessionId })
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showToast('Registered successfully', 'success');
        setTimeout(() => location.reload(), 800);
      } else {
        showToast(data.message || 'Already registered', 'error');
      }
    })
    .catch(() => showToast('Error registering', 'error'));
  }

  function unregisterFromTraining(sessionId) {
    fetch('/pfeeeee/PhtP/ajax/api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'unregisterTraining', session_id: sessionId })
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showToast('Unregistered successfully', 'success');
        setTimeout(() => location.reload(), 800);
      } else {
        showToast(data.message || 'Error unregistering', 'error');
      }
    })
    .catch(() => showToast('Error unregistering', 'error'));
  }

  function editRequest(id) {
    fetch('/pfeeeee/PhtP/ajax/api.php?action=getRequest&id=' + id)
      .then(r => r.json())
      .then(data => {
        if (!data.success) return showToast('Error loading request', 'error');
        const r = data.request;
        const overlay = document.createElement('div');
        overlay.className = 'modal-overlay';
        overlay.innerHTML = `
          <div class="modal-box modal-lg">
            <button class="modal-close" onclick="RecLise.closeModal()"><i class="fas fa-times"></i></button>
            <h3><i class="fas fa-edit me-2"></i>Edit Request #${id}</h3>
            <form id="editRequestForm" class="mt-3">
              <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" class="form-control" id="editReqTitle" value="${escHtml(r.title || '')}" required>
              </div>
              <div class="row mb-3">
                <div class="col-6">
                  <label class="form-label">Category</label>
                  <select class="form-select" id="editReqCategory">
                    <option value="technical" ${r.category === 'technical' ? 'selected' : ''}>Technical</option>
                    <option value="access" ${r.category === 'access' ? 'selected' : ''}>Access</option>
                    <option value="training" ${r.category === 'training' ? 'selected' : ''}>Training</option>
                    <option value="complaint" ${r.category === 'complaint' ? 'selected' : ''}>Complaint</option>
                  </select>
                </div>
                <div class="col-6">
                  <label class="form-label">Priority</label>
                  <select class="form-select" id="editReqPriority">
                    <option value="low" ${r.priority === 'low' ? 'selected' : ''}>Low</option>
                    <option value="medium" ${r.priority === 'medium' ? 'selected' : ''}>Medium</option>
                    <option value="high" ${r.priority === 'high' ? 'selected' : ''}>High</option>
                    <option value="urgent" ${r.priority === 'urgent' ? 'selected' : ''}>Urgent</option>
                  </select>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" id="editReqDesc" rows="4" required>${escHtml(r.description || '')}</textarea>
              </div>
              <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-neon" onclick="RecLise.closeModal()">Cancel</button>
                <button type="button" class="btn btn-neon" onclick="RecLise.saveEditRequest(${id})">Save Changes</button>
              </div>
            </form>
          </div>`;
        RecLise.closeModal();
        document.body.appendChild(overlay);
      });
  }

  function saveEditRequest(id) {
    const title = document.getElementById('editReqTitle').value.trim();
    const category = document.getElementById('editReqCategory').value;
    const priority = document.getElementById('editReqPriority').value;
    const description = document.getElementById('editReqDesc').value.trim();
    if (!title || !description) return showToast('Title and description required', 'error');
    fetch('/pfeeeee/PhtP/ajax/api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'editRequest', id, title, category, priority, description })
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showToast('Request updated successfully', 'success');
        RecLise.closeModal();
        setTimeout(() => location.reload(), 800);
      } else {
        showToast(data.message || 'Error updating request', 'error');
      }
    })
    .catch(() => showToast('Error updating request', 'error'));
  }

  function deleteRequest(id) {
    if (!confirm('Are you sure you want to delete this request? This cannot be undone.')) return;
    fetch('/pfeeeee/PhtP/ajax/api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'deleteRequest', id })
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showToast('Request deleted successfully', 'success');
        RecLise.closeModal();
        setTimeout(() => location.reload(), 800);
      } else {
        showToast(data.message || 'Error deleting request', 'error');
      }
    })
    .catch(() => showToast('Error deleting request', 'error'));
  }

  function filterByStatus() {
    const val = document.getElementById('statusFilter')?.value || '';
    const rows = document.querySelectorAll('.table-glass tbody tr');
    rows.forEach(tr => {
      const s = tr.getAttribute('data-status') || '';
      tr.style.display = (!val || s === val) ? '' : 'none';
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

   return {
      toggleTheme,
      toggleLangMenu,
      toggleNotifMenu,
      markNotificationsRead,
      setLanguage,
      toggleSidebar,
      handleLogout,
      showToast,
      closeModal,
      submitNewRequest,
      submitSupportRequest,
      viewRequestDetail,
      editRequest,
      saveEditRequest,
      deleteRequest,
      processRequest,
      showAddTrainingModal,
      doAddTraining,
      editTraining,
      updateTraining,
      deleteTraining,
     deleteTrainingConfirm,
     registerForTraining,
     unregisterFromTraining,
     viewRegistrations,
     showAddGuideModal,
     submitGuide,
     editGuide,
     updateGuide,
      deleteGuide,
      deleteGuideConfirm,
      filterByStatus,
      viewAssistGuide,
      viewResolvedRequest,
      showReplyModal,
      submitReply,
      sendReply,
      escalateToAdmin,
      saveDashboard,
      renderContent,
      showAttachments,
      showAddTrainingModal,
      doAddTraining
    };
})();





