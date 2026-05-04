<nav class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div>
      <img src="../assets/img/logo-light.png" alt="Logo" class="sidebar-logo" id="sidebarLogo" />
      <img src="../assets/img/logo-dark.png" alt="Logo" class="sidebar-logo" id="sidebarLogoDark" />
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
        ['view' => 'submit-request', 'label' => 'submitRequest', 'icon' => 'fa-plus-circle'],
        ['view' => 'track-status', 'label' => 'trackStatus', 'icon' => 'fa-search'],
        ['view' => 'messages', 'label' => 'messages', 'icon' => 'fa-comments'],
        ['view' => 'training', 'label' => 'training', 'icon' => 'fa-graduation-cap'],
        ['view' => 'assist', 'label' => 'assist', 'icon' => 'fa-life-ring'],
        ['view' => 'history', 'label' => 'history', 'icon' => 'fa-clock-rotate-left']
      ],
      'support' => [
        ['view' => 'dashboard', 'label' => 'dashboard', 'icon' => 'fa-gauge-high'],
        ['view' => 'requests', 'label' => 'requests', 'icon' => 'fa-inbox'],
        ['view' => 'training', 'label' => 'training', 'icon' => 'fa-graduation-cap'],
        ['view' => 'assist', 'label' => 'assist', 'icon' => 'fa-life-ring'],
        ['view' => 'history', 'label' => 'history', 'icon' => 'fa-clock-rotate-left'],
        ['view' => 'users', 'label' => 'userManagement', 'icon' => 'fa-users'],
        ['view' => 'technical-issues', 'label' => 'technicalProblems', 'icon' => 'fa-bug'],
        ['view' => 'distribution-boxes', 'label' => 'distributionBoxes', 'icon' => 'fa-box'],
        ['view' => 'correspondences', 'label' => 'correspondences', 'icon' => 'fa-envelope']
      ],
      'admin' => [
        ['view' => 'dashboard', 'label' => 'dashboard', 'icon' => 'fa-gauge-high'],
        ['view' => 'users', 'label' => 'userManagement', 'icon' => 'fa-users'],
        ['view' => 'requests', 'label' => 'requests', 'icon' => 'fa-inbox'],
        ['view' => 'training', 'label' => 'training', 'icon' => 'fa-graduation-cap'],
        ['view' => 'assist', 'label' => 'assist', 'icon' => 'fa-life-ring'],
        ['view' => 'history', 'label' => 'history', 'icon' => 'fa-clock-rotate-left'],
        ['view' => 'technical-issues', 'label' => 'technicalProblems', 'icon' => 'fa-bug'],
        ['view' => 'distribution-boxes', 'label' => 'distributionBoxes', 'icon' => 'fa-box'],
        ['view' => 'correspondences', 'label' => 'correspondences', 'icon' => 'fa-envelope'],
        ['view' => 'references', 'label' => 'referentials', 'icon' => 'fa-book'],
        ['view' => 'customization', 'label' => 'systemSettings', 'icon' => 'fa-gear']
      ]
    ];
    $items = $sidebarItems[$role] ?? $sidebarItems['user'];
    $currentView = $currentView ?? 'dashboard';
    foreach ($items as $item):
      $active = ($currentView === $item['view']) ? 'active' : '';
    ?>
      <li class="nav-item">
        <a class="nav-link <?= $active ?>" onclick="RecLise.renderContent('<?= $item['view'] ?>')" data-view="<?= $item['view'] ?>">
          <i class="fas <?= $item['icon'] ?>"></i>
          <span><?php echo t($item['label']); ?></span>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
  <div class="sidebar-footer" id="sidebarFooter">
    <button class="btn-logout" onclick="RecLise.handleLogout()" title="<?php echo t('logout'); ?>">
      <i class="fas fa-sign-out-alt"></i>
      <span><?php echo t('logout'); ?></span>
    </button>
  </div>
</nav>
