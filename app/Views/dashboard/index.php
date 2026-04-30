<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-1">Dashboard</h4>
    <p class="text-muted mb-0" style="font-size:.875rem">Welcome back! Here's your membership overview.</p>
  </div>
  <div class="text-muted small"><i class="bi bi-calendar3 me-1"></i><?= date('l, d M Y') ?></div>
</div>

<div class="row g-3 mb-4">
  <?php
  $cards = [
      ['Active members',     $stats['members_active'],      'person-check',      'success'],
      ['Expired members',    $stats['members_expired'],     'person-x',          'secondary'],
      ['Expiring ≤14d',      $stats['expiring_soon'],       'hourglass-split',   'warning'],
      ['Pending payments',   $stats['pending_payments'],    'cash-stack',        'info'],
      ['Today collection',   money($stats['today_collection']),  'wallet2',      'primary'],
      ['Month collection',   money($stats['month_collection']),  'graph-up-arrow','primary'],
      ['Outstanding invoices',$stats['outstanding_invoices'],'file-earmark-ruled','danger'],
  ];
  foreach ($cards as [$label, $value, $icon, $color]): ?>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card stat-card stat-<?= $color ?> h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-<?= $color ?>-soft"><i class="bi bi-<?= $icon ?>"></i></div>
        <div>
          <div class="stat-label"><?= $label ?></div>
          <div class="stat-value"><?= esc($value) ?></div>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="row g-3">
  <div class="col-12 col-lg-8">
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-bar-chart me-2"></i>Monthly collection</span>
        <small class="text-muted">Last 12 months</small>
      </div>
      <div class="card-body"><canvas id="chartMonthly" height="110"></canvas></div>
    </div>
  </div>
  <div class="col-12 col-lg-4">
    <div class="card h-100">
      <div class="card-header"><i class="bi bi-clock-history me-2"></i>Expiring soon <small class="text-muted">(30 days)</small></div>
      <ul class="list-group list-group-flush">
        <?php foreach ($expiring as $m): ?>
          <li class="list-group-item d-flex justify-content-between align-items-start py-3" style="border-color:var(--border)">
            <div>
              <a href="<?= site_url('members/' . $m['id']) ?>" class="fw-semibold text-decoration-none" style="color:var(--text)"><?= esc($m['name']) ?></a>
              <div class="text-muted" style="font-size:.78rem"><?= esc($m['membership_id']) ?> · <?= esc($m['plan_name'] ?? '-') ?></div>
            </div>
            <span class="badge bg-warning"><?= fdate($m['expiry_date']) ?></span>
          </li>
        <?php endforeach; ?>
        <?php if (! $expiring): ?>
          <li class="list-group-item text-muted py-4 text-center" style="border-color:var(--border)">
            <i class="bi bi-check-circle me-1 text-success"></i> Nothing expiring soon.
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</div>

<div class="card mt-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span><i class="bi bi-hourglass-split me-2"></i>Pending payments</span>
    <a href="<?= site_url('payments?status=pending') ?>" class="btn btn-sm btn-outline-primary">View all</a>
  </div>
  <div class="table-responsive">
    <table class="table table-hover mb-0 align-middle">
      <thead><tr><th>Member</th><th>Amount</th><th>Method</th><th>Date</th><th>Reference</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($pending as $p): ?>
        <tr>
          <td>
            <span class="fw-semibold"><?= esc($p['member_name']) ?></span>
            <span class="text-muted small d-block"><?= esc($p['membership_id']) ?></span>
          </td>
          <td class="fw-semibold"><?= money($p['amount']) ?></td>
          <td><span class="badge bg-info"><?= esc(ucfirst($p['method'])) ?></span></td>
          <td><?= fdate($p['payment_date']) ?></td>
          <td><?= esc($p['reference_no']) ?></td>
          <td class="text-end"><a class="btn btn-sm btn-primary" href="<?= site_url('payments/' . $p['id']) ?>">Review</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (! $pending): ?>
          <tr><td colspan="6" class="text-center text-muted py-4"><i class="bi bi-check-circle me-1 text-success"></i> No pending payments.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const data = <?= json_encode($monthly) ?>;
new Chart(document.getElementById('chartMonthly'), {
  type: 'bar',
  data: {
    labels: data.map(d => d.m),
    datasets: [{
      label: 'Collection (RM)',
      data: data.map(d => parseFloat(d.t)),
      backgroundColor: 'rgba(99,102,241,.65)',
      hoverBackgroundColor: 'rgba(99,102,241,.85)',
      borderRadius: 6,
      borderSkipped: false,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' }, ticks: { font: { family: 'Inter', size: 11 } } },
      x: { grid: { display: false }, ticks: { font: { family: 'Inter', size: 11 } } }
    }
  }
});
</script>
<?= $this->endSection() ?>
