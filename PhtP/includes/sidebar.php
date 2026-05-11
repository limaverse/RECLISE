<nav class="sidebar" id="sidebar">
   <div class="sidebar-header">
    <div>
       <img src="/pfeeeee/Untitled-1rrrr.png" alt="Logo" class="sidebar-logo" id="sidebarLogo" />
       <img src="/pfeeeee/Untitled-1rg.png" alt="Logo" class="sidebar-logo" id="sidebarLogoDark" style="display:none;" />
      <small id="sidebarRoleLabel"><?php
        $roleLabels = ['admin' => 'Admin', 'support' => 'Support', 'user' => 'User'];
        echo $roleLabels[$_SESSION['role']] ?? 'User';
      ?></small>
    </div>
  </div>
  <ul class="nav flex-column" id="sidebarNav">
    <?php
    $role = $_SESSION['role'];
    $sidebarItems = [
      'user' => [
        ['view' => 'dashboard', 'label' => 'dashboard', 'icon' => 'fa-gauge-high'],
        ['view' => 'submit-request', 'label' => 'submit', 'icon' => 'fa-paper-plane'],
        ['view' => 'track-status', 'label' => 'trackStatus', 'icon' => 'fa-truck-fast'],
        ['view' => 'messages', 'label' => 'messages', 'icon' => 'fa-envelope'],
        ['view' => 'training', 'label' => 'trainingCat', 'icon' => 'fa-graduation-cap'],
        ['view' => 'assist', 'label' => 'assist', 'icon' => 'fa-headset'],
        ['view' => 'history', 'label' => 'history', 'icon' => 'fa-clock-rotate-left']
      ],
      'support' => [
        ['view' => 'dashboard', 'label' => 'dashboard', 'icon' => 'fa-gauge-high'],
        ['view' => 'incoming-requests', 'label' => 'incomingRequests', 'icon' => 'fa-inbox'],
        ['view' => 'submit-request', 'label' => 'submitToAdmin', 'icon' => 'fa-paper-plane'],
        ['view' => 'messages', 'label' => 'messages', 'icon' => 'fa-comments'],
        ['view' => 'training', 'label' => 'trainingCat', 'icon' => 'fa-graduation-cap'],
        ['view' => 'assist', 'label' => 'assist', 'icon' => 'fa-headset'],
        ['view' => 'analytics', 'label' => 'analytics', 'icon' => 'fa-chart-line'],
        ['view' => 'customize-dashboards', 'label' => 'customizeUserDashboards', 'icon' => 'fa-palette'],
        ['view' => 'escalation-history', 'label' => 'escalationHistory', 'icon' => 'fa-history'],
        ['view' => 'history', 'label' => 'history', 'icon' => 'fa-clock-rotate-left']
      ],
      'admin' => [
        ['view' => 'dashboard', 'label' => 'dashboard', 'icon' => 'fa-gauge-high'],
        ['view' => 'users', 'label' => 'userManagement', 'icon' => 'fa-users-gear'],
        ['view' => 'escalated-requests', 'label' => 'escalatedRequests', 'icon' => 'fa-arrow-up-right-dots'],
        ['view' => 'correspondences', 'label' => 'correspondences', 'icon' => 'fa-file-lines'],
        ['view' => 'references', 'label' => 'referentials', 'icon' => 'fa-tags'],
        ['view' => 'distribution-boxes', 'label' => 'distributionBoxes', 'icon' => 'fa-boxes-stacked'],
        ['view' => 'technical-issues', 'label' => 'technicalProblems', 'icon' => 'fa-bug'],
        ['view' => 'customization', 'label' => 'commonCustomization', 'icon' => 'fa-sliders'],
        ['view' => 'statistics', 'label' => 'statistics', 'icon' => 'fa-chart-pie'],
        ['view' => 'audit-logs', 'label' => 'auditLogs', 'icon' => 'fa-scroll'],
        ['view' => 'system-settings', 'label' => 'systemSettings', 'icon' => 'fa-gear']
      ]
    ];
    $items = $sidebarItems[$role] ?? $sidebarItems['user'];
    foreach ($items as $item):
      $active = ($currentView === $item['view']) ? 'active' : '';
      $path = "/pfeeeee/PhtP/{$role}/{$item['view']}.php";
    ?>
      <li class="nav-item">
        <a class="nav-link <?= $active ?>" href="<?= $path ?>">
          <i class="fas <?= $item['icon'] ?>"></i>
          <span><?php echo t($item['label']); ?></span>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
  <div class="sidebar-footer" id="sidebarFooter">
    <button class="btn-logout" onclick="RecLise.handleLogout()" title="<?php echo t('logout'); ?>">
      <i class="fas fa-right-from-bracket"></i>
      <span><?php echo t('logout'); ?></span>
    </button>
  </div>
</nav>
