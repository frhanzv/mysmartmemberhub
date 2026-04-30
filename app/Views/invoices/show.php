<?= $this->extend('layouts/main') ?>

<?php
  $eiStatus = $i['einvoice_status'] ?? null;
  $eiBadge  = match ($eiStatus) {
      'valid'     => 'bg-success',
      'pending'   => 'bg-warning text-dark',
      'invalid'   => 'bg-danger',
      'cancelled' => 'bg-secondary',
      default     => 'bg-light text-dark border',
  };
  $cancellable = $eiStatus === 'valid'
      && ! empty($i['einvoice_cancellable_until'])
      && strtotime($i['einvoice_cancellable_until']) > time();
?>

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

<div class="card mt-3 shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center">
    <strong>LHDN MyInvois e-Invoice</strong>
    <span class="badge <?= $eiBadge ?>"><?= esc(strtoupper($eiStatus ?: 'not submitted')) ?></span>
  </div>
  <div class="card-body">
    <?php if ($eiStatus === 'valid'): ?>
      <dl class="row mb-2">
        <dt class="col-md-3">IRBM UUID</dt>
        <dd class="col-md-9"><code><?= esc($i['einvoice_uuid']) ?></code></dd>
        <dt class="col-md-3">Long ID</dt>
        <dd class="col-md-9"><code><?= esc($i['einvoice_long_id']) ?></code></dd>
        <dt class="col-md-3">Validated</dt>
        <dd class="col-md-9"><?= esc($i['einvoice_validated_at']) ?></dd>
        <dt class="col-md-3">Cancellable until</dt>
        <dd class="col-md-9"><?= esc($i['einvoice_cancellable_until']) ?> <span class="text-muted">(72h window)</span></dd>
        <?php if (! empty($einvoicePublicUrl)): ?>
          <dt class="col-md-3">Public URL</dt>
          <dd class="col-md-9"><a href="<?= esc($einvoicePublicUrl) ?>" target="_blank" class="small"><?= esc($einvoicePublicUrl) ?></a></dd>
        <?php endif; ?>
      </dl>
    <?php elseif ($eiStatus === 'pending'): ?>
      <p class="text-muted mb-2">Submitted to MyInvois. IRBM is still validating.</p>
    <?php elseif ($eiStatus === 'invalid'): ?>
      <p class="text-danger mb-2">MyInvois rejected this document. See latest submission below.</p>
    <?php elseif ($eiStatus === 'cancelled'): ?>
      <p class="text-muted mb-2">This e-Invoice has been cancelled with IRBM.</p>
    <?php else: ?>
      <p class="text-muted mb-2">No e-Invoice has been submitted to LHDN MyInvois yet.</p>
    <?php endif; ?>

    <div class="d-flex flex-wrap gap-2">
      <?php if (in_array($eiStatus, [null, 'invalid'], true) || $eiStatus === ''): ?>
        <form method="post" action="<?= site_url('invoices/' . $i['id'] . '/einvoice/submit') ?>">
          <?= csrf_field() ?>
          <button class="btn btn-sm btn-primary"><i class="bi bi-cloud-arrow-up me-1"></i>Submit to LHDN</button>
        </form>
      <?php endif; ?>
      <?php if ($eiStatus === 'pending'): ?>
        <form method="post" action="<?= site_url('invoices/' . $i['id'] . '/einvoice/refresh') ?>">
          <?= csrf_field() ?>
          <button class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-clockwise me-1"></i>Refresh status</button>
        </form>
      <?php endif; ?>
      <?php if ($cancellable): ?>
        <form method="post" action="<?= site_url('invoices/' . $i['id'] . '/einvoice/cancel') ?>" onsubmit="return confirm('Cancel this e-Invoice with IRBM? This is irreversible.');" class="d-flex gap-2">
          <?= csrf_field() ?>
          <input type="text" name="reason" required placeholder="Cancellation reason" class="form-control form-control-sm" style="min-width:240px">
          <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-octagon me-1"></i>Cancel e-Invoice</button>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <?php if (! empty($einvoiceDoc)): ?>
    <div class="card-footer small">
      <details>
        <summary>Last submission (<?= esc($einvoiceDoc['environment']) ?>) — <?= esc($einvoiceDoc['status']) ?> @ <?= esc($einvoiceDoc['updated_at']) ?></summary>
        <div class="mt-2">
          <div class="row g-2">
            <?php foreach (['submission_uid','irbm_uuid','irbm_long_id','code_number'] as $f): ?>
              <?php if (! empty($einvoiceDoc[$f])): ?>
                <div class="col-md-6"><strong><?= esc($f) ?>:</strong> <code><?= esc($einvoiceDoc[$f]) ?></code></div>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
          <?php if (! empty($einvoiceDoc['error_payload'])): ?>
            <div class="mt-2">
              <strong class="text-danger">Errors</strong>
              <pre class="bg-light p-2 mb-0" style="max-height:240px;overflow:auto"><?= esc($einvoiceDoc['error_payload']) ?></pre>
            </div>
          <?php endif; ?>
          <?php if (! empty($einvoiceDoc['response_payload'])): ?>
            <div class="mt-2">
              <strong>Raw response</strong>
              <pre class="bg-light p-2 mb-0" style="max-height:240px;overflow:auto"><?= esc($einvoiceDoc['response_payload']) ?></pre>
            </div>
          <?php endif; ?>
        </div>
      </details>
    </div>
  <?php endif; ?>
</div>
<?= $this->endSection() ?>
