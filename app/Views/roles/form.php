<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<h4>Edit role: <?= esc($role['name']) ?></h4>
<form method="post" action="<?= site_url('roles/' . $role['id'] . '/update') ?>">
  <?= csrf_field() ?>
  <div class="card card-body shadow-sm mb-3">
    <div class="row g-3">
      <div class="col-md-4"><label class="form-label">Name</label>
        <input class="form-control" name="name" required value="<?= esc($role['name']) ?>"></div>
      <div class="col-md-2"><label class="form-label">Slug</label>
        <input class="form-control" disabled value="<?= esc($role['slug']) ?>"></div>
      <div class="col-md-6"><label class="form-label">Description</label>
        <input class="form-control" name="description" value="<?= esc($role['description']) ?>"></div>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-header bg-white"><strong>Permissions</strong></div>
    <div class="card-body">
      <?php foreach ($grouped as $group => $perms): ?>
        <div class="mb-3">
          <h6 class="text-uppercase text-muted small"><?= esc($group) ?></h6>
          <div class="row">
            <?php foreach ($perms as $p): ?>
              <div class="col-md-4 mb-2">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= $p['id'] ?>"
                         id="perm-<?= $p['id'] ?>" <?= in_array($p['id'], $assigned, true) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="perm-<?= $p['id'] ?>">
                    <code><?= esc($p['slug']) ?></code>
                    <?php if ($p['description']): ?><small class="text-muted d-block"><?= esc($p['description']) ?></small><?php endif; ?>
                  </label>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="mt-3">
    <button class="btn btn-primary">Save</button>
    <a class="btn btn-link" href="<?= site_url('roles') ?>">Cancel</a>
  </div>
</form>
<?= $this->endSection() ?>
