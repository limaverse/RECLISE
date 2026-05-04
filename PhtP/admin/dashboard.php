<?php
$currentView = 'dashboard';
require_once '../includes/header.php';

// Fetch stats for dashboard
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalReqs = $pdo->query("SELECT COUNT(*) FROM requests")->fetchColumn();
$resolved = $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'resolved' OR status = 'closed'")->fetchColumn();
$rate = $totalReqs > 0 ? round(($resolved / $totalReqs) * 100) : 0;

// Recent activity (Audit Logs)
$stmtLogs = $pdo->query("SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 5");
$recentLogs = to_camel_all($stmtLogs->fetchAll());
?>

<div class="section-header">
  <h2><?php echo t('welcomeBack'); ?> <?= htmlspecialchars($_SESSION['full_name']) ?></h2>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon">
        <svg width="40" height="40" viewBox="509 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:block;">
          <path opacity="0.35" d="M544.947 37.0526C550.761 37.0526 555.474 32.3398 555.474 26.5263C555.474 20.7128 550.761 16 544.947 16C539.134 16 534.421 20.7128 534.421 26.5263C534.421 32.3398 539.134 37.0526 544.947 37.0526Z" fill="var(--icon-color)" />
          <path d="M557.579 43.3687H532.316C528.827 43.3687 526 46.196 526 49.6844C526 53.1729 528.827 56.0002 532.316 56.0002H557.579C561.067 56.0002 563.895 53.1729 563.895 49.6844C563.895 46.196 561.067 43.3687 557.579 43.3687Z" fill="var(--icon-color)" />
        </svg>
      </div>
      <div class="stat-value"><?= $totalUsers ?></div>
      <div class="stat-label"><?php echo t('totalUsers'); ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon">
        <svg width="40" height="40" viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:block;">
          <path opacity="0.35" d="M49.3 51.1998H22.7C19.5517 51.1998 17 48.6481 17 45.4998V26.4998C17 23.3515 19.5517 20.7998 22.7 20.7998H49.3C52.4483 20.7998 55 23.3515 55 26.4998V45.4998C55 48.6481 52.4483 51.1998 49.3 51.1998Z" fill="var(--icon-color)" />
          <path d="M32.0898 35.5533L17 26.4998C17 23.3515 19.5517 20.7998 22.7 20.7998H49.3C52.4483 20.7998 55 23.3515 55 26.4998L39.9102 35.5533C37.5029 36.9973 34.4971 36.9973 32.0898 35.5533Z" fill="var(--icon-color)" />
        </svg>
      </div>
      <div class="stat-value"><?= $totalReqs ?></div>
      <div class="stat-label"><?php echo t('totalRequests'); ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon">
        <svg width="40" height="40" viewBox="102 92 72 72" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:block;">
          <path opacity="0.35" d="M153.984 136.87L144.009 126.874C143.186 126.05 141.854 126.047 141.027 126.865L140.69 127.199L131.174 117.683C130.605 115.977 129.531 114.486 128.093 113.407L124.868 110.99C123.978 110.323 122.733 110.41 121.947 111.197L121.197 111.948C120.409 112.734 120.322 113.978 120.989 114.869L123.407 118.093C124.486 119.532 125.977 120.605 127.682 121.174L137.183 130.675L136.881 130.973C136.05 131.795 136.046 133.138 136.872 133.965L146.872 143.984C148.836 145.948 152.02 145.948 153.986 143.984C155.948 142.02 155.948 138.836 153.984 136.87Z" fill="var(--icon-color)" />
          <path d="M122.02 143.98C123.991 145.951 127.188 145.951 129.159 143.98L144.982 128.189L137.877 121.016L122.02 136.843C120.049 138.812 120.049 142.009 122.02 143.98Z" fill="var(--icon-color)" />
          <path d="M148.848 122.09C147.401 123.537 144.999 123.448 143.668 121.822C142.513 120.408 142.759 118.303 144.051 117.011L147.998 113.064C148.728 112.334 148.392 111.067 147.389 110.826C145.927 110.477 144.349 110.433 142.712 110.788C138.646 111.671 135.406 115.002 134.679 119.097C133.388 126.377 139.623 132.613 146.903 131.321C151.001 130.595 154.33 127.355 155.211 123.287C155.566 121.65 155.524 120.071 155.173 118.61C154.932 117.606 153.666 117.271 152.937 117.999C151.186 119.754 149.232 121.705 148.848 122.09Z" fill="var(--icon-color)" />
        </svg>
      </div>
      <div class="stat-value"><?= $rate ?>%</div>
      <div class="stat-label"><?php echo t('resolutionRate'); ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon">
        <svg width="40" height="40" viewBox="204 92 72 72" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:block;">
          <path opacity="0.35" d="M236.06 120.119H224.238V143.762H236.06V120.119Z" fill="var(--icon-color)" />
          <path opacity="0.35" d="M255.763 145.732H238.03V114.208C238.03 112.031 239.794 110.267 241.971 110.267H253.793C255.97 110.267 257.733 112.031 257.733 114.208V143.762C257.733 144.85 256.851 145.732 255.763 145.732Z" fill="var(--icon-color)" />
          <path d="M253.792 118.148H249.852V114.208H253.792V118.148Z" fill="var(--icon-color)" />
          <path d="M245.911 118.148H241.971V114.208H245.911V118.148Z" fill="var(--icon-color)" />
          <path d="M253.792 126.029H249.852V122.089H253.792V126.029Z" fill="var(--icon-color)" />
          <path d="M245.911 126.029H241.971V122.089H245.911V126.029Z" fill="var(--icon-color)" />
          <path d="M253.792 133.911H249.852V129.97H253.792V133.911Z" fill="var(--icon-color)" />
          <path d="M245.911 133.911H241.971V129.97H245.911V133.911Z" fill="var(--icon-color)" />
          <path d="M253.792 141.792H249.852V137.851H253.792V141.792Z" fill="var(--icon-color)" />
          <path d="M245.911 141.792H241.971V137.851H245.911V141.792Z" fill="var(--icon-color)" />
          <path d="M234.089 116.178H226.208C224.031 116.178 222.268 117.941 222.268 120.118V143.762C222.268 144.85 223.15 145.732 224.238 145.732H238.03V120.118C238.03 117.941 236.267 116.178 234.089 116.178ZM234.089 141.792H226.208V137.851H234.089V141.792ZM234.089 133.91H226.208V129.97H234.089V133.91ZM234.089 126.029H226.208V122.089H234.089V126.029Z" fill="var(--icon-color)" />
        </svg>
      </div>
      <div class="stat-value">98%</div>
      <div class="stat-label"><?php echo t('slaCompliance'); ?></div>
    </div>
  </div>
</div>

<div class="row g-3">
  <!-- Recent Activity -->
  <div class="col-md-6">
    <div class="glass-card" style="padding: 16px;">
      <h5 style="margin-bottom: 16px;"><?php echo t('recentActivity'); ?></h5>
      <?php if (empty($recentLogs)): ?>
        <p style="color:var(--text-secondary);"><?php echo t('noResults'); ?></p>
      <?php else: ?>
        <?php foreach ($recentLogs as $log): ?>
          <div class="activity-item">
            <div class="activity-dot" style="background:var(--neon-accent)"></div>
            <div>
              <div class="activity-text"><strong><?= htmlspecialchars($log['action']) ?></strong> — <?= htmlspecialchars($log['details']) ?></div>
              <div class="activity-time"><?= date('M d, H:i', strtotime($log['createdAt'])) ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="col-md-6">
    <div class="glass-card" style="padding: 16px;">
      <h5 style="margin-bottom: 16px;"><?php echo t('quickActions'); ?></h5>
      <div class="d-grid gap-2">
        <a href="users.php" class="btn btn-outline-neon text-start"><i class="fas fa-users-gear me-2"></i><?php echo t('userManagement'); ?></a>
        <a href="escalated-requests.php" class="btn btn-outline-neon text-start"><i class="fas fa-arrow-up-right-dots me-2"></i><?php echo t('escalatedRequests'); ?></a>
        <a href="statistics.php" class="btn btn-outline-neon text-start"><i class="fas fa-chart-pie me-2"></i><?php echo t('statistics'); ?></a>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
