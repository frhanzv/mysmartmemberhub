<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Invoices</h4>
  <?php if (can('report.export')): ?>
    <a class="btn btn-outline-secondary btn-sm" href="<?= site_url('invoices/export') ?>"><i class="bi bi-download me-1"></i>Export</a>
  <?php endif; ?>
</div>

<form class="card card-body shadow-sm mb-3" method="get">
  <div class="row g-2 align-items-end">
    <div class="col-md-4"><label class="form-label small">Search</label>
      <input class="form-control form-control-sm" name="q" value="<?= esc($filters['q'] ?? '') ?>" placeholder="Invoice no / member"></div>
    <div class="col-md-3"><label class="form-label small">Status</label>
      <select class="form-select form-select-sm" name="status">
        <option value="">All</option>
        <?php foreach (['draft','issued','paid','cancelled'] as $s): ?>
        <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="col-md-3"><label class="form-label small">Month issued</label>
      <input type="month" class="form-control form-control-sm" name="month" value="<?= esc($filters['month'] ?? '') ?>"></div>
    <div class="col-md-2"><button class="btn btn-secondary btn-sm w-100">Filter</button></div>
  </div>
</form>

<div class="card shadow-sm">
  <div class="table-responsive"><table class="table mb-0 align-middle">
    <thead><tr><th>Invoice no.</th><th>Member</th><th>Issued</th><th>Due</th><th>Total</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><a href="<?= site_url('invoices/' . $r['id']) ?>"><?= esc($r['invoice_no']) ?></a></td>
        <td><?= esc($r['member_name']) ?> <small class="text-muted"><?= esc($r['membership_id']) ?></small></td>
        <td><?= fdate($r['issued_at']) ?></td>
        <td><?= fdate($r['due_at']) ?></td>
        <td><?= money($r['total']) ?></td>
        <td><?= status_badge($r['status']) ?></td>
        <td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="<?= site_url('invoices/' . $r['id'] . '/pdf') ?>" target="_blank">PDF</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (! $rows): ?><tr><td colspan="7" class="text-center text-muted py-4">No invoices.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
  <div class="card-body"><?= view('partials/pagination') ?></div>
</div>
<?= $this->endSection() ?>
