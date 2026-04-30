<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php $isEdit = ! empty($user); ?>
<h4><?= $isEdit ? 'Edit user' : 'New user' ?></h4>
<form class="card card-body shadow-sm" method="post"
      action="<?= site_url($isEdit ? 'users/' . $user['id'] . '/update' : 'users/store') ?>">
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-md-6"><label class="form-label">Name *</label>
      <input class="form-control" name="name" required value="<?= esc(old('name', $user['name'] ?? '')) ?>"></div>
    <div class="col-md-3"><label class="form-label">Username *</label>
      <input class="form-control" name="username" required value="<?= esc(old('username', $user['username'] ?? '')) ?>"></div>
    <div class="col-md-3"><label class="form-label">Status</label>
      <select class="form-select" name="status">
        <?php foreach (['active','disabled'] as $s): ?>
          <option value="<?= $s ?>" <?= ($user['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="col-md-6"><label class="form-label">Email *</label>
      <input type="email" class="form-control" name="email" required value="<?= esc(old('email', $user['email'] ?? '')) ?>"></div>
    <div class="col-md-3"><label class="form-label">Role *</label>
      <select class="form-select" name="role_id" required>
        <?php foreach ($roles as $r): ?>
          <option value="<?= $r['id'] ?>" <?= ($user['role_id'] ?? '') == $r['id'] ? 'selected' : '' ?>><?= esc($r['name']) ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="col-md-3"><label class="form-label">Password<?= $isEdit ? ' (leave blank to keep)' : ' *' ?></label>
      <input type="password" class="form-control" name="password" <?= $isEdit ? '' : 'required' ?> minlength="8"></div>
  </div>
  <div class="mt-3">
    <button class="btn btn-primary"><?= $isEdit ? 'Update' : 'Create' ?></button>
    <a class="btn btn-link" href="<?= site_url('users') ?>">Cancel</a>
  </div>
</form>
<?= $this->endSection() ?>
