<!doctype html>
<html lang="en"><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign In – MySmartMemberHub</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
:root {
  --primary: #6366f1;
  --primary-light: #818cf8;
  --primary-dark: #4f46e5;
  --accent: #06b6d4;
}
* { box-sizing: border-box; }
body {
  font-family: 'Inter', -apple-system, sans-serif;
  background: #0f172a;
  min-height: 100vh;
  display: flex;
  margin: 0;
  -webkit-font-smoothing: antialiased;
}

/* Decorative side */
.login-left {
  flex: 1;
  display: none;
  position: relative;
  overflow: hidden;
}
@media (min-width: 992px) { .login-left { display: flex; align-items: center; justify-content: center; } }
.login-left::before {
  content: '';
  position: absolute; inset: 0;
  background:
    radial-gradient(ellipse 500px 400px at 30% 40%, rgba(99,102,241,.2), transparent),
    radial-gradient(ellipse 400px 350px at 70% 60%, rgba(6,182,212,.15), transparent);
}
.login-left-content { position: relative; z-index:1; text-align:center; max-width:420px; padding:2rem; }
.login-left-content .brand-icon {
  width: 64px; height: 64px;
  background: linear-gradient(135deg, var(--primary), var(--accent));
  border-radius: 18px;
  display: inline-flex; align-items: center; justify-content: center;
  font-size: 1.8rem; color: #fff;
  margin-bottom: 1.5rem;
}
.login-left-content h2 {
  font-size: 1.75rem; font-weight: 800; color: #f1f5f9;
  letter-spacing: -.03em; margin-bottom: .75rem;
}
.login-left-content p { color: #94a3b8; line-height: 1.7; font-size: .95rem; }

/* Form side */
.login-right {
  width: 100%;
  max-width: 480px;
  display: flex; align-items: center; justify-content: center;
  padding: 2rem;
}
@media (min-width: 992px) { .login-right { min-width: 480px; background: #fff; } }
@media (max-width: 991.98px) { .login-right { margin: auto; } }

.login-card {
  width: 100%;
  max-width: 380px;
}
@media (max-width: 991.98px) {
  .login-card {
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 20px;
    padding: 2.5rem 2rem;
    backdrop-filter: blur(12px);
  }
}
.login-card .logo {
  display: flex; align-items: center; gap: .6rem;
  font-weight: 700; font-size: 1.1rem;
  margin-bottom: 2rem;
  text-decoration: none;
}
@media (min-width: 992px) { .login-card .logo { color: #1e293b; } }
@media (max-width: 991.98px) { .login-card .logo { color: #fff; } }
.logo-icon-sm {
  width: 32px; height: 32px;
  background: linear-gradient(135deg, var(--primary), var(--accent));
  border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: .9rem;
}

.login-card h3 {
  font-size: 1.5rem; font-weight: 700; letter-spacing: -.03em;
  margin-bottom: .25rem;
}
@media (min-width: 992px) { .login-card h3 { color: #0f172a; } }
@media (max-width: 991.98px) { .login-card h3 { color: #f1f5f9; } }
.login-card .subtitle {
  font-size: .9rem; margin-bottom: 1.75rem;
}
@media (min-width: 992px) { .login-card .subtitle { color: #64748b; } }
@media (max-width: 991.98px) { .login-card .subtitle { color: #94a3b8; } }

.login-card .form-label {
  font-weight: 500; font-size: .85rem; margin-bottom: .3rem;
}
@media (min-width: 992px) { .login-card .form-label { color: #334155; } }
@media (max-width: 991.98px) { .login-card .form-label { color: #cbd5e1; } }

.login-card .form-control {
  border-radius: 10px; padding: .6rem 1rem; font-size: .9rem;
  transition: all .2s ease;
}
@media (min-width: 992px) {
  .login-card .form-control { border: 1px solid #e2e8f0; background: #f8fafc; }
  .login-card .form-control:focus { border-color: var(--primary-light); box-shadow: 0 0 0 3px rgba(99,102,241,.1); background: #fff; }
}
@media (max-width: 991.98px) {
  .login-card .form-control { border: 1px solid rgba(255,255,255,.12); background: rgba(255,255,255,.06); color: #f1f5f9; }
  .login-card .form-control:focus { border-color: var(--primary-light); box-shadow: 0 0 0 3px rgba(99,102,241,.15); background: rgba(255,255,255,.1); }
  .login-card .form-control::placeholder { color: #64748b; }
}

.btn-signin {
  width: 100%;
  padding: .7rem;
  border-radius: 10px;
  border: none;
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  color: #fff;
  font-weight: 600;
  font-size: .95rem;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(99,102,241,.3);
  transition: all .25s ease;
}
.btn-signin:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(99,102,241,.4);
}
.btn-signin:active { transform: translateY(0); }

.forgot-link {
  font-size: .85rem;
  text-decoration: none;
  transition: color .2s;
}
@media (min-width: 992px) { .forgot-link { color: var(--primary); } .forgot-link:hover { color: var(--primary-dark); } }
@media (max-width: 991.98px) { .forgot-link { color: var(--primary-light); } .forgot-link:hover { color: #fff; } }

.creds-box {
  margin-top: 1.5rem;
  padding: 1rem;
  border-radius: 10px;
  font-size: .78rem;
  line-height: 1.7;
}
@media (min-width: 992px) { .creds-box { background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; } }
@media (max-width: 991.98px) { .creds-box { background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.08); color: #94a3b8; } }
.creds-box strong { font-weight: 600; }
@media (min-width: 992px) { .creds-box strong { color: #334155; } }
@media (max-width: 991.98px) { .creds-box strong { color: #cbd5e1; } }
.creds-box code {
  padding: .15em .4em;
  border-radius: 4px;
  font-size: .78rem;
}
@media (min-width: 992px) { .creds-box code { background: #e0e7ff; color: var(--primary-dark); } }
@media (max-width: 991.98px) { .creds-box code { background: rgba(99,102,241,.15); color: var(--primary-light); } }

.alert {
  border-radius: 10px; border: none; font-size: .875rem; font-weight: 450;
}
.alert-danger { background: rgba(239,68,68,.1); color: #dc2626; }
.alert-success { background: rgba(16,185,129,.1); color: #059669; }

@keyframes fadeUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
.login-card { animation: fadeUp .5s ease-out; }
</style>
</head><body>

<!-- Left decorative panel -->
<div class="login-left">
  <div class="login-left-content">
    <div class="brand-icon"><i class="bi bi-people-fill"></i></div>
    <h2>Welcome back</h2>
    <p>Manage your membership operations effortlessly. Track members, automate invoicing, and generate reports — all in one place.</p>
  </div>
</div>

<!-- Right login panel -->
<div class="login-right">
  <div class="login-card">
    <a href="<?= site_url('/') ?>" class="logo">
      <div class="logo-icon-sm"><i class="bi bi-people-fill"></i></div>
      MemberHub
    </a>

    <h3>Sign in</h3>
    <p class="subtitle">Enter your credentials to continue</p>

    <?php helper('format'); ?>
    <?= flash_alerts() ?>

    <form method="post" action="<?= site_url('login') ?>">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label">Email or username</label>
        <input class="form-control" name="login" autofocus required value="<?= esc(old('login')) ?>" placeholder="Enter your email or username">
      </div>
      <div class="mb-3">
        <label class="form-label d-flex justify-content-between">
          Password
          <a href="<?= site_url('forgot-password') ?>" class="forgot-link">Forgot?</a>
        </label>
        <input class="form-control" type="password" name="password" required placeholder="Enter your password">
      </div>
      <button class="btn-signin" type="submit"><i class="bi bi-box-arrow-in-right me-1"></i> Sign in</button>
    </form>

    <div class="creds-box">
      <strong>Demo credentials</strong><br>
      Admin: <code>admin</code> / <code>Admin@123</code><br>
      Staff: <code>staff</code> / <code>Staff@123</code><br>
      Finance: <code>finance</code> / <code>Finance@123</code><br>
      Manager: <code>manager</code> / <code>Manager@123</code>
    </div>
  </div>
</div>

</body></html>
