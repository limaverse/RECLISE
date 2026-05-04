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
        <div class="lang-item" data-lang="fr" onclick="RecLise.setLanguage('fr')">Français</div>
        <div class="lang-item" data-lang="en" onclick="RecLise.setLanguage('en')">English</div>
        <div class="lang-item" data-lang="ar" onclick="RecLise.setLanguage('ar')">العربية</div>
      </div>
    </div>
    <button class="btn-icon" onclick="RecLise.toggleTheme()" title="Toggle theme" id="btnThemeToggle">
      <i class="fas <?php echo ($_SESSION['theme'] ?? 'dark') === 'dark' ? 'fa-sun' : 'fa-moon'; ?>" id="themeIcon"></i>
    </button>
  </div>
</div>
