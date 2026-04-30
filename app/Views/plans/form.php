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
  </div>
  <div class="mt-3">
    <button class="btn btn-primary"><?= $isEdit ? 'Update' : 'Create' ?></button>
    <a class="btn btn-link" href="<?= site_url('plans') ?>">Cancel</a>
  </div>
</form>
<?= $this->endSection() ?>
