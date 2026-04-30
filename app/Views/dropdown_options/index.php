<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <h4 class="mb-0">Dropdown Options</h4>
  <a href="<?= site_url('settings/dropdown-options/create') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add option</a>
</div>

<?php if (empty($grouped)): ?>
  <div class="alert alert-info">No dropdown options found. Click <strong>Add option</strong> to get started.</div>
<?php else: ?>
  <?php foreach ($grouped as $category => $items): ?>
    <div class="card shadow-sm mb-3">
      <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <strong><i class="bi bi-list-ul me-1"></i><?= esc(ucwords(str_replace('_', ' ', $category))) ?></strong>
        <span class="badge bg-secondary"><?= count($items) ?> option<?= count($items) !== 1 ? 's' : '' ?></span>
      </div>
      <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
          <thead><tr>
            <th style="width:50px">#</th>
            <th>Label</th>
            <th>Value</th>
            <th style="width:80px">Order</th>
            <th style="width:80px">Active</th>
            <th style="width:130px" class="text-end">Actions</th>
          </tr></thead>
          <tbody>
            <?php foreach ($items as $opt): ?>
              <tr<?= ! $opt['is_active'] ? ' class="table-secondary text-muted"' : '' ?>>
                <td><?= $opt['id'] ?></td>
                <td><?= esc($opt['label']) ?></td>
                <td><code><?= esc($opt['value']) ?></code></td>
                <td><?= $opt['sort_order'] ?></td>
                <td><?= $opt['is_active'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
                <td class="text-end">
                  <a href="<?= site_url('settings/dropdown-options/' . $opt['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                  <form class="d-inline" method="post" action="<?= site_url('settings/dropdown-options/' . $opt['id'] . '/delete') ?>" onsubmit="return confirm('Delete this option?')">
                    <?= csrf_field() ?>
                    <button class="btn btn-sm btn-outline-danger">Delete</button>
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

<a href="<?= site_url('settings') ?>" class="btn btn-link"><i class="bi bi-arrow-left me-1"></i>Back to settings</a>
<?= $this->endSection() ?>
