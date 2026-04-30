<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<h4>Audit log</h4>
<form class="card card-body shadow-sm mb-3" method="get">
  <div class="row g-2 align-items-end">
    <div class="col-md-4"><input class="form-control form-control-sm" name="q" value="<?= esc($_GET['q'] ?? '') ?>" placeholder="User name or entity ID"></div>
    <div class="col-md-3"><input class="form-control form-control-sm" name="entity" value="<?= esc($_GET['entity'] ?? '') ?>" placeholder="entity (e.g. members)"></div>
    <div class="col-md-3"><input class="form-control form-control-sm" name="action" value="<?= esc($_GET['action'] ?? '') ?>" placeholder="action (create/update/...)"></div>
    <div class="col-md-2"><button class="btn btn-secondary btn-sm w-100">Filter</button></div>
  </div>
</form>
<div class="card shadow-sm"><div class="table-responsive"><table class="table mb-0 align-middle">
  <thead><tr><th>When</th><th>User</th><th>Action</th><th>Entity</th><th>ID</th><th>Diff</th><th>IP</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td class="text-nowrap"><?= fdatetime($r['created_at'], 'd M Y H:i:s') ?></td>
      <td><?= esc($r['user_name'] ?? '—') ?></td>
      <td><span class="badge bg-secondary"><?= esc($r['action']) ?></span></td>
      <td><code><?= esc($r['entity']) ?></code></td>
      <td><?= esc($r['entity_id']) ?></td>
      <td><a data-bs-toggle="collapse" href="#diff-<?= $r['id'] ?>">view</a>
        <div class="collapse" id="diff-<?= $r['id'] ?>"><pre class="small bg-light p-2 mt-1"><?= esc(($r['new_values'] ?? '') . "\n" . ($r['old_values'] ?? '')) ?></pre></div></td>
      <td class="small text-muted"><?= esc($r['ip']) ?></td>
    </tr>
  <?php endforeach; ?>
  <?php if (! $rows): ?><tr><td colspan="7" class="text-center text-muted py-4">No audit entries.</td></tr><?php endif; ?>
  </tbody>
</table></div>
<div class="card-body"><?= view('partials/pagination') ?></div></div>
<?= $this->endSection() ?>
