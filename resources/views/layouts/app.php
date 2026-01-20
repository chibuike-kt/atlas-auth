<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title ?? 'Atlas', ENT_QUOTES, 'UTF-8') ?></title>
</head>

<body>
  <?= $content ?? '' ?>
</body>

</html>