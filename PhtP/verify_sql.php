<?php
$sqlFile = __DIR__ . '/../sqlpfe.sql';
$dbHost = 'localhost';
$dbName = 'reclise_db';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

$sqlContent = file_get_contents($sqlFile);
preg_match_all('/CREATE TABLE IF NOT EXISTS (\w+)\s*\((.*?)\)\s*ENGINE=\w+;/s', $sqlContent, $tableMatches, PREG_SET_ORDER);

$expectedSchema = [];
foreach ($tableMatches as $tm) {
    $tableName = $tm[1];
    $body = $tm[2];
    $columns = [];
    $lines = explode("\n", $body);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        if (preg_match('/^(CONSTRAINT|KEY|UNIQUE|INDEX|FOREIGN|PRIMARY\s+KEY)/i', $line)) continue;
        if (preg_match('/^(`?\w+`?)\s+(.+)$/', $line, $colMatch)) {
            $colName = trim($colMatch[1], '`');
            $colDef = $colMatch[2];
            $isPk = preg_match('/PRIMARY\s+KEY/i', $colDef);
            if (strtoupper($colDef) === 'AUTO_INCREMENT PRIMARY KEY') {
                $columns[$colName] = ['type' => 'INT', 'nullable' => false, 'is_auto' => true];
                continue;
            }
            $isAuto = preg_match('/AUTO_INCREMENT/i', $colDef);
            $type = preg_replace('/\s*(NOT NULL|NULL|DEFAULT|UNIQUE|AUTO_INCREMENT|PRIMARY\s+KEY|ON\s+UPDATE|REFERENCES|ON\s+DELETE|FOREIGN\s+KEY).*/i', '', $colDef);
            $type = trim($type);
            $isNullable = !$isPk && !preg_match('/NOT\s+NULL/i', $colDef);
            $columns[$colName] = ['type' => strtoupper($type), 'nullable' => $isNullable, 'is_auto' => (bool)$isAuto];
        }
    }
    $expectedSchema[$tableName] = $columns;
}

$actualSchema = [];
$stmt = $pdo->query("SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, EXTRA FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '$dbName' ORDER BY TABLE_NAME, ORDINAL_POSITION");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $tn = $row['TABLE_NAME'];
    if (in_array($tn, ['v_request_stats', 'v_requests_detail', 'v_requests_by_department', 'v_training_summary'])) continue;
    $actualSchema[$tn][$row['COLUMN_NAME']] = [
        'type' => strtoupper($row['COLUMN_TYPE']),
        'nullable' => $row['IS_NULLABLE'] === 'YES',
        'is_auto' => strpos($row['EXTRA'], 'auto_increment') !== false,
    ];
}

$actualIndexes = [];
$stmt = $pdo->query("SELECT TABLE_NAME, INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = '$dbName' AND INDEX_NAME != 'PRIMARY'");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) $actualIndexes[$row['TABLE_NAME']][] = $row['INDEX_NAME'];
$actualIndexes = array_map('array_unique', $actualIndexes);

$stmt = $pdo->query("SHOW FULL TABLES WHERE TABLE_TYPE = 'VIEW'");
$actualViews = [];
while ($row = $stmt->fetch(PDO::FETCH_NUM)) $actualViews[] = $row[0];

$expectedIndexes = [];
preg_match_all('/CREATE INDEX (\w+)\s+ON\s+(\w+)/', $sqlContent, $idxMatches, PREG_SET_ORDER);
foreach ($idxMatches as $im) $expectedIndexes[$im[2]][] = $im[1];

$expectedViews = ['v_request_stats', 'v_requests_detail', 'v_requests_by_department', 'v_training_summary'];

function normalizeType($type) {
    $type = strtoupper(trim($type));
    $type = preg_replace('/VARCHAR\(\d+\)/', 'VARCHAR', $type);
    $type = preg_replace('/INT\(\d+\)/', 'INT', $type);
    $type = preg_replace('/TINYINT\(\d+\)/', 'TINYINT', $type);
    if (preg_match('/ENUM\((.*?)\)/', $type, $m)) {
        $values = array_map('trim', explode(',', $m[1]));
        $type = 'ENUM(' . implode(',', $values) . ')';
    }
    return $type;
}

$issues = [];
$passChecks = 0;
$totalChecks = 0;

foreach ($expectedSchema as $tbl => $expectedCols) {
    if (!isset($actualSchema[$tbl])) {
        $issues[] = ['type' => 'MISSING_TABLE', 'table' => $tbl, 'detail' => "Table <code>$tbl</code> does not exist in database"];
        continue;
    }
    $actualCols = $actualSchema[$tbl];
    foreach ($expectedCols as $col => $exp) {
        $totalChecks++;
        if (!isset($actualCols[$col])) {
            $issues[] = ['type' => 'MISSING_COLUMN', 'table' => $tbl, 'column' => $col, 'detail' => "Column <code>$tbl.$col</code> missing from database"];
        } else {
            $act = $actualCols[$col];
            $expType = normalizeType($exp['type']);
            $actType = normalizeType($act['type']);
            $typeMatch = ($expType === $actType);
            $nullMatch = ($exp['nullable'] === $act['nullable']);
            $autoMatch = ($exp['is_auto'] === $act['is_auto']);
            if (!$typeMatch || !$nullMatch || !$autoMatch) {
                $detail = "Column <code>$tbl.$col</code>: ";
                $parts = [];
                if (!$typeMatch) $parts[] = "type expected <code>$expType</code>, got <code>$actType</code>";
                if (!$nullMatch) $parts[] = "null expected <code>" . ($exp['nullable'] ? 'NULL' : 'NOT NULL') . "</code>, got <code>" . ($act['nullable'] ? 'NULL' : 'NOT NULL') . "</code>";
                if (!$autoMatch && $exp['is_auto']) $parts[] = "expected AUTO_INCREMENT";
                $detail .= implode(', ', $parts);
                $issues[] = ['type' => 'MISMATCH', 'table' => $tbl, 'column' => $col, 'detail' => $detail];
            } else {
                $passChecks++;
            }
        }
    }
    foreach ($actualCols as $col => $act) {
        if (!isset($expectedCols[$col])) {
            $issues[] = ['type' => 'EXTRA_COLUMN', 'table' => $tbl, 'column' => $col, 'detail' => "Column <code>$tbl.$col</code> exists in DB but not in sqlpfe.sql"];
        }
    }
}

foreach ($expectedIndexes as $tbl => $idxs) {
    foreach ($idxs as $idx) {
        $totalChecks++;
        if (isset($actualIndexes[$tbl]) && in_array($idx, $actualIndexes[$tbl])) $passChecks++;
        else $issues[] = ['type' => 'MISSING_INDEX', 'table' => $tbl, 'detail' => "Index <code>$idx</code> on <code>$tbl</code> missing"];
    }
}

foreach ($expectedViews as $view) {
    $totalChecks++;
    if (in_array($view, $actualViews)) $passChecks++;
    else $issues[] = ['type' => 'MISSING_VIEW', 'table' => $view, 'detail' => "View <code>$view</code> missing"];
}

$failChecks = count($issues);
$totalTables = count($expectedSchema);
$totalCols = array_sum(array_map('count', $expectedSchema));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Schema Verifier — RecLise</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',-apple-system,sans-serif;background:#0f172a;color:#e2e8f0;padding:20px}
        h1{color:#818cf8;margin-bottom:8px;font-size:1.5rem}
        .subtitle{color:#94a3b8;margin-bottom:24px;font-size:.9rem}
        .summary{display:flex;gap:16px;flex-wrap:wrap;margin-bottom:30px}
        .summary-card{background:#1e293b;border:1px solid #334155;border-radius:12px;padding:16px 24px;min-width:180px}
        .summary-card .count{font-size:2rem;font-weight:700}
        .summary-card .label{color:#94a3b8;font-size:.82rem;margin-top:4px}
        .ok{color:#34d399}.err{color:#f87171}
        table{width:100%;border-collapse:collapse;margin-bottom:30px;background:#1e293b;border-radius:12px;overflow:hidden}
        th,td{padding:10px 16px;text-align:left;font-size:.88rem;border-bottom:1px solid #334155}
        th{background:#334155;color:#818cf8;font-weight:600;text-transform:uppercase;font-size:.75rem;letter-spacing:.5px}
        .badge{padding:2px 8px;border-radius:6px;font-size:.75rem;font-weight:600}
        .badge-ok{background:rgba(52,211,153,.15);color:#34d399}
        .badge-err{background:rgba(248,113,113,.15);color:#f87171}
        .section-header{font-size:1.2rem;color:#cbd5e1;margin:30px 0 16px;padding-bottom:8px;border-bottom:1px solid #334155}
        .verdict{padding:20px;border-radius:12px;text-align:center;font-size:1.2rem;font-weight:700;margin-bottom:30px}
        .verdict-ok{background:rgba(52,211,153,.1);border:2px solid #34d399;color:#34d399}
        .verdict-err{background:rgba(248,113,113,.1);border:2px solid #f87171;color:#f87171}
        code{font-family:'JetBrains Mono',monospace;font-size:.88rem}
        .tbl-name{color:#818cf8;font-family:'JetBrains Mono',monospace}
        .table-section{margin-bottom:30px}
        .table-section h2{color:#e2e8f0;margin-bottom:8px;font-size:1.1rem}
    </style>
</head>
<body>
    <h1>SQL Schema Verifier</h1>
    <p class="subtitle">Comparing <code>sqlpfe.sql</code> against <strong><?= $dbName ?></strong></p>
    <div class="summary">
        <div class="summary-card"><div class="count"><?= $totalTables ?></div><div class="label">Expected Tables</div></div>
        <div class="summary-card"><div class="count"><?= $totalCols ?></div><div class="label">Expected Columns</div></div>
        <div class="summary-card"><div class="count <?= $failChecks === 0 ? 'ok' : 'err' ?>"><?= $totalChecks + $failChecks ?></div><div class="label">Total Checks</div></div>
        <div class="summary-card"><div class="count ok"><?= $passChecks ?></div><div class="label">Passed</div></div>
        <div class="summary-card"><div class="count <?= $failChecks === 0 ? 'ok' : 'err' ?>"><?= $failChecks ?></div><div class="label">Failed</div></div>
    </div>
    <?php if ($failChecks === 0): ?>
        <div class="verdict verdict-ok">100% MATCH — Database perfectly matches sqlpfe.sql</div>
    <?php else: ?>
        <div class="verdict verdict-err"><?= $failChecks ?> ISSUE(S) FOUND</div>
        <table><thead><tr><th>Type</th><th>Table</th><th>Column</th><th>Details</th></tr></thead><tbody>
        <?php foreach ($issues as $i): ?>
        <tr><td><code><?= $i['type'] ?></code></td><td><?= htmlspecialchars($i['table']) ?></td><td><?= $i['column'] ?? '—' ?></td><td><?= $i['detail'] ?></td></tr>
        <?php endforeach; ?>
        </tbody></table>
    <?php endif; ?>
    <h2 class="section-header">Table-by-Table Verification</h2>
    <?php foreach ($expectedSchema as $tbl => $expectedCols): ?>
    <div class="table-section">
        <h2>Table: <span class="tbl-name"><?= $tbl ?></span></h2>
        <table>
            <thead><tr><th>Column</th><th>Expected Type</th><th>Actual Type</th><th>Nullable</th><th>Status</th></tr></thead>
            <tbody>
            <?php if (!isset($actualSchema[$tbl])): ?>
            <tr><td colspan="5"><span class="badge badge-err">TABLE MISSING FROM DATABASE</span></td></tr>
            <?php else: ?>
            <?php foreach ($expectedCols as $col => $exp): ?>
            <?php
                $actualCol = $actualSchema[$tbl][$col] ?? null;
                if ($actualCol) {
                    $expType = normalizeType($exp['type']);
                    $actType = normalizeType($actualCol['type']);
                    $match = ($expType === $actType) && ($exp['nullable'] === $actualCol['nullable']) && ($exp['is_auto'] === $actualCol['is_auto']);
                    $statusClass = $match ? 'badge-ok' : 'badge-err';
                    $statusText = $match ? 'OK' : 'MISMATCH';
                } else {
                    $actType = '—';
                    $statusClass = 'badge-err';
                    $statusText = 'MISSING';
                }
            ?>
            <tr>
                <td><code><?= $col ?></code></td>
                <td><?= $exp['type'] ?></td>
                <td><?= $actualCol ? $actualCol['type'] : '—' ?></td>
                <td><?= $exp['nullable'] ? 'YES' : 'NO' ?></td>
                <td><span class="badge <?= $statusClass ?>"><?= $statusText ?></span></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php endforeach; ?>
    <h2 class="section-header">Indexes</h2>
    <table>
        <thead><tr><th>Table</th><th>Index</th><th>Exists in DB</th><th>Status</th></tr></thead>
        <tbody>
            <?php foreach ($expectedIndexes as $tbl => $idxs): ?>
            <?php foreach ($idxs as $idx): ?>
            <?php $found = isset($actualIndexes[$tbl]) && in_array($idx, $actualIndexes[$tbl]); ?>
            <tr><td><code><?= $tbl ?></code></td><td><code><?= $idx ?></code></td><td><?= $found ? 'Yes' : 'No' ?></td><td><span class="badge <?= $found ? 'badge-ok' : 'badge-err' ?>"><?= $found ? 'EXISTS' : 'MISSING' ?></span></td></tr>
            <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
    <h2 class="section-header">Views</h2>
    <table>
        <thead><tr><th>View Name</th><th>Exists in DB</th><th>Status</th></tr></thead>
        <tbody>
            <?php foreach ($expectedViews as $view): ?>
            <?php $found = in_array($view, $actualViews); ?>
            <tr><td><code><?= $view ?></code></td><td><?= $found ? 'Yes' : 'No' ?></td><td><span class="badge <?= $found ? 'badge-ok' : 'badge-err' ?>"><?= $found ? 'EXISTS' : 'MISSING' ?></span></td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
