<?php helper('format'); ?>
<!doctype html>
<html><head>
<meta charset="utf-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#333; }
  h1 { color:#1f2d3d; margin:0; }
  .row { width:100%; }
  .col { display:inline-block; vertical-align:top; }
  .right { text-align:right; }
  table { width:100%; border-collapse:collapse; margin-top:18px; }
  th, td { padding:8px; border-bottom:1px solid #eee; }
  th { background:#f5f7fb; text-align:left; }
  .totals td { border:none; }
  .footer { margin-top:30px; font-size:11px; color:#777; border-top:1px solid #eee; padding-top:8px; }
</style>
</head><body>

<table style="margin-bottom:18px"><tr>
  <td>
    <h1>INVOICE</h1>
    <div><?= esc($settings['company.name'] ?? 'MySmartMemberHub') ?></div>
    <div><?= nl2br(esc($settings['company.address'] ?? '')) ?></div>
    <div><?= esc($settings['company.phone'] ?? '') ?></div>
    <div><?= esc($settings['company.email'] ?? '') ?></div>
  </td>
  <td class="right">
    <div><strong>Invoice #</strong> <?= esc($i['invoice_no']) ?></div>
    <div><strong>Issued:</strong> <?= fdate($i['issued_at']) ?></div>
    <div><strong>Due:</strong> <?= fdate($i['due_at']) ?></div>
    <div><strong>Status:</strong> <?= esc(strtoupper($i['status'])) ?></div>
    <?php if (! empty($i['einvoice_uuid']) && ($i['einvoice_status'] ?? '') === 'valid'): ?>
      <div style="margin-top:6px"><strong>e-Invoice:</strong> VALIDATED</div>
      <div style="font-size:10px"><strong>UUID:</strong> <?= esc($i['einvoice_uuid']) ?></div>
      <?php if (! empty($i['einvoice_validated_at'])): ?>
        <div style="font-size:10px"><strong>Validated:</strong> <?= esc($i['einvoice_validated_at']) ?></div>
      <?php endif; ?>
      <?php if (! empty($qrDataUri)): ?>
        <div style="margin-top:6px"><img src="<?= $qrDataUri ?>" alt="e-Invoice QR" style="width:110px;height:110px"></div>
      <?php endif; ?>
    <?php endif; ?>
  </td>
</tr></table>

<table style="margin-bottom:0">
  <tr>
    <td style="width:50%; vertical-align:top">
      <strong>Bill To</strong><br>
      <?= esc($i['member_name']) ?> (<?= esc($i['membership_id']) ?>)<br>
      <?= nl2br(esc($i['address'] ?? '')) ?><br>
      <?= esc($i['phone'] ?? '') ?> <?= esc($i['email'] ?? '') ?>
    </td>
    <td></td>
  </tr>
</table>

<table>
  <thead><tr><th>Description</th><th class="right">Amount</th></tr></thead>
  <tbody>
    <tr>
      <td><?= esc($i['plan_name'] ?? 'Membership') ?></td>
      <td class="right"><?= money($i['amount']) ?></td>
    </tr>
  </tbody>
</table>

<table class="totals" style="width:50%; margin-left:50%">
  <tr><td>Subtotal</td><td class="right"><?= money($i['amount']) ?></td></tr>
  <tr><td>Tax (<?= esc($i['tax_percent']) ?>%)</td><td class="right"><?= money($i['tax_amount']) ?></td></tr>
  <tr><td><strong>Total</strong></td><td class="right"><strong><?= money($i['total']) ?></strong></td></tr>
</table>

<div class="footer">
  <strong>Payment instructions</strong><br>
  <?= nl2br(esc($settings['payment.instructions'] ?? '')) ?>
  <hr>
  <?= esc($settings['invoice.footer'] ?? 'Thank you!') ?>
</div>
</body></html>
