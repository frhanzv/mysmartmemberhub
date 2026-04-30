<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
// Friendly labels & descriptions for module toggles.
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

// Group labels & icons
$groupMeta = [
    'module'    => ['Modules — enable / disable', 'bi-toggles',        'Disabled modules return 404 on their routes and are hidden from the sidebar.'],
    'company'   => ['Company',                     'bi-building',       null],
    'branding'  => ['Branding',                    'bi-palette',        null],
    'invoice'   => ['Invoice',                     'bi-receipt',        null],
    'payment'   => ['Payment',                     'bi-credit-card',    null],
    'system'    => ['System',                      'bi-gear',           null],
    'einvoice'  => ['LHDN e-Invoice',              'bi-file-earmark-code', 'MyInvois supplier identity & environment.'],
    'counter'   => ['Counter',                     'bi-123',            null],
    'general'   => ['General',                     'bi-sliders',        null],
];

$groupOrder = ['module', 'company', 'branding', 'invoice', 'payment', 'system', 'einvoice', 'counter', 'general'];
$ordered = [];
foreach ($groupOrder as $g) { if (isset($grouped[$g])) { $ordered[$g] = $grouped[$g]; } }
foreach ($grouped as $g => $items) { if (! isset($ordered[$g])) { $ordered[$g] = $items; } }

$idx = 0;
?>

<h4 class="mb-3">System settings</h4>

<form method="post" enctype="multipart/form-data" action="<?= site_url('settings/update') ?>">
  <?= csrf_field() ?>

  <div class="accordion" id="settingsAccordion">
    <?php foreach ($ordered as $group => $items): $idx++; ?>
      <?php
        $meta  = $groupMeta[$group] ?? [ucfirst($group), 'bi-sliders', null];
        $colId = 'collapse_' . $group;
      ?>
      <div class="accordion-item mb-2 shadow-sm rounded">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button"
                  data-bs-toggle="collapse" data-bs-target="#<?= $colId ?>">
            <i class="bi <?= $meta[1] ?> me-2"></i>
            <strong><?= $meta[0] ?></strong>
            <?php if ($meta[2]): ?>
              <small class="text-muted ms-3"><?= $meta[2] ?></small>
            <?php endif; ?>
            <span class="badge bg-secondary ms-auto me-2"><?= count($items) ?></span>
          </button>
        </h2>
        <div id="<?= $colId ?>" class="accordion-collapse collapse" data-bs-parent="#settingsAccordion">
          <div class="accordion-body">
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
                    <small class="text-muted"><?= $desc ?></small><br>
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
      </div>
    <?php endforeach; ?>

    <!-- ── Dropdown Options section ───────────────────────── -->
    <div class="accordion-item mb-2 shadow-sm rounded">
      <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button"
                data-bs-toggle="collapse" data-bs-target="#collapse_dropdown_options">
          <i class="bi bi-ui-checks me-2"></i>
          <strong>Dropdown Options</strong>
          <small class="text-muted ms-3">Manage form dropdown values (countries, payment methods, etc.)</small>
          <span class="badge bg-secondary ms-auto me-2"><?= array_sum(array_map('count', $dropdownGrouped ?? [])) ?></span>
        </button>
      </h2>
      <div id="collapse_dropdown_options" class="accordion-collapse collapse" data-bs-parent="#settingsAccordion">
        <div class="accordion-body">
          <div class="d-flex justify-content-end mb-3">
            <a href="<?= site_url('settings/dropdown-options/create') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add option</a>
          </div>

          <?php if (empty($dropdownGrouped)): ?>
            <div class="alert alert-info mb-0">No dropdown options found. Click <strong>Add option</strong> to get started.</div>
          <?php else: ?>
            <?php foreach ($dropdownGrouped as $category => $catItems): ?>
              <div class="card border mb-3">
                <div class="card-header bg-light d-flex align-items-center justify-content-between py-2">
                  <span class="fw-semibold"><i class="bi bi-list-ul me-1"></i><?= esc(ucwords(str_replace('_', ' ', $category))) ?></span>
                  <span class="badge bg-secondary"><?= count($catItems) ?></span>
                </div>
                <div class="table-responsive">
                  <table class="table table-sm table-hover mb-0 small">
                    <thead><tr>
                      <th>Label</th>
                      <th>Value</th>
                      <th style="width:60px">Order</th>
                      <th style="width:60px">Active</th>
                      <th style="width:110px" class="text-end">Actions</th>
                    </tr></thead>
                    <tbody>
                      <?php foreach ($catItems as $opt): ?>
                        <tr<?= ! $opt['is_active'] ? ' class="table-secondary text-muted"' : '' ?>>
                          <td><?= esc($opt['label']) ?></td>
                          <td><code><?= esc($opt['value']) ?></code></td>
                          <td><?= $opt['sort_order'] ?></td>
                          <td><?= $opt['is_active'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
                          <td class="text-end text-nowrap">
                            <a href="<?= site_url('settings/dropdown-options/' . $opt['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary py-0 px-1">Edit</a>
                            <form class="d-inline" method="post" action="<?= site_url('settings/dropdown-options/' . $opt['id'] . '/delete') ?>" onsubmit="return confirm('Delete this option?')">
                              <?= csrf_field() ?>
                              <button class="btn btn-sm btn-outline-danger py-0 px-1">Del</button>
                            </form>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <button class="btn btn-primary mt-3"><i class="bi bi-save me-1"></i>Save settings</button>
</form>
<?= $this->endSection() ?>
