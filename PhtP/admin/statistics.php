<?php
$currentView = 'statistics';
require_once '../includes/header.php';

// Fetch statistics
$totalRequests = $pdo->query("SELECT COUNT(*) FROM requests")->fetchColumn();
$byType = $pdo->query("SELECT type, COUNT(*) as cnt FROM requests GROUP BY type")->fetchAll();
$byCategory = $pdo->query("SELECT category, COUNT(*) as cnt FROM requests GROUP BY category")->fetchAll();
$byDept = $pdo->query("SELECT u.department, COUNT(*) as cnt FROM requests r LEFT JOIN users u ON r.user_id = u.id GROUP BY u.department")->fetchAll();
?>

<div class="section-header">
  <h2><i class="fas fa-chart-bar me-2"></i><?php echo t('statistics'); ?></h2>
</div>

<div class="row g-3">
  <!-- By Type -->
  <div class="col-md-4">
    <div class="glass-card" style="padding:20px;">
      <h5 class="mb-4"><i class="fas fa-chart-pie me-2" style="color:var(--icon-color);"></i><?php echo t('requestsByType'); ?></h5>
      <?php
      $typeMap = ['request' => ['color' => '#3B82F6', 'label' => t('request')], 'complaint' => ['color' => '#EF4444', 'label' => t('complaint')]];
      foreach ($typeMap as $type => $info):
        $cnt = 0;
        foreach ($byType as $row) { if ($row['type'] === $type) { $cnt = $row['cnt']; break; } }
        $pct = $totalRequests > 0 ? round(($cnt / $totalRequests) * 100) : 0;
      ?>
        <div class="mb-3">
          <div style="display:flex;justify-content:space-between;font-size:0.88rem;margin-bottom:0.25rem;">
            <span><?= $info['label'] ?></span>
            <span><?= $cnt ?> (<?= $pct ?>%)</span>
          </div>
          <div class="progress-custom"><div class="bar-fill" style="width:<?= $pct ?>%;background:<?= $info['color'] ?>;"></div></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- By Category -->
  <div class="col-md-4">
    <div class="glass-card" style="padding:20px;">
      <h5 class="mb-4"><i class="fas fa-tags me-2" style="color:var(--icon-color);"></i><?php echo t('requestsByCategory'); ?></h5>
      <?php
      $catMap = ['technical' => ['color' => '#F59E0B', 'label' => t('technical')], 'access' => ['color' => '#10B981', 'label' => t('access')], 'training' => ['color' => '#8B5CF6', 'label' => t('training')]];
      foreach ($catMap as $cat => $info):
        $cnt = 0;
        foreach ($byCategory as $row) { if ($row['category'] === $cat) { $cnt = $row['cnt']; break; } }
        $pct = $totalRequests > 0 ? round(($cnt / $totalRequests) * 100) : 0;
      ?>
        <div class="mb-3">
          <div style="display:flex;justify-content:space-between;font-size:0.88rem;margin-bottom:0.25rem;">
            <span><?= $info['label'] ?></span>
            <span><?= $cnt ?> (<?= $pct ?>%)</span>
          </div>
          <div class="progress-custom"><div class="bar-fill" style="width:<?= $pct ?>%;background:<?= $info['color'] ?>;"></div></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- By Department -->
  <div class="col-md-4">
    <div class="glass-card" style="padding:20px;">
      <h5 class="mb-4"><i class="fas fa-building me-2" style="color:var(--icon-color);"></i><?php echo t('requestsByDept'); ?></h5>
      <?php foreach ($byDept as $row):
        $dept = $row['department'] ? $row['department'] : 'Unknown';
        $cnt = $row['cnt'];
        $pct = $totalRequests > 0 ? round(($cnt / $totalRequests) * 100) : 0;
        ?>
        <div class="mb-3">
          <div style="display:flex;justify-content:space-between;font-size:0.88rem;margin-bottom:0.25rem;">
            <span><?= htmlspecialchars($dept) ?></span>
            <span><?= $cnt ?> (<?= $pct ?>%)</span>
          </div>
          <div class="progress-custom"><div class="bar-fill" style="width:<?= $pct ?>%;background:var(--neon-accent);"></div></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
