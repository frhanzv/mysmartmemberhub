<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Reports</h4>
  <form method="get" class="d-flex gap-2">
    <input type="month" class="form-control form-control-sm" name="month" value="<?= esc($month) ?>">
    <button class="btn btn-secondary btn-sm">Update</button>
  </form>
</div>

<div class="row g-3 mb-3">
  <?php
  $cards = [
      ['Active members',   $summary['active_members'], 'success'],
      ['Expired members',  $summary['expired_members'], 'secondary'],
      ['Month collection (' . $month . ')', money($summary['month_collection']), 'primary'],
      ['Outstanding invoices', $summary['outstanding_count'] . ' / ' . money($summary['outstanding_total']), 'danger'],
  ];
  foreach ($cards as [$l, $v, $c]): ?>
    <div class="col-md-3"><div class="card shadow-sm h-100"><div class="card-body">
      <div class="text-muted small text-uppercase"><?= $l ?></div>
      <div class="fs-4 fw-semibold text-<?= $c ?>"><?= $v ?></div>
    </div></div></div>
  <?php endforeach; ?>
</div>

<div class="row g-3">
  <div class="col-md-7">
    <div class="card shadow-sm">
      <div class="card-header bg-white">
        <strong>Collection by plan (<?= esc($month) ?>)</strong>
      </div>
      <div class="table-responsive"><table class="table mb-0">
        <thead><tr><th>Plan</th><th class="text-end">Count</th><th class="text-end">Total</th></tr></thead>
        <tbody>
        <?php foreach ($byPlan as $row): ?>
          <tr>
            <td><?= esc($row['name'] ?? '— no plan —') ?></td>
            <td class="text-end"><?= esc($row['cnt']) ?></td>
            <td class="text-end"><?= money($row['total']) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (! $byPlan): ?><tr><td colspan="3" class="text-center text-muted">No data.</td></tr><?php endif; ?>
        </tbody>
      </table></div>
    </div>
  </div>
  <div class="col-md-5">
    <div class="card shadow-sm">
      <div class="card-header bg-white"><strong>Exports</strong></div>
      <div class="list-group list-group-flush">
        <?php if (can('report.export') && module_enabled('exports')): ?>
        <a class="list-group-item list-group-item-action" href="<?= site_url('reports/export/monthly?month=' . urlencode($month)) ?>"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Monthly statement (<?= esc($month) ?>)</a>
        <a class="list-group-item list-group-item-action" href="<?= site_url('reports/export/outstanding') ?>"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Outstanding invoices</a>
        <a class="list-group-item list-group-item-action" href="<?= site_url('members/export') ?>"><i class="bi bi-file-earmark-spreadsheet me-2"></i>All members</a>
        <a class="list-group-item list-group-item-action" href="<?= site_url('payments/export') ?>"><i class="bi bi-file-earmark-spreadsheet me-2"></i>All payments</a>
        <a class="list-group-item list-group-item-action" href="<?= site_url('invoices/export') ?>"><i class="bi bi-file-earmark-spreadsheet me-2"></i>All invoices</a>
        <a class="list-group-item list-group-item-action" href="<?= site_url('receipts/export') ?>"><i class="bi bi-file-earmark-spreadsheet me-2"></i>All receipts</a>
        <?php else: ?>
          <div class="list-group-item text-muted">You don't have export permissions.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
