<?php
$currentView = 'dashboard';
require_once '../includes/header.php';

// Fetch stats for dashboard (assigned to current support)
$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM requests WHERE assigned_to = ?");
$stmtTotal->execute([$_SESSION['user_id']]);
$totalCount = $stmtTotal->fetchColumn();

$stmtEscalated = $pdo->prepare("SELECT COUNT(*) FROM requests WHERE assigned_to = ? AND escalated_to_admin = 1");
$stmtEscalated->execute([$_SESSION['user_id']]);
$escalatedCount = $stmtEscalated->fetchColumn();

$stmtResolved = $pdo->prepare("SELECT COUNT(*) FROM requests WHERE assigned_to = ? AND (status = 'resolved' OR status = 'closed')");
$stmtResolved->execute([$_SESSION['user_id']]);
$resolvedCount = $stmtResolved->fetchColumn();

$stmtInProgress = $pdo->prepare("SELECT COUNT(*) FROM requests WHERE assigned_to = ? AND status = 'in_progress'");
$stmtInProgress->execute([$_SESSION['user_id']]);
$inProgressCount = $stmtInProgress->fetchColumn();

// Recent activity (assigned requests)
$stmtRecent = $pdo->prepare("SELECT * FROM requests WHERE assigned_to = ? ORDER BY updated_at DESC LIMIT 3");
$stmtRecent->execute([$_SESSION['user_id']]);
$recentRequests = to_camel_all($stmtRecent->fetchAll());
?>

<div class="section-header">
  <h2><?php echo t('welcomeBack'); ?> <?= htmlspecialchars($_SESSION['full_name']) ?></h2>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon">
        <svg width="40" height="40" viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:block;">
          <path opacity="0.35" d="M49.3 51.1998H22.7C19.5517 51.1998 17 48.6481 17 45.4998V26.4998C17 23.3515 19.5517 20.7998 22.7 20.7998H49.3C52.4483 20.7998 55 23.3515 55 26.4998V45.4998C55 48.6481 52.4483 51.1998 49.3 51.1998Z" fill="var(--icon-color)" />
          <path d="M32.0898 35.5533L17 26.4998C17 23.3515 19.5517 20.7998 22.7 20.7998H49.3C52.4483 20.7998 55 23.3515 55 26.4998L39.9102 35.5533C37.5029 36.9973 34.4971 36.9973 32.0898 35.5533Z" fill="var(--icon-color)" />
        </svg>
      </div>
      <div class="stat-value"><?= $totalCount ?></div>
      <div class="stat-label"><?php echo t('totalRequests'); ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon">
        <svg width="40" height="40" viewBox="305 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:block;">
          <path opacity="0.35" d="M341 58C353.15 58 363 48.1503 363 36C363 23.8497 353.15 14 341 14C328.85 14 319 23.8497 319 36C319 48.1503 328.85 58 341 58Z" fill="var(--icon-color)" />
          <path d="M333.729 38.2002C332.372 38.2002 331.69 39.837 332.645 40.8006L339.432 47.6514C340.296 48.5226 341.704 48.5226 342.569 47.6514L349.356 40.8006C350.311 39.837 349.626 38.2002 348.271 38.2002H333.729Z" fill="var(--icon-color)" />
          <path d="M343.2 40.3998C343.2 40.3998 343.2 25.7654 343.2 24.9998C343.2 23.7854 342.214 22.7998 341 22.7998C339.785 22.7998 338.8 23.7854 338.8 24.9998C338.8 25.7654 338.8 40.3998 338.8 40.3998H343.2Z" fill="var(--icon-color)" />
        </svg>
      </div>
      <div class="stat-value"><?= $escalatedCount ?></div>
      <div class="stat-label"><?php echo t('escalated'); ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon">
        <svg width="40" height="40" viewBox="204 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:block;">
          <path opacity="0.35" d="M240 35C244.418 35 248 31.4183 248 27C248 22.5817 244.418 19 240 19C235.582 19 232 22.5817 232 27C232 31.4183 235.582 35 240 35Z" fill="var(--icon-color)" />
          <path opacity="0.35" d="M225 39C227.761 39 230 36.7614 230 34C230 31.2386 227.761 29 225 29C222.239 29 220 31.2386 220 34C220 36.7614 222.239 39 225 39Z" fill="var(--icon-color)" />
          <path opacity="0.35" d="M255 39C257.761 39 260 36.7614 260 34C260 31.2386 257.761 29 255 29C252.239 29 250 31.2386 250 34C250 36.7614 252.239 39 255 39Z" fill="var(--icon-color)" />
          <path d="M246 53H234C230.686 53 228 49.866 228 46C228 42.134 230.686 39 234 39H246C249.314 39 252 42.134 252 46C252 49.866 249.314 53 246 53Z" fill="var(--icon-color)" />
          <path opacity="0.35" d="M259.102 43H220.744C218.124 43 216 45.124 216 47.744V48.258C216 50.876 218.124 53 220.744 53H259.102C261.806 53 264 50.806 264 48.1V47.898C264 45.194 261.806 43 259.102 43Z" fill="var(--icon-color)" />
        </svg>
      </div>
      <div class="stat-value"><?= $resolvedCount ?></div>
      <div class="stat-label"><?php echo t('resolved'); ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon">
        <svg width="40" height="40" viewBox="407 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:block;">
          <path opacity="0.35" d="M443.25 53C442.168 53 441.067 52.5943 440.236 51.7443C439.406 50.9136 427.409 40.9261 425.419 38.9557C420.86 34.3966 420.86 26.9977 425.419 22.4193C429.998 17.8602 437.397 17.8602 441.956 22.4193C442.439 22.9216 442.864 23.4625 443.25 24.0034L447.398 34.2324L443.25 53Z" fill="var(--icon-color)" />
          <path d="M443.25 53C444.332 53 445.433 52.5943 446.264 51.7443C447.094 50.9136 459.091 40.9261 461.081 38.9557C465.64 34.3966 465.64 26.9977 461.081 22.4193C456.502 17.8602 449.103 17.8602 444.544 22.4193C444.061 22.9216 443.636 23.4625 443.25 24.0034V36.3864V53Z" fill="var(--icon-color)" />
        </svg>
      </div>
      <div class="stat-value"><?= $inProgressCount ?></div>
      <div class="stat-label"><?php echo t('inProgress'); ?></div>
    </div>
  </div>
</div>

<div class="row g-3">
  <!-- Recent Activity -->
  <div class="col-md-6">
    <div class="glass-card" style="padding: 16px;">
      <h5 style="margin-bottom: 16px;"><?php echo t('recentActivity'); ?></h5>
      <?php if (empty($recentRequests)): ?>
        <p style="color:var(--text-secondary);"><?php echo t('noResults'); ?></p>
      <?php else: ?>
        <?php foreach ($recentRequests as $r): ?>
          <div class="activity-item">
            <div class="activity-dot" style="background:var(--neon-accent)"></div>
            <div>
              <div class="activity-text"><strong>#<?= $r['id'] ?></strong> — <?= htmlspecialchars($r['title']) ?></div>
              <div class="activity-time"><?= date('M d, H:i', strtotime($r['updatedAt'])) ?></div>
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
        <a href="incoming-requests.php" class="btn btn-outline-neon text-start"><i class="fas fa-inbox me-2"></i><?php echo t('incomingRequests'); ?></a>
        <a href="training.php" class="btn btn-outline-neon text-start"><i class="fas fa-graduation-cap me-2"></i><?php echo t('trainingCat'); ?></a>
        <a href="analytics.php" class="btn btn-outline-neon text-start"><i class="fas fa-chart-line me-2"></i><?php echo t('analytics'); ?></a>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
