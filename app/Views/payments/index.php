<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Payments</h4>
  <div>
    <?php if (can('report.export') && module_enabled('exports')): ?>
      <a class="btn btn-outline-secondary btn-sm" href="<?= site_url('payments/export') ?>"><i class="bi bi-download me-1"></i>Export</a>
    <?php endif; ?>
    <?php if (can('payment.create')): ?>
      <a class="btn btn-primary btn-sm" href="<?= site_url('payments/create') ?>"><i class="bi bi-plus-lg me-1"></i>Record payment</a>
    <?php endif; ?>
  </div>
</div>

<form class="card card-body shadow-sm mb-3" method="get">
  <div class="row g-2 align-items-end">
    <div class="col-md-4"><label class="form-label small">Search</label>
      <input class="form-control form-control-sm" name="q" value="<?= esc($filters['q'] ?? '') ?>" placeholder="Member / reference / invoice"></div>
    <div class="col-md-3"><label class="form-label small">Status</label>
      <select class="form-select form-select-sm" name="status">
        <option value="">All</option>
        <?php foreach (['pending','confirmed','rejected','reversed'] as $s): ?>
        <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="col-md-3"><label class="form-label small">Month</label>
      <input type="month" class="form-control form-control-sm" name="month" value="<?= esc($filters['month'] ?? '') ?>"></div>
    <div class="col-md-2"><button class="btn btn-secondary btn-sm w-100">Filter</button></div>
  </div>
</form>

<div class="card shadow-sm">
  <div class="table-responsive"><table class="table mb-0 align-middle">
    <thead><tr><th>Date</th><th>Member</th><th>Invoice</th><th>Amount</th><th>Method</th><th>Reference</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= fdate($r['payment_date']) ?></td>
        <td><?= esc($r['member_name']) ?> <small class="text-muted"><?= esc($r['membership_id']) ?></small></td>
        <td><?= esc($r['invoice_no'] ?? '-') ?></td>
        <td><?= money($r['amount']) ?></td>
        <td><?= esc(ucfirst($r['method'])) ?></td>
        <td><?= esc($r['reference_no']) ?></td>
        <td><?= status_badge($r['status']) ?></td>
        <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?= site_url('payments/' . $r['id']) ?>">View</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (! $rows): ?><tr><td colspan="8" class="text-center text-muted py-4">No payments.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
  <div class="card-body"><?= view('partials/pagination') ?></div>
</div>
<?= $this->endSection() ?>
