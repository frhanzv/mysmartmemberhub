<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Payment #<?= $p['id'] ?></h4>
  <div>
    <?php if ($p['status'] === 'pending' && can('payment.approve')): ?>
      <form method="post" class="d-inline" action="<?= site_url('payments/' . $p['id'] . '/approve') ?>">
        <?= csrf_field() ?><button class="btn btn-sm btn-success"><i class="bi bi-check2-circle me-1"></i>Approve & generate receipt</button>
      </form>
      <form method="post" class="d-inline" action="<?= site_url('payments/' . $p['id'] . '/reject') ?>" onsubmit="return confirm('Reject this payment?')">
        <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger">Reject</button>
      </form>
    <?php endif; ?>
  </div>
</div>

<div class="row g-3">
  <div class="col-md-7">
    <div class="card card-body shadow-sm">
      <dl class="row mb-0">
        <dt class="col-4">Status</dt><dd class="col-8"><?= status_badge($p['status']) ?></dd>
        <dt class="col-4">Member</dt><dd class="col-8"><a href="<?= site_url('members/' . $p['member_id']) ?>"><?= esc($p['member_name']) ?></a> <small class="text-muted"><?= esc($p['membership_id']) ?></small></dd>
        <dt class="col-4">Invoice</dt><dd class="col-8"><?= $p['invoice_id'] ? '<a href="' . site_url('invoices/' . $p['invoice_id']) . '">' . esc($p['invoice_no']) . '</a>' : '<span class="text-muted">— none —</span>' ?></dd>
        <dt class="col-4">Amount</dt><dd class="col-8"><?= money($p['amount']) ?></dd>
        <dt class="col-4">Date</dt><dd class="col-8"><?= fdate($p['payment_date']) ?></dd>
        <dt class="col-4">Method</dt><dd class="col-8"><?= esc(ucfirst($p['method'])) ?></dd>
        <dt class="col-4">Reference</dt><dd class="col-8"><?= esc($p['reference_no']) ?: '-' ?></dd>
        <dt class="col-4">Notes</dt><dd class="col-8"><?= nl2br(esc($p['notes'] ?? '')) ?></dd>
        <?php if ($p['approved_at']): ?>
        <dt class="col-4">Decided</dt><dd class="col-8"><?= fdatetime($p['approved_at']) ?></dd>
        <?php endif; ?>
      </dl>
    </div>
  </div>
  <div class="col-md-5">
    <?php if (! empty($p['proof_path'])): ?>
      <div class="card card-body shadow-sm">
        <strong>Proof of payment</strong>
        <a class="d-block mt-2" href="<?= site_url('payments/' . $p['id'] . '/proof') ?>" target="_blank">View attached file</a>
      </div>
    <?php endif; ?>
    <?php if ($receipt): ?>
      <div class="card card-body shadow-sm mt-3">
        <strong>Receipt issued</strong>
        <p class="mb-1"><a href="<?= site_url('receipts/' . $receipt['id']) ?>"><?= esc($receipt['receipt_no']) ?></a></p>
        <a class="btn btn-sm btn-outline-secondary" href="<?= site_url('receipts/' . $receipt['id'] . '/pdf') ?>" target="_blank">Download PDF</a>
      </div>
    <?php endif; ?>
  </div>
</div>
<?= $this->endSection() ?>
