<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Members</h4>
  <div>
    <?php if (can('member.import')): ?>
      <a class="btn btn-outline-secondary btn-sm" href="<?= site_url('members/import') ?>"><i class="bi bi-upload me-1"></i>Import</a>
    <?php endif; ?>
    <?php if (can('report.export') && module_enabled('exports')): ?>
      <a class="btn btn-outline-secondary btn-sm" href="<?= site_url('members/export') ?>"><i class="bi bi-download me-1"></i>Export</a>
    <?php endif; ?>
    <?php if (can('member.create')): ?>
      <a class="btn btn-primary btn-sm" href="<?= site_url('members/create') ?>"><i class="bi bi-plus-lg me-1"></i>New member</a>
    <?php endif; ?>
  </div>
</div>

<form class="card card-body shadow-sm mb-3" method="get">
  <div class="row g-2 align-items-end">
    <div class="col-md-4"><label class="form-label small">Search</label>
      <input class="form-control form-control-sm" name="q" value="<?= esc($filters['q'] ?? '') ?>" placeholder="Name / IC / email / membership ID"></div>
    <div class="col-md-3"><label class="form-label small">Status</label>
      <select class="form-select form-select-sm" name="status">
        <option value="">All</option>
        <?php foreach (['active','expired','suspended'] as $s): ?>
        <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="col-md-3"><label class="form-label small">Plan</label>
      <select class="form-select form-select-sm" name="plan_id">
        <option value="">All</option>
        <?php foreach ($plans as $p): ?>
        <option value="<?= $p['id'] ?>" <?= ($filters['plan_id'] ?? '') == $p['id'] ? 'selected' : '' ?>><?= esc($p['name']) ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="col-md-2"><button class="btn btn-secondary btn-sm w-100">Filter</button></div>
  </div>
</form>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th>ID</th><th>Name</th><th>Plan</th><th>Joined</th><th>Expiry</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
          <td><code><?= esc($r['membership_id']) ?></code></td>
          <td>
            <a href="<?= site_url('members/' . $r['id']) ?>" class="fw-semibold text-decoration-none"><?= esc($r['name']) ?></a>
            <?php if ($r['email']): ?><small class="d-block text-muted"><?= esc($r['email']) ?></small><?php endif; ?>
          </td>
          <td><?= esc($r['plan_name'] ?? '-') ?></td>
          <td><?= fdate($r['joined_date']) ?></td>
          <td><?= fdate($r['expiry_date']) ?></td>
          <td><?= status_badge($r['status']) ?></td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-primary" href="<?= site_url('members/' . $r['id']) ?>">View</a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (! $rows): ?><tr><td colspan="7" class="text-center text-muted py-4">No members found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="card-body"><?= view('partials/pagination') ?></div>
</div>
<?= $this->endSection() ?>
