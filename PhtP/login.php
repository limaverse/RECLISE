<?php
session_start();
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/i18n.php';

if (Auth::check()) {
    Auth::redirectByRole();
}

$error = '';
$lang = $_GET['lang'] ?? ($_SESSION['lang'] ?? 'fr');
$_SESSION['lang'] = $lang;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = t('fillAllFields');
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && hash('sha256', $password) === $user['password_hash']) {
            if ($user['status'] !== 'active') {
                $error = t('accountInactive');
            } else {
                Auth::login($user);
                $role = $user['role'] ?? 'user';
                $stmt = $pdo->prepare("INSERT INTO audit_logs (action, user_id, details) VALUES ('LOGIN', ?, ?)");
                $stmt->execute([$user['id'], $role . ' logged in']);
                Auth::redirectByRole();
            }
        } else {
            $error = t('invalidCredentials');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" data-bs-theme="dark" dir="<?= ($lang === 'ar' ? 'rtl' : 'ltr') ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- base tag removed - using absolute paths -->
<meta name="description" content="RecLise - Connexion - ELISSA Support Platform.">
<title>RecLise — <?php echo t('login'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="/pfeeeee/PhtP/assets/css/styles.css" rel="stylesheet">
<style>
[data-bs-theme="dark"] .login-box .logo-image {
    filter: brightness(0) invert(1);
}
.login-actions { display: flex; justify-content: center; gap: 12px; }
.btn-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: var(--input-bg);
    border: 1px solid var(--input-border);
    color: var(--text-secondary);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
}
.btn-icon:hover {
    color: var(--neon-accent);
    border-color: rgba(var(--brand-accent-rgb), 0.3);
}
.dropdown-menu {
    background: var(--modal-bg);
    border: 1px solid var(--glass-border);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}
.dropdown-item {
    color: var(--text-primary);
}
.dropdown-item:hover {
    background: rgba(var(--brand-accent-rgb), 0.1);
    color: var(--neon-accent);
}
</style>
</head>
<body class="<?= ($lang === 'ar' ? 'rtl' : '') ?>">
<canvas id="particleCanvas"></canvas>
<div class="toast-container" id="toastContainer"></div>
<?php if ($error): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    showToast('<?php echo addslashes($error); ?>', 'error');
});
</script>
<?php endif; ?>
<div id="loginPage">
<div class="login-container">
<div class="login-box">
<img src="/pfeeeee/Untitled-1.png" alt="Logo" class="logo-image" />
<p class="subtitle" id="loginSubtitle"><?php echo t('welcome'); ?> — ELISSA Support Platform — MESRS</p>
<form id="loginForm" method="POST" action="login.php?lang=<?php echo $lang; ?>" autocomplete="off">
<div class="mb-3 text-start">
<label class="form-label" id="lblUsername"><?php echo t('username'); ?></label>
<input type="text" class="form-control" name="email" id="inputUsername" placeholder="admin@mesrs.tn" required>
</div>
<div class="mb-3 text-start">
<label class="form-label" id="lblPassword"><?php echo t('password'); ?></label>
<input type="password" class="form-control" name="password" id="inputPassword" placeholder="••••••••" required>
</div>
<button type="submit" class="btn btn-neon w-100" id="btnAuthenticate">
<i class="fas fa-fingerprint me-2"></i><span><?php echo t('authenticate'); ?></span>
</button>
</form>
<div class="mt-3 login-actions">
<div class="dropdown">
<button class="btn-icon" id="loginLangBtn" data-bs-toggle="dropdown" aria-expanded="false" title="<?php echo t('changeLang'); ?>"><i class="fas fa-globe"></i></button>
<ul class="dropdown-menu">
<li><a class="dropdown-item" href="?lang=fr">Français (FR)</a></li>
<li><a class="dropdown-item" href="?lang=en">English (EN)</a></li>
<li><a class="dropdown-item" href="?lang=ar">العربية (AR)</a></li>
</ul>
</div>
<button class="btn-icon" id="loginThemeBtn" onclick="toggleTheme()" title="<?php echo t('toggleTheme'); ?>"><i class="fas fa-moon" id="themeIconLogin"></i></button>
</div>
</div>
</div>
</div>
<canvas id="loginParticles"></canvas>

<!-- Request Access Modal -->
<div class="modal fade" id="requestAccessModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background:var(--card-bg);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,0.1);border-radius:16px;">
      <div class="modal-header border-bottom-0">
        <h5 class="modal-title" style="color:var(--text-primary);"><i class="fas fa-user-plus me-2"></i>Request Access</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="requestAccessForm">
          <div class="mb-3">
            <label class="form-label" style="color:var(--text-secondary);">Full Name</label>
            <input type="text" class="form-control" name="full_name" required>
          </div>
          <div class="mb-3">
            <label class="form-label" style="color:var(--text-secondary);">Email</label>
            <input type="email" class="form-control" name="email" required>
          </div>
          <div class="mb-3">
            <label class="form-label" style="color:var(--text-secondary);">Department</label>
            <input type="text" class="form-control" name="department" required>
          </div>
          <button type="submit" class="btn btn-neon w-100">Submit Request</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.getElementById('requestAccessForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fd.append('action', 'requestAccess');
    fetch('/pfeeeee/PhtP/ajax/api.php', { method: 'POST', body: fd })
      .then(r => r.json())
      .then(d => {
        if(d.success) {
          alert('Access requested successfully! Wait for admin approval.');
          bootstrap.Modal.getInstance(document.getElementById('requestAccessModal')).hide();
        } else {
          alert(d.error || 'Failed to request access');
        }
      });
  });
</script>
<script>
let theme = 'dark';
function toggleTheme() {
    theme = theme === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-bs-theme', theme);
    document.getElementById('themeIconLogin').className = theme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
}
function showToast(message, type) {
    type = type || 'success';
    var container = document.getElementById('toastContainer');
    var toast = document.createElement('div');
    toast.className = 'toast-msg ' + type;
    toast.innerHTML = '<span>' + message + '</span>';
    container.appendChild(toast);
    setTimeout(function() { toast.style.opacity = '0'; setTimeout(function() { toast.remove(); }, 300); }, 3500);
}
function initBackgroundAnimation() {
    var canvas = document.getElementById('particleCanvas');
    if (!canvas) return;
    var ctx = canvas.getContext('2d');
    var pts = [], W, H;
    function resize() { W = canvas.width = window.innerWidth; H = canvas.height = window.innerHeight; }
    resize();
    window.addEventListener('resize', function() { resize(); initPts(); });
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
            var isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.r, 0, Math.PI * 2);
            ctx.fillStyle = isDark ? 'rgba(255,255,255,0.9)' : 'rgba(2,92,132,0.9)';
            ctx.shadowBlur = 10;
            ctx.shadowColor = isDark ? '#FFFFFF' : '#025C84';
            ctx.fill();
            ctx.shadowBlur = 0;
        };
    }
    function initPts() {
        pts = [];
        var n = Math.min(Math.floor(W * H / 8000), 130);
        for (var i = 0; i < n; i++) pts.push(new Pt());
    }
    initPts();
    var LDIST = 155;
    function drawLinks() {
        var isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        var base = isDark ? '255,255,255,' : '2,92,132,';
        for (var i = 0; i < pts.length; i++) {
            for (var j = i + 1; j < pts.length; j++) {
                var dx = pts[i].x - pts[j].x;
                var dy = pts[i].y - pts[j].y;
                var d = Math.sqrt(dx * dx + dy * dy);
                if (d < LDIST) {
                    ctx.beginPath();
                    ctx.moveTo(pts[i].x, pts[i].y);
                    ctx.lineTo(pts[j].x, pts[j].y);
                    ctx.strokeStyle = 'rgba(' + base + (1 - d / LDIST) * 0.45 + ')';
                    ctx.lineWidth = 0.8;
                    ctx.stroke();
                }
            }
        }
    }
    function loop() {
        ctx.clearRect(0, 0, W, H);
        pts.forEach(function(p) { p.step(); p.draw(); });
        drawLinks();
        requestAnimationFrame(loop);
    }
    loop();
}
document.documentElement.setAttribute('data-bs-theme', theme);
document.getElementById('themeIconLogin').className = theme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
initBackgroundAnimation();
</script>
</body>
</html>
