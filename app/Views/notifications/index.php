<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Notifications</h4>
  <form method="post" action="<?= site_url('notifications/mark-all-read') ?>">
    <?= csrf_field() ?>
    <button class="btn btn-outline-secondary btn-sm">Mark all read</button>
  </form>
</div>
<div class="card shadow-sm">
  <ul class="list-group list-group-flush">
  <?php foreach ($rows as $n): ?>
    <li class="list-group-item d-flex justify-content-between <?= $n['read_at'] ? 'text-muted' : '' ?>">
      <div>
        <div><strong><?= esc($n['title']) ?></strong> <small class="text-muted">· <?= esc($n['type']) ?></small></div>
        <small><?= esc($n['message']) ?></small>
      </div>
      <div class="text-end small text-muted">
        <?= fdatetime($n['created_at']) ?>
        <?php if ($n['link']): ?><br><a href="<?= esc($n['link']) ?>">Open</a><?php endif; ?>
      </div>
    </li>
  <?php endforeach; ?>
  <?php if (! $rows): ?><li class="list-group-item text-muted text-center py-4">No notifications.</li><?php endif; ?>
  </ul>
  <div class="card-body"><?= view('partials/pagination') ?></div>
</div>
<?= $this->endSection() ?>
