<?php
$title = 'Register';
$errors = $errors ?? [];
$old = $old ?? [];
ob_start();
?>
<h2>Register</h2>

<?php foreach (['name', 'email', 'password'] as $k): ?>
  <?php if (!empty($errors[$k])): ?>
    <p style="color:#ff8a8a;"><?= htmlspecialchars($errors[$k], ENT_QUOTES, 'UTF-8') ?></p>
  <?php endif; ?>
<?php endforeach; ?>

<form method="post" action="/register">
  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '', ENT_QUOTES, 'UTF-8') ?>">
  <label>Name</label>
  <input name="name" type="text" autocomplete="name" required value="<?= htmlspecialchars((string)($old['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
  <label>Email</label>
  <input name="email" type="email" autocomplete="email" required value="<?= htmlspecialchars((string)($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
  <label>Password</label>
  <input name="password" type="password" autocomplete="new-password" required>
  <button type="submit">Create account</button>
</form>

<p class="muted" style="margin-top:12px;">
  Already have an account? <a href="/">Login</a>
</p>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/auth.php';
