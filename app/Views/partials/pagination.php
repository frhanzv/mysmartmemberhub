<?php /** @var \CodeIgniter\Pager\Pager|null $pager */ ?>
<?php if (! empty($pager)): ?>
  <?= $pager->links('default', 'default_full') ?>
<?php endif; ?>
