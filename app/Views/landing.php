<!doctype html>
<html lang="en"><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>MySmartMemberHub — Modern Membership Management</title>
<meta name="description" content="Streamline your membership operations with MySmartMemberHub. Manage members, payments, invoices and reports — all in one place.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
:root {
  --primary: #6366f1;
  --primary-light: #818cf8;
  --primary-dark: #4f46e5;
  --accent: #06b6d4;
  --surface: #0f172a;
}
* { margin:0; padding:0; box-sizing:border-box; }
body {
  font-family: 'Inter', -apple-system, sans-serif;
  background: var(--surface);
  color: #e2e8f0;
  -webkit-font-smoothing: antialiased;
  overflow-x: hidden;
}

/* ─── NAV ─── */
.landing-nav {
  position: fixed; top:0; left:0; right:0; z-index:100;
  padding: 1.25rem 2rem;
  display: flex; align-items: center; justify-content: space-between;
  background: rgba(15,23,42,.7);
  backdrop-filter: blur(16px);
  border-bottom: 1px solid rgba(255,255,255,.06);
}
.landing-nav .logo {
  display: flex; align-items: center; gap: .65rem;
  font-weight: 700; font-size: 1.15rem; color: #fff;
  text-decoration: none;
}
.landing-nav .logo-icon {
  width: 36px; height: 36px;
  background: linear-gradient(135deg, var(--primary), var(--accent));
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem;
}
.landing-nav .btn-login {
  padding: .55rem 1.75rem;
  border-radius: 100px;
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  color: #fff; font-weight: 600; font-size: .9rem;
  text-decoration: none;
  border: none; cursor: pointer;
  box-shadow: 0 4px 15px rgba(99,102,241,.3);
  transition: all .25s ease;
}
.landing-nav .btn-login:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(99,102,241,.45);
}

/* ─── HERO ─── */
.hero {
  min-height: 100vh;
  display: flex; align-items: center; justify-content: center;
  text-align: center;
  padding: 6rem 2rem 4rem;
  position: relative;
}
.hero::before {
  content: '';
  position: absolute; inset: 0;
  background:
    radial-gradient(ellipse 600px 400px at 20% 30%, rgba(99,102,241,.15), transparent),
    radial-gradient(ellipse 500px 350px at 80% 70%, rgba(6,182,212,.12), transparent),
    radial-gradient(ellipse 400px 300px at 50% 50%, rgba(99,102,241,.06), transparent);
}
.hero-content { position: relative; z-index: 1; max-width: 780px; }
.hero-badge {
  display: inline-flex; align-items: center; gap: .5rem;
  padding: .4rem 1rem;
  border-radius: 100px;
  background: rgba(99,102,241,.1);
  border: 1px solid rgba(99,102,241,.2);
  color: var(--primary-light);
  font-size: .8rem; font-weight: 500;
  margin-bottom: 1.75rem;
}
.hero h1 {
  font-size: clamp(2.5rem, 5vw, 4rem);
  font-weight: 800;
  line-height: 1.1;
  letter-spacing: -.04em;
  margin-bottom: 1.25rem;
}
.hero h1 .gradient-text {
  background: linear-gradient(135deg, var(--primary-light), var(--accent));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
.hero p {
  font-size: 1.15rem;
  color: #94a3b8;
  line-height: 1.7;
  margin-bottom: 2.25rem;
  max-width: 600px;
  margin-left: auto; margin-right: auto;
}
.hero .cta-group { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
.btn-cta {
  padding: .85rem 2.5rem;
  border-radius: 100px;
  font-weight: 600; font-size: 1rem;
  text-decoration: none;
  transition: all .25s ease;
  display: inline-flex; align-items: center; gap: .5rem;
}
.btn-cta-primary {
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  color: #fff;
  box-shadow: 0 4px 20px rgba(99,102,241,.35);
}
.btn-cta-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 30px rgba(99,102,241,.5);
  color: #fff;
}
.btn-cta-outline {
  background: transparent;
  color: #e2e8f0;
  border: 1px solid rgba(255,255,255,.15);
}
.btn-cta-outline:hover {
  background: rgba(255,255,255,.06);
  border-color: rgba(255,255,255,.25);
  color: #fff;
}

/* ─── FEATURES ─── */
.features {
  padding: 5rem 2rem 6rem;
  max-width: 1200px;
  margin: 0 auto;
}
.features .section-label {
  text-align: center;
  font-size: .8rem;
  font-weight: 600;
  letter-spacing: .1em;
  text-transform: uppercase;
  color: var(--primary-light);
  margin-bottom: .75rem;
}
.features h2 {
  text-align: center;
  font-size: 2rem;
  font-weight: 700;
  letter-spacing: -.03em;
  margin-bottom: 3rem;
  color: #f1f5f9;
}
.feature-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
}
.feature-card {
  padding: 2rem;
  border-radius: 16px;
  background: rgba(255,255,255,.03);
  border: 1px solid rgba(255,255,255,.06);
  transition: all .3s ease;
}
.feature-card:hover {
  background: rgba(255,255,255,.06);
  border-color: rgba(99,102,241,.2);
  transform: translateY(-4px);
}
.feature-icon {
  width: 52px; height: 52px;
  border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.4rem;
  margin-bottom: 1.25rem;
}
.feature-card h3 {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: .6rem;
  color: #f1f5f9;
}
.feature-card p {
  font-size: .9rem;
  color: #94a3b8;
  line-height: 1.65;
  margin: 0;
}

/* ─── STATS ─── */
.stats-bar {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 2rem;
  max-width: 900px;
  margin: 0 auto;
  padding: 3rem 2rem;
  text-align: center;
}
.stat-item .stat-num {
  font-size: 2.5rem;
  font-weight: 800;
  letter-spacing: -.04em;
  background: linear-gradient(135deg, var(--primary-light), var(--accent));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
.stat-item .stat-desc {
  font-size: .85rem;
  color: #64748b;
  margin-top: .25rem;
}

/* ─── FOOTER ─── */
.landing-footer {
  text-align: center;
  padding: 2rem;
  color: #475569;
  font-size: .8rem;
  border-top: 1px solid rgba(255,255,255,.06);
}

/* ─── ANIMATIONS ─── */
@keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
.hero-badge { animation: float 4s ease-in-out infinite; }
@keyframes fadeUp { from{opacity:0;transform:translateY(30px)} to{opacity:1;transform:translateY(0)} }
.hero-content > * { animation: fadeUp .7s ease-out both; }
.hero-content > *:nth-child(2) { animation-delay:.15s; }
.hero-content > *:nth-child(3) { animation-delay:.3s; }
.hero-content > *:nth-child(4) { animation-delay:.45s; }
</style>
</head><body>

<!-- NAV -->
<nav class="landing-nav">
  <a href="<?= site_url('/') ?>" class="logo">
    <div class="logo-icon"><i class="bi bi-people-fill"></i></div>
    MemberHub
  </a>
  <a href="<?= site_url('login') ?>" class="btn-login">
    <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
  </a>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-content">
    <div class="hero-badge"><i class="bi bi-lightning-charge-fill"></i> Membership Management, Reimagined</div>
    <h1>Manage your members<br><span class="gradient-text">smarter & faster</span></h1>
    <p>Streamline registrations, automate invoicing, track payments and generate reports — all from one powerful, beautiful dashboard.</p>
    <div class="cta-group">
      <a href="<?= site_url('login') ?>" class="btn-cta btn-cta-primary"><i class="bi bi-rocket-takeoff"></i> Get Started</a>
      <a href="#features" class="btn-cta btn-cta-outline"><i class="bi bi-play-circle"></i> See Features</a>
    </div>
  </div>
</section>

<!-- STATS -->
<div class="stats-bar">
  <div class="stat-item"><div class="stat-num">∞</div><div class="stat-desc">Unlimited Members</div></div>
  <div class="stat-item"><div class="stat-num">4</div><div class="stat-desc">Role-Based Access</div></div>
  <div class="stat-item"><div class="stat-num">PDF</div><div class="stat-desc">Auto Invoices & Receipts</div></div>
  <div class="stat-item"><div class="stat-num">LHDN</div><div class="stat-desc">e-Invoice Ready</div></div>
</div>

<!-- FEATURES -->
<section class="features" id="features">
  <div class="section-label">Features</div>
  <h2>Everything you need to run your organisation</h2>
  <div class="feature-grid">
    <div class="feature-card">
      <div class="feature-icon" style="background:rgba(99,102,241,.12);color:#818cf8"><i class="bi bi-person-vcard-fill"></i></div>
      <h3>Member Management</h3>
      <p>Register, renew, import and track members with auto-generated IDs, expiry alerts, and full profile management.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon" style="background:rgba(6,182,212,.12);color:#22d3ee"><i class="bi bi-cash-stack"></i></div>
      <h3>Payment Tracking</h3>
      <p>Record payments with proof upload, auto-match to invoices, multi-level approval workflow, and full audit trail.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon" style="background:rgba(16,185,129,.12);color:#34d399"><i class="bi bi-file-earmark-pdf"></i></div>
      <h3>Auto Invoices & Receipts</h3>
      <p>Generate professional PDF invoices and receipts automatically. Download, email, or share with QR codes.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon" style="background:rgba(245,158,11,.12);color:#fbbf24"><i class="bi bi-graph-up-arrow"></i></div>
      <h3>Finance Reports</h3>
      <p>Monthly collection, outstanding balances, member analytics — all exportable to Excel with one click.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon" style="background:rgba(239,68,68,.12);color:#f87171"><i class="bi bi-shield-lock-fill"></i></div>
      <h3>Role-Based Access</h3>
      <p>Four default roles with 22 granular permissions. Full control over who can view, create, approve or delete.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon" style="background:rgba(168,85,247,.12);color:#c084fc"><i class="bi bi-file-earmark-code"></i></div>
      <h3>LHDN e-Invoice</h3>
      <p>MyInvois integration with UBL 2.1 document generation, submission, cancellation, and refund-note flows.</p>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer class="landing-footer">
  &copy; <?= date('Y') ?> MySmartMemberHub. Built with CodeIgniter 4.
</footer>

</body></html>
