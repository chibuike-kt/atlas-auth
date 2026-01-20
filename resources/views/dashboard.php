<?php
$title = 'Dashboard';
ob_start();
?>
<h1>Dashboard</h1>
<form method="post" action="/logout">
  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '', ENT_QUOTES, 'UTF-8') ?>">
  <button type="submit">Logout</button>
</form>

<form method="post" action="/logout-everywhere" style="margin-top:12px;">
  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '', ENT_QUOTES, 'UTF-8') ?>">
  <button type="submit">Logout everywhere</button>
</form>

<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/app.php';
