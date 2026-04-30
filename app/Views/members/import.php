<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<h4>Bulk import members</h4>
<div class="card card-body shadow-sm" style="max-width:640px">
  <p class="small text-muted">Upload a <code>.xlsx</code> file with the following columns in the first row:</p>
  <pre class="bg-light p-2 small">name | ic_no | email | phone | address | plan_code | joined_date</pre>
  <p class="small text-muted">Plan codes available: see Plans page. Dates accept any format <code>strtotime()</code> understands.</p>
  <form method="post" enctype="multipart/form-data" action="<?= site_url('members/import') ?>">
    <?= csrf_field() ?>
    <input type="file" name="file" accept=".xlsx,.xls" class="form-control mb-3" required>
    <button class="btn btn-primary"><i class="bi bi-upload me-1"></i>Import</button>
    <a class="btn btn-link" href="<?= site_url('members') ?>">Cancel</a>
  </form>
</div>
<?= $this->endSection() ?>
