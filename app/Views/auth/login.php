<!doctype html>
<html lang="en"><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login – MySmartMemberHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
<style>body{background:linear-gradient(135deg,#1f2d3d,#3b4f72);min-height:100vh;display:flex;align-items:center;justify-content:center}</style>
</head><body>
<div class="card shadow" style="max-width:420px;width:100%">
  <div class="card-body p-4">
    <div class="text-center mb-3">
      <i class="bi bi-people-fill fs-1 text-primary"></i>
      <h4 class="mt-2 mb-0">MySmartMemberHub</h4>
      <small class="text-muted">Sign in to continue</small>
    </div>

    <?php helper('format'); ?>
    <?= flash_alerts() ?>

    <form method="post" action="<?= site_url('login') ?>">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label">Email or username</label>
        <input class="form-control" name="login" autofocus required value="<?= esc(old('login')) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input class="form-control" type="password" name="password" required>
      </div>
      <button class="btn btn-primary w-100" type="submit"><i class="bi bi-box-arrow-in-right me-1"></i>Login</button>
      <div class="text-center mt-3">
        <a href="<?= site_url('forgot-password') ?>" class="small">Forgot password?</a>
      </div>
    </form>

    <hr>
    <div class="small text-muted">
      <strong>Default credentials</strong><br>
      Super Admin: <code>admin</code> / <code>Admin@123</code><br>
      Staff: <code>staff</code> / <code>Staff@123</code><br>
      Finance: <code>finance</code> / <code>Finance@123</code><br>
      Manager: <code>manager</code> / <code>Manager@123</code>
    </div>
  </div>
</div>
</body></html>
