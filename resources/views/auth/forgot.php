<?php
$title = 'Forgot Password';
$sent = (bool)($sent ?? false);
ob_start();
?>
<h2>Forgot password</h2>

<?php if ($sent): ?>
  <p style="color:#8affb0;">If the email exists, a reset link has been sent. (Dev: check <code>storage/logs/app.log</code>)</p>
<?php else: ?>
  <p class="muted">Enter your email and we’ll send a reset link.</p>
<?php endif; ?>

<form method="post" action="/forgot-password">
  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '', ENT_QUOTES, 'UTF-8') ?>">
  <label>Email</label>
  <input name="email" type="email" autocomplete="email" required>
  <button type="submit">Send reset link</button>
</form>

<p class="muted" style="margin-top:12px;">
  <a href="/">Back to login</a>
</p>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/auth.php';
