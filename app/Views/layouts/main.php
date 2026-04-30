<?php
$title    = $title    ?? 'MySmartMemberHub';
$user     = current_user();
$roleSlug = current_role_slug();
?><!doctype html>
<html lang="en"><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($title) ?> – MySmartMemberHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
  body { background:#f5f7fb; }
  .sidebar { width:240px; min-height:100vh; background:#1f2d3d; color:#cfd8e3; }
  .sidebar a { color:#cfd8e3; text-decoration:none; display:block; padding:.55rem 1rem; border-radius:.25rem; }
  .sidebar a:hover, .sidebar a.active { background:#2d3e57; color:#fff; }
  .sidebar h6 { color:#7f8fa6; font-size:.7rem; letter-spacing:.05em; padding:0 1rem; margin-top:1rem; }
  .topbar { height:60px; background:#fff; border-bottom:1px solid #e3e8ef; }
  .main { padding:1.5rem; }
  .card { border:1px solid #eef0f4; }
  .table thead { background:#f8fafc; }
  .brand { font-weight:600; color:#fff; padding:1rem; font-size:1.05rem; }
</style>
</head><body>
<div class="d-flex">
  <aside class="sidebar d-none d-md-flex flex-column">
    <div class="brand"><i class="bi bi-people-fill me-2"></i>MemberHub</div>

    <nav class="px-2">
      <a href="<?= site_url('/') ?>" class="<?= url_is('/') ? 'active' : '' ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>

      <h6>Members</h6>
      <?php if (can('member.view')): ?>
        <a href="<?= site_url('members') ?>" class="<?= url_is('members*') ? 'active' : '' ?>"><i class="bi bi-person-vcard me-2"></i>Members</a>
      <?php endif; ?>
      <?php if (can('plan.manage')): ?>
        <a href="<?= site_url('plans') ?>" class="<?= url_is('plans*') ? 'active' : '' ?>"><i class="bi bi-card-list me-2"></i>Plans</a>
      <?php endif; ?>

      <h6>Finance</h6>
      <?php if (can('payment.view') && module_enabled('payments')): ?>
        <a href="<?= site_url('payments') ?>" class="<?= url_is('payments*') ? 'active' : '' ?>"><i class="bi bi-cash-coin me-2"></i>Payments</a>
      <?php endif; ?>
      <?php if (can('invoice.view') && module_enabled('invoices')): ?>
        <a href="<?= site_url('invoices') ?>" class="<?= url_is('invoices*') ? 'active' : '' ?>"><i class="bi bi-file-earmark-text me-2"></i>Invoices</a>
      <?php endif; ?>
      <?php if (can('receipt.view') && module_enabled('receipts')): ?>
        <a href="<?= site_url('receipts') ?>" class="<?= url_is('receipts*') ? 'active' : '' ?>"><i class="bi bi-receipt me-2"></i>Receipts</a>
      <?php endif; ?>
      <?php if (can('report.view') && module_enabled('reports')): ?>
        <a href="<?= site_url('reports') ?>" class="<?= url_is('reports*') ? 'active' : '' ?>"><i class="bi bi-graph-up me-2"></i>Reports</a>
      <?php endif; ?>

      <h6>Admin</h6>
      <?php if (can('user.manage')): ?>
        <a href="<?= site_url('users') ?>" class="<?= url_is('users*') ? 'active' : '' ?>"><i class="bi bi-people me-2"></i>Users</a>
      <?php endif; ?>
      <?php if (can('role.manage')): ?>
        <a href="<?= site_url('roles') ?>" class="<?= url_is('roles*') ? 'active' : '' ?>"><i class="bi bi-shield-lock me-2"></i>Roles</a>
      <?php endif; ?>
      <?php if (can('audit.view') && module_enabled('audit_log')): ?>
        <a href="<?= site_url('audit-log') ?>" class="<?= url_is('audit-log*') ? 'active' : '' ?>"><i class="bi bi-clipboard-data me-2"></i>Audit Log</a>
      <?php endif; ?>
      <?php if (can('setting.manage')): ?>
        <a href="<?= site_url('settings') ?>" class="<?= url_is('settings*') ? 'active' : '' ?>"><i class="bi bi-gear me-2"></i>Settings</a>
      <?php endif; ?>
    </nav>
  </aside>

  <div class="flex-grow-1 d-flex flex-column">
    <div class="topbar d-flex align-items-center justify-content-between px-3">
      <form class="d-flex" action="<?= site_url('members') ?>" method="get">
        <input class="form-control form-control-sm" type="search" name="q" placeholder="Search member by name / IC / email / ID" style="min-width:340px" value="<?= esc($_GET['q'] ?? '') ?>">
      </form>
      <div class="d-flex align-items-center gap-3">
        <?php if (module_enabled('notifications')): ?>
          <a href="<?= site_url('notifications') ?>" class="text-secondary"><i class="bi bi-bell fs-5"></i></a>
        <?php endif; ?>
        <div class="dropdown">
          <a class="dropdown-toggle text-decoration-none text-dark" data-bs-toggle="dropdown" href="#">
            <i class="bi bi-person-circle me-1"></i>
            <?= esc($user['name'] ?? 'Guest') ?>
            <small class="text-muted">(<?= esc($user['role_name'] ?? '') ?>)</small>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="<?= site_url('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
          </ul>
        </div>
      </div>
    </div>

    <main class="main">
      <?= flash_alerts() ?>
      <?= $this->renderSection('content') ?>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?= $this->renderSection('scripts') ?>
</body></html>
