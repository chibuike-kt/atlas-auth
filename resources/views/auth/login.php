<?php
$title = 'Login';
$errors = $errors ?? [];
$old = $old ?? [];
ob_start();
?>
<h2>Login</h2>

<?php if (!empty($errors['login'])): ?>
  <p style="color:#ff8a8a;"><?= htmlspecialchars($errors['login'], ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<form method="post" action="/login">
  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '', ENT_QUOTES, 'UTF-8') ?>">
  <label>Email</label>
  <input name="email" type="email" autocomplete="email" required value="<?= htmlspecialchars((string)($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
  <label>Password</label>
  <input name="password" type="password" autocomplete="current-password" required>
  <button type="submit">Continue</button>
</form>

<p class="muted" style="margin-top:12px;">
  No account? <a href="/register">Register</a>
</p>
<p class="muted" style="margin-top:8px;">
  <a href="/forgot-password">Forgot password?</a>
</p>

<label>
  <input type="checkbox" name="remember"> Remember me
</label>

<?php if (!empty($_GET['idle'])): ?>
  <p style="color:#ffce7a;">You were logged out due to inactivity.</p>
<?php elseif (!empty($_GET['expired'])): ?>
  <p style="color:#ffce7a;">Your session expired. Please log in again.</p>
<?php endif; ?>


<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/auth.php';
