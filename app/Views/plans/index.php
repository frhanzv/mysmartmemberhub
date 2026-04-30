<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Membership plans</h4>
  <a class="btn btn-primary btn-sm" href="<?= site_url('plans/create') ?>"><i class="bi bi-plus-lg me-1"></i>New plan</a>
</div>
<div class="card shadow-sm">
  <div class="table-responsive"><table class="table mb-0 align-middle">
    <thead><tr><th>Code</th><th>Name</th><th>Price</th><th>Duration</th><th>Active</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($plans as $p): ?>
      <tr>
        <td><code><?= esc($p['code']) ?></code></td>
        <td><?= esc($p['name']) ?></td>
        <td><?= money($p['price']) ?></td>
        <td><?= esc($p['duration_months']) ?> months</td>
        <td><?= $p['is_active'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
        <td class="text-end">
          <a class="btn btn-sm btn-outline-secondary" href="<?= site_url('plans/' . $p['id'] . '/edit') ?>">Edit</a>
          <form method="post" class="d-inline" action="<?= site_url('plans/' . $p['id'] . '/delete') ?>" onsubmit="return confirm('Delete?')">
            <?= csrf_field() ?>
            <button class="btn btn-sm btn-outline-danger">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <div class="card-body"><?= view('partials/pagination') ?></div>
</div>
<?= $this->endSection() ?>
