<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<h4>Record payment</h4>
<form class="card card-body shadow-sm" method="post" enctype="multipart/form-data" action="<?= site_url('payments/store') ?>">
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Member *</label>
      <select class="form-select" name="member_id" required>
        <option value="">— select member —</option>
        <?php foreach ($members as $m): ?>
          <option value="<?= $m['id'] ?>" <?= ($member['id'] ?? '') == $m['id'] ? 'selected' : '' ?>><?= esc($m['membership_id'] . ' — ' . $m['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Apply to invoice (optional, leave blank for auto-match)</label>
      <select class="form-select" name="invoice_id">
        <option value="">— auto-match —</option>
        <?php foreach ($invoices as $i): ?>
          <option value="<?= $i['id'] ?>"><?= esc($i['invoice_no']) ?> – <?= money($i['total']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-3"><label class="form-label">Amount (RM) *</label>
      <input type="number" step="0.01" class="form-control" name="amount" required></div>
    <div class="col-md-3"><label class="form-label">Payment date *</label>
      <input type="date" class="form-control" name="payment_date" value="<?= date('Y-m-d') ?>" required></div>
    <div class="col-md-3"><label class="form-label">Method *</label>
      <select class="form-select" name="method" required>
        <?php foreach (dropdown_options('payment_method') as $opt): ?>
          <option value="<?= esc($opt['value']) ?>"><?= esc($opt['label']) ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="col-md-3"><label class="form-label">Reference no.</label>
      <input class="form-control" name="reference_no"></div>

    <div class="col-md-6"><label class="form-label">Proof of payment</label>
      <input type="file" class="form-control" name="proof" accept="image/*,.pdf"></div>
    <div class="col-12"><label class="form-label">Notes</label>
      <textarea class="form-control" rows="2" name="notes"></textarea></div>
  </div>
  <div class="mt-3">
    <button class="btn btn-primary">Submit</button>
    <a class="btn btn-link" href="<?= site_url('payments') ?>">Cancel</a>
  </div>
</form>
<?= $this->endSection() ?>
