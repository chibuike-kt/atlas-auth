<?php
$title = 'Reset Password';
$error = $error ?? null;
$token = (string)($token ?? '');
ob_start();
?>
<h2>Reset password</h2>

<?php if ($error): ?>
  <p style="color:#ff8a8a;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php else: ?>
  <p class="muted">Set a new password (min 10 chars).</p>
<?php endif; ?>

<form method="post" action="/reset-password">
  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '', ENT_QUOTES, 'UTF-8') ?>">
  <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
  <label>New password</label>
  <input name="password" type="password" autocomplete="new-password" required>
  <button type="submit">Update password</button>
</form>

<p class="muted" style="margin-top:12px;">
  <a href="/">Back to login</a>
</p>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/auth.php';
