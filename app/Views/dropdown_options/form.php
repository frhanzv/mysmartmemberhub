<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php $isEdit = ! empty($option); ?>
<h4><?= $isEdit ? 'Edit dropdown option' : 'Add dropdown option' ?></h4>
<form class="card card-body shadow-sm" method="post"
      action="<?= site_url($isEdit ? 'settings/dropdown-options/' . $option['id'] . '/update' : 'settings/dropdown-options/store') ?>">
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-md-4">
      <label class="form-label">Category *</label>
      <select class="form-select" name="category" id="categorySelect" required>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= esc($cat) ?>" <?= ($option['category'] ?? '') === $cat ? 'selected' : '' ?>>
            <?= esc(ucwords(str_replace('_', ' ', $cat))) ?>
          </option>
        <?php endforeach; ?>
        <option value="__new__" <?= ($option && ! in_array($option['category'], $categories)) ? '' : '' ?>>+ Create new category</option>
      </select>
    </div>
    <div class="col-md-4" id="newCategoryGroup" style="display:none">
      <label class="form-label">New category name *</label>
      <input class="form-control" name="new_category" id="newCategoryInput" placeholder="e.g. industry_type">
      <small class="text-muted">Lowercase with underscores, e.g. <code>payment_method</code></small>
    </div>

    <div class="col-md-4">
      <label class="form-label">Label * <small class="text-muted">(display text)</small></label>
      <input class="form-control" name="label" required value="<?= esc(old('label', $option['label'] ?? '')) ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Value * <small class="text-muted">(stored value)</small></label>
      <input class="form-control" name="value" required value="<?= esc(old('value', $option['value'] ?? '')) ?>">
    </div>
    <div class="col-md-2">
      <label class="form-label">Sort order</label>
      <input type="number" class="form-control" name="sort_order" value="<?= esc(old('sort_order', $option['sort_order'] ?? 0)) ?>">
    </div>
    <div class="col-md-2">
      <label class="form-label d-block">&nbsp;</label>
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?= ($option['is_active'] ?? 1) ? 'checked' : '' ?>>
        <label class="form-check-label" for="is_active">Active</label>
      </div>
    </div>
  </div>
  <div class="mt-3">
    <button class="btn btn-primary"><?= $isEdit ? 'Update' : 'Add option' ?></button>
    <a class="btn btn-link" href="<?= site_url('settings/dropdown-options') ?>">Cancel</a>
  </div>
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  const sel = document.getElementById('categorySelect');
  const grp = document.getElementById('newCategoryGroup');
  const inp = document.getElementById('newCategoryInput');
  sel.addEventListener('change', function() {
    if (this.value === '__new__') {
      grp.style.display = '';
      inp.required = true;
    } else {
      grp.style.display = 'none';
      inp.required = false;
      inp.value = '';
    }
  });
  // Trigger on page load for edit forms
  if (sel.value === '__new__') { grp.style.display = ''; inp.required = true; }
</script>
<?= $this->endSection() ?>
