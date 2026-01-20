<?php
$title = 'Verify Email';
ob_start();
?>
<h2>Verify your email</h2>
<p class="muted">
  We sent you a verification link. In dev mode, the link is written to <code>storage/logs/app.log</code>.
</p>

<?php if (!empty($_GET['sent'])): ?>
  <p style="color:#8affb0;">Verification link re-sent. Check the log.</p>
<?php endif; ?>

<form method="post" action="/resend-verification">
  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '', ENT_QUOTES, 'UTF-8') ?>">
  <button type="submit">Resend verification link</button>
</form>

<p class="muted" style="margin-top:12px;">
  <a href="/dashboard">Try dashboard</a>
</p>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/auth.php';
