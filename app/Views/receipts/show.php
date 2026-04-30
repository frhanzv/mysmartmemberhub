<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Receipt <?= esc($r['receipt_no']) ?></h4>
  <a class="btn btn-sm btn-outline-secondary" target="_blank" href="<?= site_url('receipts/' . $r['id'] . '/pdf') ?>"><i class="bi bi-file-earmark-pdf me-1"></i>Download PDF</a>
</div>
<div class="card card-body shadow-sm">
  <dl class="row mb-0">
    <dt class="col-md-3">Member</dt><dd class="col-md-9"><a href="<?= site_url('members/' . $r['member_id']) ?>"><?= esc($r['member_name']) ?></a> <small class="text-muted"><?= esc($r['membership_id']) ?></small></dd>
    <dt class="col-md-3">Invoice</dt><dd class="col-md-9"><?= esc($r['invoice_no'] ?? '-') ?></dd>
    <dt class="col-md-3">Amount</dt><dd class="col-md-9"><?= money($r['amount']) ?></dd>
    <dt class="col-md-3">Issued</dt><dd class="col-md-9"><?= fdatetime($r['issued_at']) ?></dd>
    <dt class="col-md-3">Method</dt><dd class="col-md-9"><?= esc(ucfirst($r['method'] ?? '-')) ?></dd>
    <dt class="col-md-3">Reference</dt><dd class="col-md-9"><?= esc($r['reference_no'] ?? '-') ?></dd>
  </dl>
</div>
<?= $this->endSection() ?>
