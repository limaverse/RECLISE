<div class="topbar" id="topbar">
  <button class="btn-hamburger" id="btnHamburger" onclick="RecLise.toggleSidebar()">
    <i class="fas fa-bars"></i>
  </button>
  <div class="topbar-title" id="topbarTitle"><?php echo t($currentView ?? 'dashboard'); ?></div>
  <div class="topbar-actions">
    <div class="lang-dropdown">
      <button class="btn-icon" onclick="RecLise.toggleLangMenu()" title="Language">
        <i class="fas fa-globe"></i>
      </button>
      <div class="lang-dropdown-menu" id="langMenu">
        <div class="lang-item" data-lang="fr" onclick="RecLise.setLanguage('fr')">Français (FR)</div>
        <div class="lang-item" data-lang="en" onclick="RecLise.setLanguage('en')">English (EN)</div>
        <div class="lang-item" data-lang="ar" onclick="RecLise.setLanguage('ar')">العربية (AR)</div>
      </div>
    </div>
    
    <div class="notif-dropdown" style="position:relative;">
      <button class="btn-icon" onclick="RecLise.toggleNotifMenu()" title="Notifications" style="position:relative;">
        <i class="fas fa-bell"></i>
        <span id="notifBadge" class="badge rounded-pill bg-danger" style="position:absolute;top:0;right:0;font-size:0.6rem;display:none;">0</span>
      </button>
      <div class="lang-dropdown-menu" id="notifMenu" style="width:300px;right:0;left:auto;max-height:400px;overflow-y:auto;padding:0;">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
          <strong style="color:var(--text-primary)">Notifications</strong>
          <span style="font-size:0.8rem;cursor:pointer;color:var(--brand-accent);" onclick="RecLise.markNotificationsRead()">Mark all read</span>
        </div>
        <div id="notifList">
          <div class="p-3 text-center text-secondary">Loading...</div>
        </div>
      </div>
    </div>

    <button class="btn-icon" onclick="RecLise.toggleTheme()" title="Toggle theme" id="btnThemeToggle">
      <i class="fas <?php echo ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'fa-sun' : 'fa-moon'; ?>" id="themeIcon"></i>
    </button>
  </div>
</div>
