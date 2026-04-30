<?php
$title    = $title    ?? 'MySmartMemberHub';
$user     = current_user();
$roleSlug = current_role_slug();
$initials = strtoupper(substr($user['name'] ?? 'G', 0, 1));
?><!doctype html>
<html lang="en"><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($title) ?> – MySmartMemberHub</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= base_url('css/app.css') ?>" rel="stylesheet">
</head><body>

<!-- ═══ SIDEBAR ═══ -->
<aside class="sidebar d-none d-md-flex">
  <div class="brand">
    <div class="brand-icon"><i class="bi bi-people-fill"></i></div>
    MemberHub
  </div>

  <nav class="px-2 flex-grow-1">
    <a href="<?= site_url('/') ?>" class="<?= url_is('/') ? 'active' : '' ?>"><i class="bi bi-grid-1x2 me-2"></i>Dashboard</a>

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

<!-- ═══ CONTENT ═══ -->
<div class="content-wrapper">
  <div class="topbar d-flex align-items-center justify-content-between px-4">
    <div class="search-box">
      <i class="bi bi-search"></i>
      <form action="<?= site_url('members') ?>" method="get" style="display:contents">
        <input class="form-control form-control-sm" type="search" name="q"
               placeholder="Search members…" value="<?= esc($_GET['q'] ?? '') ?>">
      </form>
    </div>
    <div class="d-flex align-items-center gap-2">
      <?php if (module_enabled('notifications')): ?>
        <a href="<?= site_url('notifications') ?>" class="notif-btn"><i class="bi bi-bell fs-5"></i></a>
      <?php endif; ?>
      <div class="dropdown">
        <a class="user-menu dropdown-toggle" data-bs-toggle="dropdown" href="#">
          <div class="user-avatar"><?= $initials ?></div>
          <div class="d-none d-lg-block">
            <div class="fw-semibold small lh-1"><?= esc($user['name'] ?? 'Guest') ?></div>
            <div class="text-muted" style="font-size:.7rem"><?= esc($user['role_name'] ?? '') ?></div>
          </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="border-radius:var(--radius)">
          <li><a class="dropdown-item py-2" href="<?= site_url('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
        </ul>
      </div>
    </div>
  </div>

  <main class="main">
    <?= flash_alerts() ?>
    <?= $this->renderSection('content') ?>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?= $this->renderSection('scripts') ?>
</body></html>
