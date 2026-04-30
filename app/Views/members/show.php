<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0"><?= esc($member['name']) ?> <small class="text-muted"><?= esc($member['membership_id']) ?></small></h4>
  <div>
    <?php if (can('member.edit')): ?>
      <a class="btn btn-sm btn-outline-secondary" href="<?= site_url('members/' . $member['id'] . '/edit') ?>"><i class="bi bi-pencil me-1"></i>Edit</a>
    <?php endif; ?>
    <?php if (can('member.edit')): ?>
      <form method="post" class="d-inline" action="<?= site_url('members/' . $member['id'] . '/renew') ?>">
        <?= csrf_field() ?>
        <button class="btn btn-sm btn-outline-success"><i class="bi bi-arrow-repeat me-1"></i>Renew</button>
      </form>
    <?php endif; ?>
    <?php if (can('member.delete')): ?>
      <form method="post" class="d-inline" action="<?= site_url('members/' . $member['id'] . '/delete') ?>" onsubmit="return confirm('Delete this member?')">
        <?= csrf_field() ?>
        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete</button>
      </form>
    <?php endif; ?>
  </div>
</div>

<div class="row g-3">
  <div class="col-md-5">
    <div class="card shadow-sm">
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-5">Status</dt><dd class="col-7"><?= status_badge($member['status']) ?></dd>
          <dt class="col-5">Plan</dt><dd class="col-7"><?= esc($member['plan_name'] ?? '-') ?></dd>
          <dt class="col-5">Joined</dt><dd class="col-7"><?= fdate($member['joined_date']) ?></dd>
          <dt class="col-5">Expiry</dt><dd class="col-7"><?= fdate($member['expiry_date']) ?></dd>
          <dt class="col-5">IC</dt><dd class="col-7"><?= esc($member['ic_no']) ?: '-' ?></dd>
          <dt class="col-5">Email</dt><dd class="col-7"><?= esc($member['email']) ?: '-' ?></dd>
          <dt class="col-5">Phone</dt><dd class="col-7"><?= esc($member['phone']) ?: '-' ?></dd>
          <dt class="col-5">Address</dt><dd class="col-7"><?= nl2br(esc($member['address'] ?? '')) ?></dd>
        </dl>
      </div>
    </div>
  </div>

  <div class="col-md-7">
    <div class="card shadow-sm mb-3">
      <div class="card-header bg-white d-flex justify-content-between"><strong>Invoices</strong>
        <?php if (can('invoice.generate')): ?>
          <form method="post" action="<?= site_url('invoices/generate-for-member/' . $member['id']) ?>">
            <?= csrf_field() ?>
            <button class="btn btn-sm btn-outline-primary">Generate invoice</button>
          </form>
        <?php endif; ?>
      </div>
      <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
          <thead><tr><th>No.</th><th>Issued</th><th>Total</th><th>Status</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($invoices as $i): ?>
            <tr>
              <td><a href="<?= site_url('invoices/' . $i['id']) ?>"><?= esc($i['invoice_no']) ?></a></td>
              <td><?= fdate($i['issued_at']) ?></td>
              <td><?= money($i['total']) ?></td>
              <td><?= status_badge($i['status']) ?></td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-secondary" href="<?= site_url('invoices/' . $i['id'] . '/pdf') ?>" target="_blank">PDF</a>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (! $invoices): ?><tr><td colspan="5" class="text-muted text-center">No invoices.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-header bg-white d-flex justify-content-between"><strong>Payments</strong>
        <?php if (can('payment.create')): ?>
          <a class="btn btn-sm btn-outline-primary" href="<?= site_url('payments/create?member_id=' . $member['id']) ?>">Record payment</a>
        <?php endif; ?>
      </div>
      <div class="table-responsive">
        <table class="table table-sm mb-0 align-middle">
          <thead><tr><th>Date</th><th>Amount</th><th>Method</th><th>Reference</th><th>Status</th></tr></thead>
          <tbody>
            <?php foreach ($payments as $p): ?>
            <tr>
              <td><?= fdate($p['payment_date']) ?></td>
              <td><?= money($p['amount']) ?></td>
              <td><?= esc(ucfirst($p['method'])) ?></td>
              <td><?= esc($p['reference_no']) ?></td>
              <td><?= status_badge($p['status']) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (! $payments): ?><tr><td colspan="5" class="text-muted text-center">No payments.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
