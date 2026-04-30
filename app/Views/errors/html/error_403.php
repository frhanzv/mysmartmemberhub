<?php
$title = $title ?? '403 Forbidden';
?><!doctype html>
<html><head>
<meta charset="utf-8">
<title><?= esc($title) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="bg-light">
<div class="container py-5 text-center">
  <h1 class="display-1 text-danger">403</h1>
  <h2><?= esc($title) ?></h2>
  <p class="text-muted"><?= esc($message ?? 'You do not have permission to access this page.') ?></p>
  <a class="btn btn-primary mt-3" href="/">Back to dashboard</a>
</div>
</body></html>
