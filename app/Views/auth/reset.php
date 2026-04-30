<!doctype html>
<html><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reset Password – MySmartMemberHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:#1f2d3d;min-height:100vh;display:flex;align-items:center;justify-content:center}</style>
</head><body>
<?php helper('format'); ?>
<div class="card shadow" style="max-width:420px;width:100%">
  <div class="card-body p-4">
    <h4 class="mb-3">Set a new password</h4>
    <?= flash_alerts() ?>
    <form method="post" action="<?= site_url('reset-password/' . esc($token)) ?>">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label">New password</label>
        <input type="password" class="form-control" name="password" minlength="8" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Confirm password</label>
        <input type="password" class="form-control" name="password_confirm" minlength="8" required>
      </div>
      <button class="btn btn-primary w-100" type="submit">Reset password</button>
    </form>
  </div>
</div>
</body></html>
