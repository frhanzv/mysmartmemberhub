<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Users</h4>
  <a class="btn btn-primary btn-sm" href="<?= site_url('users/create') ?>"><i class="bi bi-plus-lg me-1"></i>New user</a>
</div>
<div class="card shadow-sm">
  <div class="table-responsive"><table class="table mb-0 align-middle">
    <thead><tr><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Last login</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
      <tr>
        <td><?= esc($u['name']) ?></td>
        <td><code><?= esc($u['username']) ?></code></td>
        <td><?= esc($u['email']) ?></td>
        <td><?= esc($u['role_name']) ?></td>
        <td><?= status_badge($u['status']) ?></td>
        <td><?= fdatetime($u['last_login_at']) ?></td>
        <td class="text-end">
          <a class="btn btn-sm btn-outline-secondary" href="<?= site_url('users/' . $u['id'] . '/edit') ?>">Edit</a>
          <form method="post" class="d-inline" action="<?= site_url('users/' . $u['id'] . '/delete') ?>" onsubmit="return confirm('Delete user?')">
            <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <div class="card-body"><?= view('partials/pagination') ?></div>
</div>
<?= $this->endSection() ?>
