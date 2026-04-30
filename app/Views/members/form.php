<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php $isEdit = ! empty($member); ?>
<h4><?= $isEdit ? 'Edit member' : 'Register member' ?></h4>
<form class="card card-body shadow-sm" method="post" enctype="multipart/form-data"
      action="<?= site_url($isEdit ? 'members/' . $member['id'] . '/update' : 'members/store') ?>">
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-md-8">
      <label class="form-label">Full name *</label>
      <input class="form-control" name="name" required value="<?= esc(old('name', $member['name'] ?? '')) ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">IC / Passport no.</label>
      <input class="form-control" name="ic_no" value="<?= esc(old('ic_no', $member['ic_no'] ?? '')) ?>">
    </div>
    <div class="col-md-6"><label class="form-label">Email</label>
      <input type="email" class="form-control" name="email" value="<?= esc(old('email', $member['email'] ?? '')) ?>"></div>
    <div class="col-md-6"><label class="form-label">Phone</label>
      <input class="form-control" name="phone" value="<?= esc(old('phone', $member['phone'] ?? '')) ?>"></div>
    <div class="col-12"><label class="form-label">Address</label>
      <textarea class="form-control" rows="2" name="address"><?= esc(old('address', $member['address'] ?? '')) ?></textarea></div>

    <div class="col-md-4"><label class="form-label">Plan *</label>
      <select class="form-select" name="plan_id" required>
        <option value="">— select —</option>
        <?php foreach ($plans as $p): ?>
          <option value="<?= $p['id'] ?>" <?= ($member['plan_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
            <?= esc($p['name']) ?> – <?= money($p['price']) ?>
          </option>
        <?php endforeach; ?>
      </select></div>
    <div class="col-md-3"><label class="form-label">Joined date *</label>
      <input type="date" class="form-control" name="joined_date" required
             value="<?= esc(old('joined_date', $member['joined_date'] ?? date('Y-m-d'))) ?>"></div>
    <?php if ($isEdit): ?>
    <div class="col-md-3"><label class="form-label">Expiry date</label>
      <input type="date" class="form-control" name="expiry_date" value="<?= esc($member['expiry_date']) ?>"></div>
    <div class="col-md-2"><label class="form-label">Status</label>
      <select class="form-select" name="status">
        <?php foreach (['active','expired','suspended'] as $s): ?>
          <option value="<?= $s ?>" <?= ($member['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select></div>
    <?php endif; ?>

    <div class="col-md-6"><label class="form-label">Photo</label>
      <input type="file" class="form-control" name="photo" accept="image/*"></div>
    <div class="col-12"><label class="form-label">Notes</label>
      <textarea class="form-control" rows="2" name="notes"><?= esc(old('notes', $member['notes'] ?? '')) ?></textarea></div>
  </div>
  <div class="mt-3">
    <button class="btn btn-primary"><?= $isEdit ? 'Update' : 'Register' ?></button>
    <a class="btn btn-link" href="<?= site_url('members') ?>">Cancel</a>
  </div>
</form>
<?= $this->endSection() ?>
