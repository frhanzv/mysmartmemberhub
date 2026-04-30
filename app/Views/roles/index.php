<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<h4>Roles</h4>
<div class="card shadow-sm"><div class="table-responsive"><table class="table mb-0 align-middle">
  <thead><tr><th>Name</th><th>Slug</th><th>Description</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($roles as $r): ?>
    <tr>
      <td><?= esc($r['name']) ?></td>
      <td><code><?= esc($r['slug']) ?></code></td>
      <td><?= esc($r['description']) ?></td>
      <td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="<?= site_url('roles/' . $r['id'] . '/edit') ?>">Edit permissions</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div></div>
<?= $this->endSection() ?>
