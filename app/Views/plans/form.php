<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php $isEdit = ! empty($plan); ?>
<h4><?= $isEdit ? 'Edit plan' : 'New plan' ?></h4>
<form class="card card-body shadow-sm" method="post"
      action="<?= site_url($isEdit ? 'plans/' . $plan['id'] . '/update' : 'plans/store') ?>">
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-md-3"><label class="form-label">Code *</label>
      <input class="form-control" name="code" required value="<?= esc(old('code', $plan['code'] ?? '')) ?>"></div>
    <div class="col-md-9"><label class="form-label">Name *</label>
      <input class="form-control" name="name" required value="<?= esc(old('name', $plan['name'] ?? '')) ?>"></div>
    <div class="col-md-3"><label class="form-label">Price (RM) *</label>
      <input type="number" step="0.01" class="form-control" name="price" required value="<?= esc(old('price', $plan['price'] ?? '0.00')) ?>"></div>
    <div class="col-md-3"><label class="form-label">Duration (months) *</label>
      <input type="number" class="form-control" name="duration_months" required value="<?= esc(old('duration_months', $plan['duration_months'] ?? 12)) ?>"></div>
    <div class="col-md-3"><label class="form-label d-block">&nbsp;</label>
      <div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?= ($plan['is_active'] ?? 1) ? 'checked' : '' ?>><label class="form-check-label" for="is_active">Active</label></div></div>
    <div class="col-12"><label class="form-label">Description</label>
      <textarea class="form-control" rows="2" name="description"><?= esc(old('description', $plan['description'] ?? '')) ?></textarea></div>

    <div class="col-12 mt-2"><h6 class="text-muted mb-0">LHDN e-Invoice classification</h6></div>
    <div class="col-md-3"><label class="form-label">Classification code</label>
      <input class="form-control" name="classification_code" maxlength="5" value="<?= esc(old('classification_code', $plan['classification_code'] ?? '022')) ?>" placeholder="022 = Others"></div>
    <div class="col-md-2"><label class="form-label">Tax type</label>
      <select class="form-select" name="tax_type">
        <?php foreach (['01' => '01 – Sales tax','02' => '02 – Service tax','03' => '03 – Tourism tax','04' => '04 – High-value goods tax','05' => '05 – Sales+Service','06' => '06 – Not Applicable','E' => 'E – Tax exemption'] as $code => $label): ?>
          <option value="<?= $code ?>" <?= ($plan['tax_type'] ?? '06') === $code ? 'selected' : '' ?>><?= esc($label) ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="col-md-2"><label class="form-label">Tax rate (%)</label>
      <input type="number" step="0.01" class="form-control" name="tax_rate" value="<?= esc(old('tax_rate', $plan['tax_rate'] ?? '0.00')) ?>"></div>
    <div class="col-md-2"><label class="form-label">Unit code</label>
      <input class="form-control" name="unit_code" maxlength="6" value="<?= esc(old('unit_code', $plan['unit_code'] ?? 'MON')) ?>" placeholder="MON, ANN, C62…"></div>
  </div>
  <div class="mt-3">
    <button class="btn btn-primary"><?= $isEdit ? 'Update' : 'Create' ?></button>
    <a class="btn btn-link" href="<?= site_url('plans') ?>">Cancel</a>
  </div>
</form>
<?= $this->endSection() ?>
