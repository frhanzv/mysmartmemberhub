<?php helper('format'); ?>
<!doctype html>
<html><head>
<meta charset="utf-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#333; }
  h1 { color:#1f2d3d; margin:0; }
  .right { text-align:right; }
  table { width:100%; border-collapse:collapse; margin-top:18px; }
  th, td { padding:8px; border-bottom:1px solid #eee; }
  th { background:#f5f7fb; text-align:left; }
  .footer { margin-top:30px; font-size:11px; color:#777; border-top:1px solid #eee; padding-top:8px; }
</style>
</head><body>

<table style="margin-bottom:18px"><tr>
  <td>
    <h1>RECEIPT</h1>
    <div><?= esc($settings['company.name'] ?? 'MySmartMemberHub') ?></div>
    <div><?= nl2br(esc($settings['company.address'] ?? '')) ?></div>
    <div><?= esc($settings['company.phone'] ?? '') ?> · <?= esc($settings['company.email'] ?? '') ?></div>
  </td>
  <td class="right">
    <div><strong>Receipt #</strong> <?= esc($r['receipt_no']) ?></div>
    <div><strong>Issued:</strong> <?= fdatetime($r['issued_at']) ?></div>
    <?php if (! empty($r['invoice_no'])): ?>
      <div><strong>Invoice:</strong> <?= esc($r['invoice_no']) ?></div>
    <?php endif; ?>
  </td>
</tr></table>

<table>
  <tr><td style="width:50%"><strong>Received from</strong></td><td></td></tr>
  <tr>
    <td>
      <?= esc($r['member_name']) ?> (<?= esc($r['membership_id']) ?>)<br>
      <?= nl2br(esc($r['address'] ?? '')) ?>
    </td>
    <td class="right">
      <strong>Amount</strong>
      <div style="font-size:18px"><?= money($r['amount']) ?></div>
    </td>
  </tr>
</table>

<table>
  <tr><th>Method</th><th>Reference</th><th>Payment date</th></tr>
  <tr>
    <td><?= esc(ucfirst($r['method'] ?? '-')) ?></td>
    <td><?= esc($r['reference_no'] ?? '-') ?></td>
    <td><?= fdate($r['payment_date'] ?? null) ?></td>
  </tr>
</table>

<div class="footer">
  <?= esc($settings['invoice.footer'] ?? 'Thank you for your payment!') ?>
</div>
</body></html>
