<!doctype html>
<html><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Forgot Password – MySmartMemberHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:#1f2d3d;min-height:100vh;display:flex;align-items:center;justify-content:center}</style>
</head><body>
<?php helper('format'); ?>
<div class="card shadow" style="max-width:420px;width:100%">
  <div class="card-body p-4">
    <h4 class="mb-3">Forgot password</h4>
    <?= flash_alerts() ?>
    <p class="text-muted small">Enter your email and we will issue a reset link (printed to logs in dev).</p>
    <form method="post" action="<?= site_url('forgot-password') ?>">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email" required>
      </div>
      <button class="btn btn-primary w-100" type="submit">Send reset link</button>
      <div class="text-center mt-3"><a href="<?= site_url('login') ?>" class="small">Back to login</a></div>
    </form>
  </div>
</div>
</body></html>
