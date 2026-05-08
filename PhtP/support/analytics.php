<?php
$currentView = 'analytics';
require_once '../includes/header.php';

$uid = $_SESSION['user_id'];
$reqs = to_camel_all($pdo->query("SELECT * FROM requests WHERE assigned_to = $uid ORDER BY created_at DESC")->fetchAll());
$total = count($reqs);
$resolved = count(array_filter($reqs, fn($r) => in_array($r['status'], ['resolved', 'closed'])));
$rate = $total > 0 ? round(($resolved / $total) * 100) : 0;

$statuses = ['new', 'in_progress', 'resolved', 'escalated'];
$statusCounts = array_map(fn($s) => count(array_filter($reqs, fn($r) => $r['status'] === $s)), $statuses);
$maxCount = max($statusCounts) ?: 1;
$colors = ['#F59E0B', '#3B82F6', '#10B981', '#EF4444'];
?>

<div class="section-header">
  <h2><i class="fas fa-chart-line me-2"></i><?php echo t('analytics'); ?></h2>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="stat-card">
      <div class="stat-icon">
        <svg width="40" height="40" viewBox="0 92 72 72" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:block;">
          <path d="M35.8946 112.578C35.8982 111.705 35.1896 110.996 34.3163 111L24.7927 111.043C23.5222 111.048 22.8905 112.584 23.7888 113.482L33.4108 123.104C34.3091 124.002 35.8445 123.371 35.8499 122.1L35.8946 112.578Z" fill="var(--icon-color)" />
          <path opacity="0.35" d="M25.1579 145H21.5789C19.6016 145 18 143.399 18 141.421V139.632C18 137.654 19.6016 136.053 21.5789 136.053H25.1579C27.1353 136.053 28.7368 137.654 28.7368 139.632V141.421C28.7368 143.399 27.1353 145 25.1579 145Z" fill="var(--icon-color)" />
          <path opacity="0.35" d="M37.6843 145H34.1053C32.1279 145 30.5264 143.399 30.5264 141.421V130.684C30.5264 128.707 32.1279 127.105 34.1053 127.105H37.6843C39.6616 127.105 41.2632 128.707 41.2632 130.684V141.421C41.2632 143.399 39.6616 145 37.6843 145Z" fill="var(--icon-color)" />
          <path opacity="0.35" d="M50.2106 145H46.6317C44.6543 145 43.0527 143.398 43.0527 141.421V114.579C43.0527 112.602 44.6543 111 46.6317 111H50.2106C52.188 111 53.7896 112.602 53.7896 114.579V141.421C53.7896 143.398 52.188 145 50.2106 145Z" fill="var(--icon-color)" />
          <path d="M21.1818 128.894C20.3676 128.894 19.5533 128.583 18.9324 127.962C17.6905 126.72 17.6905 124.705 18.9324 123.463L26.8848 115.511C28.1267 114.269 30.1417 114.269 31.3836 115.511C32.6255 116.753 32.6255 118.768 31.3836 120.01L23.4311 127.962C22.8102 128.583 21.996 128.894 21.1818 128.894Z" fill="var(--icon-color)" />
        </svg>
      </div>
      <div class="stat-value"><?= $rate ?>%</div>
      <div class="stat-label"><?php echo t('resolutionRate'); ?></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card">
      <div class="stat-icon">
        <svg width="40" height="40" viewBox="305 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:block;">
          <path opacity="0.35" d="M341 58C353.15 58 363 48.1503 363 36C363 23.8497 353.15 14 341 14C328.85 14 319 23.8497 319 36C319 48.1503 328.85 58 341 58Z" fill="var(--icon-color)" />
          <path d="M333.729 38.2002C332.372 38.2002 331.69 39.837 332.645 40.8006L339.432 47.6514C340.296 48.5226 341.704 48.5226 342.569 47.6514L349.356 40.8006C350.311 39.837 349.626 38.2002 348.271 38.2002H333.729Z" fill="var(--icon-color)" />
          <path d="M343.2 40.3998C343.2 40.3998 343.2 25.7654 343.2 24.9998C343.2 23.7854 342.214 22.7998 341 22.7998C339.785 22.7998 338.8 23.7854 338.8 24.9998C338.8 25.7654 338.8 40.3998 338.8 40.3998H343.2Z" fill="var(--icon-color)" />
        </svg>
      </div>
      <div class="stat-value">4.2 <?php echo t('hours'); ?></div>
      <div class="stat-label"><?php echo t('avgResponseTime'); ?></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card">
      <div class="stat-icon">
        <svg width="40" height="40" viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:block;">
          <path opacity="0.35" d="M49.3 51.1998H22.7C19.5517 51.1998 17 48.6481 17 45.4998V26.4998C17 23.3515 19.5517 20.7998 22.7 20.7998H49.3C52.4483 20.7998 55 23.3515 55 26.4998V45.4998C55 48.6481 52.4483 51.1998 49.3 51.1998Z" fill="var(--icon-color)" />
          <path d="M32.0898 35.5533L17 26.4998C17 23.3515 19.5517 20.7998 22.7 20.7998H49.3C52.4483 20.7998 55 23.3515 55 26.4998L39.9102 35.5533C37.5029 36.9973 34.4971 36.9973 32.0898 35.5533Z" fill="var(--icon-color)" />
        </svg>
      </div>
      <div class="stat-value"><?= $total ?></div>
      <div class="stat-label"><?php echo t('totalRequests'); ?></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card">
      <div class="stat-icon">
        <svg width="40" height="40" viewBox="102 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:block;">
          <path opacity="0.35" d="M121.163 27.5377C121.583 30.6599 123.224 37.8356 129.695 44.3067C136.166 50.7779 143.34 52.4193 146.464 52.8386C148.727 53.3278 151.181 52.7064 152.939 50.9479C155.689 48.1977 145.728 38.236 142.977 40.9862L141.223 42.7409L131.261 32.7792L133.014 31.0245C135.764 28.2743 125.802 18.3126 123.052 21.0627C121.294 22.8212 120.672 25.2748 121.163 27.5377Z" fill="var(--icon-color)" />
          <path d="M123.059 21.0572L133.018 31.017C135.763 28.265 135.763 23.8111 133.013 21.061C130.266 18.3146 125.811 18.3127 123.059 21.0572Z" fill="var(--icon-color)" />
          <path d="M142.981 40.9805L152.941 50.9403C155.686 48.1902 155.686 43.7344 152.936 40.9843C150.187 38.2379 145.733 38.236 142.981 40.9805Z" fill="var(--icon-color)" />
        </svg>
      </div>
      <div class="stat-value"><?= $resolved ?></div>
      <div class="stat-label"><?php echo t('resolved'); ?></div>
    </div>
  </div>
</div>

<div class="glass-card">
  <h5 style="margin-bottom:20px;"><?php echo t('trackStatus'); ?></h5>
  <div class="bar-chart">
    <?php foreach ($statuses as $i => $s): ?>
      <div class="bar-col">
        <div class="bar-value"><?= $statusCounts[$i] ?></div>
        <div class="bar" style="height:<?= ($statusCounts[$i] / $maxCount) * 160 ?>px;background:<?= $colors[$i] ?>;"></div>
        <div class="bar-label"><?php echo t('status' . ucfirst(str_replace('_', '', $s))); ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
