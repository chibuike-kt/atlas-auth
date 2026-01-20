<?php
$title = 'Login';
ob_start();
?>
<h2>Login</h2>
<p class="muted">Secure session + CSRF are already wired. Next we implement real auth.</p>

<form method="post" action="/login">
  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '', ENT_QUOTES, 'UTF-8') ?>">
  <label>Email</label>
  <input name="email" type="email" autocomplete="email" required>
  <label>Password</label>
  <input name="password" type="password" autocomplete="current-password" required>
  <button type="submit">Continue</button>
</form>

<p class="muted" style="margin-top:12px;">
  No account? <a href="/register">Register</a>
</p>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/auth.php';
