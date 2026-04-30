<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<h4 class="mb-3">Dashboard</h4>

<div class="row g-3 mb-4">
  <?php
  $cards = [
      ['Active members', $stats['members_active'], 'person-check', 'success'],
      ['Expired members', $stats['members_expired'], 'person-x', 'secondary'],
      ['Expiring ≤14d',  $stats['expiring_soon'],   'hourglass-split', 'warning'],
      ['Pending payments', $stats['pending_payments'], 'cash-stack', 'info'],
      ['Today collection', money($stats['today_collection']), 'wallet2', 'primary'],
      ['Month collection', money($stats['month_collection']), 'graph-up-arrow', 'primary'],
      ['Outstanding invoices', $stats['outstanding_invoices'], 'file-earmark-ruled', 'danger'],
  ];
  foreach ($cards as [$label, $value, $icon, $color]): ?>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="card shadow-sm h-100">
      <div class="card-body d-flex align-items-center">
        <div class="me-3 fs-2 text-<?= $color ?>"><i class="bi bi-<?= $icon ?>"></i></div>
        <div>
          <div class="text-muted small text-uppercase"><?= $label ?></div>
          <div class="fs-4 fw-semibold"><?= esc($value) ?></div>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="row g-3">
  <div class="col-12 col-lg-8">
    <div class="card shadow-sm">
      <div class="card-header bg-white"><strong>Monthly collection (last 12 months)</strong></div>
      <div class="card-body"><canvas id="chartMonthly" height="120"></canvas></div>
    </div>
  </div>
  <div class="col-12 col-lg-4">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-white"><strong>Expiring soon (30 days)</strong></div>
      <ul class="list-group list-group-flush">
        <?php foreach ($expiring as $m): ?>
          <li class="list-group-item d-flex justify-content-between">
            <span>
              <a href="<?= site_url('members/' . $m['id']) ?>"><?= esc($m['name']) ?></a>
              <small class="text-muted d-block"><?= esc($m['membership_id']) ?> · <?= esc($m['plan_name'] ?? '-') ?></small>
            </span>
            <span class="text-end small"><?= fdate($m['expiry_date']) ?></span>
          </li>
        <?php endforeach; ?>
        <?php if (! $expiring): ?>
          <li class="list-group-item text-muted">Nothing expiring soon.</li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</div>

<div class="card mt-4 shadow-sm">
  <div class="card-header bg-white d-flex justify-content-between">
    <strong>Pending payments</strong>
    <a href="<?= site_url('payments?status=pending') ?>" class="small">View all</a>
  </div>
  <div class="table-responsive">
    <table class="table table-sm mb-0 align-middle">
      <thead><tr><th>Member</th><th>Amount</th><th>Method</th><th>Date</th><th>Reference</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($pending as $p): ?>
        <tr>
          <td><?= esc($p['member_name']) ?> <small class="text-muted"><?= esc($p['membership_id']) ?></small></td>
          <td><?= money($p['amount']) ?></td>
          <td><?= esc(ucfirst($p['method'])) ?></td>
          <td><?= fdate($p['payment_date']) ?></td>
          <td><?= esc($p['reference_no']) ?></td>
          <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?= site_url('payments/' . $p['id']) ?>">Review</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (! $pending): ?>
          <tr><td colspan="6" class="text-center text-muted">No pending payments.</td></tr>
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
    datasets: [{ label: 'Collection (RM)', data: data.map(d => parseFloat(d.t)), backgroundColor: '#0d6efd' }]
  },
  options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>
<?= $this->endSection() ?>
