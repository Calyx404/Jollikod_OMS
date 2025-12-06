<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();
require '../../database/connection.php';

if (!isset($_SESSION['branch_id'])) {
    header("Location: ../branch/");
    exit;
}
$branch_id = (int)$_SESSION['branch_id'];

// Date ranges
$today = date('Y-m-d');
$firstOfThisMonth = date('Y-m-01');
$firstOfPrevMonth = date('Y-m-01', strtotime('-1 month'));
$startLedger = date('Y-m-d', strtotime('-29 days')); // last 30 days

$statusPaid = 'paid'; // lowercase compare

// ---------- LEDGER: daily aggregates (only days WITH orders) ----------
$stmt = $pdo->prepare("
    SELECT DATE(o.created_at) AS day,
           COUNT(DISTINCT o.order_id) AS orders_count,
           COALESCE(SUM(p.total),0) AS revenue,
           COALESCE(AVG(p.total),0) AS avg_order
    FROM orders o
    JOIN payments p ON o.order_id = p.order_id
    WHERE o.branch_id = ? AND LOWER(o.status) = ? AND DATE(o.created_at) >= ?
    GROUP BY DATE(o.created_at)
    ORDER BY DATE(o.created_at) ASC
");
$stmt->execute([$branch_id, $statusPaid, $startLedger]);
$ledgerRowsAsc = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If ledgerRowsAsc is empty, ledger will show "No records available"
$ledgerWithGrowth = [];
$prevRevenue = null;
foreach ($ledgerRowsAsc as $r) {
    $revenue = (float)$r['revenue'];
    if ($prevRevenue === null) {
        $growth = null;
    } else {
        if ($prevRevenue == 0) {
            $growth = $revenue == 0 ? 0.0 : 100.0;
        } else {
            $growth = (($revenue - $prevRevenue) / $prevRevenue) * 100.0;
        }
    }
    $ledgerWithGrowth[] = [
        'day' => $r['day'],
        'orders_count' => (int)$r['orders_count'],
        'revenue' => $revenue,
        'avg_order' => (float)$r['avg_order'],
        'growth' => is_null($growth) ? null : round($growth, 2)
    ];
    $prevRevenue = $revenue;
}

// For display in table: newest first
$ledgerWithGrowthDesc = array_reverse($ledgerWithGrowth);


// ---------- KPIs derived from ledger (last 30 days / overall depending on requirement) ----------
// You asked KPIs above the ledger; we'll compute KPIs using ALL paid orders (same as earlier) so KPIs represent overall numbers.
// Total orders (paid)
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT o.order_id) AS total_orders,
           COALESCE(SUM(p.total),0) AS total_revenue,
           COALESCE(AVG(p.total),0) AS avg_order_value
    FROM orders o
    JOIN payments p ON o.order_id = p.order_id
    WHERE o.branch_id = ? AND LOWER(o.status) = ?
");
$stmt->execute([$branch_id, $statusPaid]);
$kRow = $stmt->fetch(PDO::FETCH_ASSOC);
$k_total_orders = (int)($kRow['total_orders'] ?? 0);
$k_total_revenue = (float)($kRow['total_revenue'] ?? 0);
$k_avg_order_value = (float)($kRow['avg_order_value'] ?? 0);

// Revenue growth this month vs previous month (robust to zeros)
$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(p.total),0) AS revenue
    FROM orders o
    JOIN payments p ON o.order_id = p.order_id
    WHERE o.branch_id = ? AND LOWER(o.status) = ? AND o.created_at >= ? AND o.created_at < ?
");
$startThis = $firstOfThisMonth . ' 00:00:00';
$startNext = date('Y-m-d 00:00:00', strtotime($firstOfThisMonth . ' +1 month'));
$startPrev = $firstOfPrevMonth . ' 00:00:00';
$startPrevNext = $startThis;

$stmt->execute([$branch_id, $statusPaid, $startThis, $startNext]);
$revenueThisMonth = (float)($stmt->fetchColumn() ?? 0);

$stmt->execute([$branch_id, $statusPaid, $startPrev, $startPrevNext]);
$revenuePrevMonth = (float)($stmt->fetchColumn() ?? 0);

if ($revenuePrevMonth == 0) {
    $revenueGrowthPct = ($revenueThisMonth == 0) ? 0.0 : 100.0;
} else {
    $revenueGrowthPct = (($revenueThisMonth - $revenuePrevMonth) / $revenuePrevMonth) * 100.0;
}
$revenueGrowthPct = round($revenueGrowthPct, 2);


// ---------- Line chart (current month daily revenue) ----------
$stmt = $pdo->prepare("
    SELECT DATE(o.created_at) AS day, COALESCE(SUM(p.total),0) AS revenue
    FROM orders o
    JOIN payments p ON o.order_id = p.order_id
    WHERE o.branch_id = ? AND LOWER(o.status) = ? AND o.created_at >= ?
    GROUP BY DATE(o.created_at)
    ORDER BY DATE(o.created_at)
");
$stmt->execute([$branch_id, $statusPaid, $firstOfThisMonth]);
$rowsLine = $stmt->fetchAll(PDO::FETCH_ASSOC);

// prepare labels/data arrays only for days that have revenue (no forced fill)
$labels_line = [];
$data_line = [];
foreach ($rowsLine as $r) {
    $labels_line[] = date('M j', strtotime($r['day']));
    $data_line[] = (float)$r['revenue'];
}

// ---------- Bar chart: top categories by revenue (current month) ----------
$stmt = $pdo->prepare("
    SELECT mc.name AS category, COALESCE(SUM(oi.total),0) AS revenue
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN menu_items mi ON oi.menu_item_id = mi.menu_item_id
    JOIN menu_categories mc ON mi.menu_category_id = mc.menu_category_id
    WHERE o.branch_id = ? AND LOWER(o.status) = ? AND o.created_at >= ?
    GROUP BY mc.menu_category_id
    ORDER BY revenue DESC
    LIMIT 10
");
$stmt->execute([$branch_id, $statusPaid, $firstOfThisMonth]);
$catRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$labels_bar = [];
$data_bar = [];
foreach ($catRows as $r) {
    $labels_bar[] = $r['category'];
    $data_bar[] = (float)$r['revenue'];
}

// helper
function money($v) {
    return '₱' . number_format((float)$v, 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Store Analytics</title>

  <link rel="stylesheet" href="../../assets/css/pages/branch/store.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="branch">
  <main class="page">
    <header class="header header-page">
      <div class="context"><h1>Store</h1></div>
      <div class="actions right">
        <button onclick="parent.navigate('./customer.php')" class="btn btn-primary subnav"><span class="btn-label">Customer</span><i class="bx bxs-fork-spoon btn-icon"></i></button>
        <button onclick="parent.navigate('./staff.php')" class="btn btn-primary subnav"><span class="btn-label">Staff</span><i class="bx bxs-chef-hat btn-icon"></i></button>
      </div>
    </header>

    <main class="main-container main-scrollable">
      <main class="main">

        <div class="tab-container">
          <input type="radio" name="tab-group" id="tab-1" checked />
          <input type="radio" name="tab-group" id="tab-2" />

          <div class="tab-bar">
            <label for="tab-1" class="tab">Analytics</label>
            <label for="tab-2" class="tab">Ledger</label>
          </div>

          <!-- ANALYTICS TAB -->
          <div class="tab-content" id="content-1">
            <div class="table-container" id="store-analytics">
              <div class="table-header">
                <form class="table-filter">
                  <div class="field"><label>Date</label><input type="date" /></div>
                  <div class="field"><label>Range</label>
                    <select id="rangeSelect">
                      <option value="month">This month</option>
                      <option value="30">Last 30 days</option>
                    </select>
                  </div>
                  <button class="btn btn-primary" id="applyRange">Apply</button>
                </form>
              </div>

              <div class="table-content">
                <!-- KPI Cards -->
                <div class="kpi-grid">
                  <div class="kpi-card">
                    <p class="kpi-label">Total Orders</p>
                    <h4 class="kpi-value"><?= $k_total_orders ?></h4>
                    <p class="kpi-sub">Total paid orders</p>
                  </div>

                  <div class="kpi-card">
                    <p class="kpi-label">Avg Order Value</p>
                    <h4 class="kpi-value"><?= money($k_avg_order_value) ?></h4>
                    <p class="kpi-sub">Average paid order value</p>
                  </div>

                  <div class="kpi-card">
                    <p class="kpi-label">Total Revenue</p>
                    <h4 class="kpi-value"><?= money($k_total_revenue) ?></h4>
                    <p class="kpi-sub">Sum of total payments</p>
                  </div>

                  <div class="kpi-card">
                    <p class="kpi-label">Revenue Growth</p>
                    <h4 class="kpi-value"><?= $revenueGrowthPct ?>%</h4>
                    <p class="kpi-sub">This month vs previous month</p>
                  </div>
                </div>

                <!-- Charts -->
                <div class="charts-row">
                  <div class="chart-card">
                    <p>Sales (<?= date('F Y') ?>)</p>
                    <div class="chart-wrapper">
                      <canvas id="salesLine"></canvas>
                    </div>
                  </div>

                  <div class="chart-card">
                    <p>Top Categories (<?= date('F Y') ?>)</p>
                    <div class="chart-wrapper">
                      <canvas id="catBar"></canvas>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>

          <!-- LEDGER TAB -->
          <div class="tab-content" id="content-2">
            <div class="table-container" id="store-ledger">
              <div class="table-header">
                <form class="table-filter">
                  <div class="field"><label>Date</label><input type="date" /></div>
                  <div class="field"><label>Sort By</label><select><option value="date">Date</option></select></div>
                  <button class="btn btn-primary" type="button">Apply</button>
                </form>
              </div>

              <div class="table-content">
                <table>
                  <thead>
                    <tr>
                      <th>Date</th>
                      <th>Orders</th>
                      <th>Avg Order Value</th>
                      <th>Revenue</th>
                      <th>Growth vs Prev Available Day</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (count($ledgerWithGrowthDesc) === 0): ?>
                      <tr><td colspan="5">No records available.</td></tr>
                    <?php else: ?>
                      <?php foreach ($ledgerWithGrowthDesc as $row): ?>
                        <tr>
                          <td><?= date('M d, Y', strtotime($row['day'])) ?></td>
                          <td><?= (int)$row['orders_count'] ?></td>
                          <td><?= money($row['avg_order']) ?></td>
                          <td><?= money($row['revenue']) ?></td>
                          <td>
                            <?php if (is_null($row['growth'])): ?>
                              —
                            <?php else: ?>
                              <?= ($row['growth'] >= 0 ? '+' : '') . $row['growth'] . '%' ?>
                            <?php endif; ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>

            </div>
          </div>

        </div>

      </main>
    </main>
  </main>
</body>

<script>
/* Reusable chart creator */
function createChart(ctx, type, labels, data, options = {}) {
  return new Chart(ctx, {
    type,
    data: {
      labels,
      datasets: [{
        label: options.label || '',
        data,
        ...options.datasetOptions
      }]
    },
    options: {
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: options.scales || {},
      ...options.extraOptions
    }
  });
}

// data from PHP
const lineLabels = <?= json_encode($labels_line, JSON_UNESCAPED_UNICODE) ?>;
const lineData = <?= json_encode($data_line, JSON_NUMERIC_CHECK) ?>;
const barLabels = <?= json_encode($labels_bar, JSON_UNESCAPED_UNICODE) ?>;
const barData = <?= json_encode($data_bar, JSON_NUMERIC_CHECK) ?>;

// Sales line
const salesCtx = document.getElementById('salesLine').getContext('2d');
createChart(salesCtx, 'line', lineLabels, lineData, {
  datasetOptions: {
    fill: true,
    tension: 0.3,
    borderWidth: 2,
    pointRadius: 3,
    backgroundColor: 'rgba(193,1,26,0.08)',
    borderColor: 'rgba(193,1,26,1)'
  },
  scales: {
    x: { grid: { display: false } },
    y: { ticks: { callback: val => '₱' + Number(val).toLocaleString() } }
  }
});

// Category bar
const catCtx = document.getElementById('catBar').getContext('2d');
createChart(catCtx, 'bar', barLabels, barData, {
  datasetOptions: {
    backgroundColor: 'rgba(22,163,74,0.9)',
    borderRadius: 6,
    borderSkipped: false
  },
  scales: {
    x: { grid: { display: false }, ticks: { autoSkip: false } },
    y: { ticks: { callback: val => '₱' + Number(val).toLocaleString() } }
  }
});
</script>
</html>
