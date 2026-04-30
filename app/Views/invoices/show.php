<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Invoice <?= esc($i['invoice_no']) ?></h4>
  <div>
    <a class="btn btn-sm btn-outline-secondary" target="_blank" href="<?= site_url('invoices/' . $i['id'] . '/pdf') ?>"><i class="bi bi-file-earmark-pdf me-1"></i>Download PDF</a>
    <?php if (can('invoice.email') && ! empty($i['email'])): ?>
      <form method="post" class="d-inline" action="<?= site_url('invoices/' . $i['id'] . '/email') ?>">
        <?= csrf_field() ?><button class="btn btn-sm btn-outline-primary"><i class="bi bi-envelope me-1"></i>Email to member</button>
      </form>
    <?php endif; ?>
  </div>
</div>

<div class="card card-body shadow-sm">
  <dl class="row mb-0">
    <dt class="col-md-3">Status</dt><dd class="col-md-9"><?= status_badge($i['status']) ?></dd>
    <dt class="col-md-3">Member</dt><dd class="col-md-9"><a href="<?= site_url('members/' . $i['member_id']) ?>"><?= esc($i['member_name']) ?></a> <small class="text-muted"><?= esc($i['membership_id']) ?></small></dd>
    <dt class="col-md-3">Plan</dt><dd class="col-md-9"><?= esc($i['plan_name'] ?? '-') ?></dd>
    <dt class="col-md-3">Issued</dt><dd class="col-md-9"><?= fdate($i['issued_at']) ?></dd>
    <dt class="col-md-3">Due</dt><dd class="col-md-9"><?= fdate($i['due_at']) ?></dd>
    <dt class="col-md-3">Subtotal</dt><dd class="col-md-9"><?= money($i['amount']) ?></dd>
    <dt class="col-md-3">Tax (<?= esc($i['tax_percent']) ?>%)</dt><dd class="col-md-9"><?= money($i['tax_amount']) ?></dd>
    <dt class="col-md-3">Total</dt><dd class="col-md-9"><strong><?= money($i['total']) ?></strong></dd>
  </dl>
</div>
<?= $this->endSection() ?>
