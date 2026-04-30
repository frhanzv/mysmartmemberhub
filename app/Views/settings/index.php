<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
// Friendly labels & descriptions for module toggles. Keys map to `module.<x>.enabled`.
$moduleMeta = [
    'module.payments.enabled'      => ['Payments',       'Record payments, approve / reject / reverse, payment history. Disabling hides the section and 404s its routes.'],
    'module.invoices.enabled'      => ['Invoices',       'Auto-generated invoices, PDFs, email-out. Disabling stops auto-invoice creation and 404s the section.'],
    'module.receipts.enabled'      => ['Receipts',       'Auto-receipts when a payment is approved. Disabling skips PDF receipt issuance on approval.'],
    'module.reports.enabled'       => ['Reports & Stats','Finance reports, monthly collection, outstanding balance.'],
    'module.einvoice.enabled'      => ['LHDN e-Invoice', 'MyInvois submit / cancel / refund-note flow on invoices. Requires sandbox credentials in <code>einvoice.*</code>.'],
    'module.notifications.enabled' => ['Notifications',  'Bell icon + in-app notifications.'],
    'module.audit_log.enabled'     => ['Audit Log',      'Audit log UI under <code>/audit-log</code>. Audit rows are still written even when this is off.'],
    'module.exports.enabled'       => ['Exports',        'Excel / CSV / PDF export buttons across the app.'],
];
$groupOrder = ['module', 'company', 'branding', 'invoice', 'payment', 'system', 'einvoice', 'counter', 'general'];
$ordered = [];
foreach ($groupOrder as $g) { if (isset($grouped[$g])) { $ordered[$g] = $grouped[$g]; } }
foreach ($grouped as $g => $items) { if (! isset($ordered[$g])) { $ordered[$g] = $items; } }
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">System settings</h4>
  <div>
    <a href="<?= site_url('settings/dropdown-options') ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-ui-checks me-1"></i>Manage Dropdown Options</a>
    <small class="text-muted ms-2">Super Admin only</small>
  </div>
</div>
<form method="post" enctype="multipart/form-data" action="<?= site_url('settings/update') ?>">
  <?= csrf_field() ?>
  <?php foreach ($ordered as $group => $items): ?>
    <div class="card shadow-sm mb-3">
      <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <strong>
          <?php if ($group === 'module'): ?><i class="bi bi-toggles me-2"></i>Modules &mdash; enable / disable<?php else: ?><?= esc(ucfirst($group)) ?><?php endif; ?>
        </strong>
        <?php if ($group === 'module'): ?>
          <small class="text-muted">Disabled modules return 404 on their routes and are hidden from the sidebar.</small>
        <?php elseif ($group === 'einvoice'): ?>
          <small class="text-muted">LHDN MyInvois supplier identity &amp; environment.</small>
        <?php endif; ?>
      </div>
      <div class="card-body">
        <?php foreach ($items as $s): ?>
          <?php
            $key   = $s['key_name'];
            $type  = $s['type'];
            $value = $s['value'];
            $isModule = ($group === 'module');
            [$label, $desc] = $moduleMeta[$key] ?? [$key, null];
          ?>
          <div class="row g-3 align-items-center mb-2">
            <label class="col-md-4 col-form-label">
              <?php if ($isModule && isset($moduleMeta[$key])): ?>
                <span class="fw-semibold"><?= esc($label) ?></span><br>
                <small class="text-muted"><?= $desc /* contains controlled HTML */ ?></small><br>
                <code class="small text-muted"><?= esc($key) ?></code>
              <?php else: ?>
                <code><?= esc($key) ?></code>
              <?php endif; ?>
            </label>
            <div class="col-md-8">
              <?php if ($type === 'bool'): ?>
                <div class="form-check form-switch">
                  <input type="hidden" name="settings[<?= esc($key) ?>]" value="0">
                  <input class="form-check-input" type="checkbox" role="switch"
                         name="settings[<?= esc($key) ?>]" value="1"
                         id="set_<?= esc(str_replace('.', '_', $key)) ?>"
                         <?= ((string) $value === '1') ? 'checked' : '' ?>>
                  <label class="form-check-label" for="set_<?= esc(str_replace('.', '_', $key)) ?>">
                    <?= ((string) $value === '1') ? 'Enabled' : 'Disabled' ?>
                  </label>
                </div>
              <?php elseif (in_array($key, ['payment.instructions','company.address','invoice.footer'], true)): ?>
                <textarea class="form-control" rows="3" name="settings[<?= esc($key) ?>]"><?= esc($value) ?></textarea>
              <?php elseif ($key === 'branding.logo'): ?>
                <input type="file" class="form-control" name="logo" accept="image/*">
                <?php if ($value): ?><small class="text-muted d-block">Current: <?= esc($value) ?></small><?php endif; ?>
              <?php elseif ($key === 'einvoice.environment'): ?>
                <select class="form-select" name="settings[<?= esc($key) ?>]">
                  <?php foreach (['stub' => 'Stub (offline dev)', 'sandbox' => 'Sandbox (preprod LHDN)', 'prod' => 'Production'] as $opt => $lab): ?>
                    <option value="<?= $opt ?>" <?= $value === $opt ? 'selected' : '' ?>><?= esc($lab) ?></option>
                  <?php endforeach; ?>
                </select>
              <?php else: ?>
                <input class="form-control" name="settings[<?= esc($key) ?>]" value="<?= esc($value) ?>">
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
  <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Save settings</button>
</form>
<?= $this->endSection() ?>
