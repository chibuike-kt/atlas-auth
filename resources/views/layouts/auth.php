<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title ?? 'Atlas Auth', ENT_QUOTES, 'UTF-8') ?></title>
  <style>
    body {
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial;
      background: #0b1220;
      color: #e6edf6;
      margin: 0
    }

    .wrap {
      max-width: 420px;
      margin: 72px auto;
      padding: 24px
    }

    .card {
      background: #0f1b33;
      border: 1px solid #1d2a4a;
      border-radius: 16px;
      padding: 20px
    }

    label {
      display: block;
      margin: 12px 0 6px
    }

    input {
      width: 100%;
      padding: 12px;
      border-radius: 12px;
      border: 1px solid #25385f;
      background: #0b1428;
      color: #e6edf6
    }

    button {
      width: 100%;
      margin-top: 14px;
      padding: 12px;
      border-radius: 12px;
      border: 0;
      background: #4f8cff;
      color: #081022;
      font-weight: 700;
      cursor: pointer
    }

    a {
      color: #8ab4ff
    }

    .muted {
      color: #a9b7d0;
      font-size: 14px
    }
  </style>
</head>

<body>
  <div class="wrap">
    <div class="card">
      <?= $content ?? '' ?>
    </div>
  </div>
</body>

</html>