<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<h4>System settings</h4>
<form method="post" enctype="multipart/form-data" action="<?= site_url('settings/update') ?>">
  <?= csrf_field() ?>
  <?php foreach ($grouped as $group => $items): ?>
    <div class="card shadow-sm mb-3">
      <div class="card-header bg-white"><strong><?= esc(ucfirst($group)) ?></strong></div>
      <div class="card-body">
        <?php foreach ($items as $s): ?>
          <div class="row g-3 align-items-center mb-2">
            <label class="col-md-4 col-form-label"><code><?= esc($s['key_name']) ?></code></label>
            <div class="col-md-8">
              <?php if (in_array($s['key_name'], ['payment.instructions','company.address','invoice.footer'], true)): ?>
                <textarea class="form-control" rows="3" name="settings[<?= esc($s['key_name']) ?>]"><?= esc($s['value']) ?></textarea>
              <?php elseif ($s['key_name'] === 'branding.logo'): ?>
                <input type="file" class="form-control" name="logo" accept="image/*">
                <?php if ($s['value']): ?><small class="text-muted d-block">Current: <?= esc($s['value']) ?></small><?php endif; ?>
              <?php else: ?>
                <input class="form-control" name="settings[<?= esc($s['key_name']) ?>]" value="<?= esc($s['value']) ?>">
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
  <button class="btn btn-primary">Save settings</button>
</form>
<?= $this->endSection() ?>
