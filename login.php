<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';

// Уже залогинен — сразу в реестр.
if (auth_is_logged_in()) {
    header('Location: index.php');
    exit;
}

$needsSetup = !auth_has_any_user();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Вход · ЦМК Реестр вебинаров</title>
<link rel="stylesheet" href="assets/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
<style>
  body { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(155deg, var(--teal), var(--teal-dark)); }
  .login-card {
    width: 100%; max-width: 380px; background: var(--card); border-radius: 16px;
    padding: 32px 30px; box-shadow: 0 24px 60px -20px rgba(0,0,0,0.35);
  }
  .login-brand { display: flex; align-items: center; gap: 12px; margin-bottom: 22px; }
  .login-brand .brand-mark { width: 42px; height: 42px; border-radius: 10px; background: linear-gradient(155deg, var(--teal), var(--teal-dark)); color: #fff; display: flex; align-items: center; justify-content: center; font-family: 'Manrope', sans-serif; font-weight: 800; font-size: 14px; }
  .login-brand h1 { font-family: 'Manrope', sans-serif; font-size: 18px; margin: 0; }
  .login-brand p { margin: 2px 0 0; font-size: 12.5px; color: var(--ink-soft); }
  .login-field { margin-bottom: 14px; }
  .login-field label { display: block; font-size: 12.5px; font-weight: 600; color: var(--ink-soft); margin-bottom: 5px; }
  .login-field input { width: 100%; border: 1px solid var(--line); border-radius: 8px; padding: 10px 12px; font-size: 14px; font-family: 'Inter', sans-serif; }
  .login-field input:focus { outline: 2px solid var(--teal); outline-offset: 1px; }
  .login-error { background: #FBE7E4; color: #8A2E22; border-radius: 8px; padding: 9px 12px; font-size: 12.5px; margin-bottom: 14px; display: none; }
  .login-hint { font-size: 12px; color: var(--ink-soft); margin-top: 14px; text-align: center; }
  .login-submit { width: 100%; justify-content: center; padding: 11px; font-size: 14px; margin-top: 4px; }
</style>
</head>
<body>
  <div class="login-card">
    <div class="login-brand">
      <div class="brand-mark">ЦМК</div>
      <div>
        <h1>Реестр вебинаров</h1>
        <p><?= $needsSetup ? 'Создайте первый аккаунт' : 'Вход' ?></p>
      </div>
    </div>

    <div class="login-error" id="loginError"></div>

    <form id="loginForm">
      <div class="login-field">
        <label for="loginUsername">Логин</label>
        <input type="text" id="loginUsername" autocomplete="username" required autofocus>
      </div>
      <div class="login-field">
        <label for="loginPassword">Пароль</label>
        <input type="password" id="loginPassword" autocomplete="<?= $needsSetup ? 'new-password' : 'current-password' ?>" required minlength="6">
      </div>
      <?php if ($needsSetup): ?>
      <div class="login-field">
        <label for="loginPasswordConfirm">Повторите пароль</label>
        <input type="password" id="loginPasswordConfirm" autocomplete="new-password" required minlength="6">
      </div>
      <?php endif; ?>
      <button type="submit" class="btn btn-primary login-submit">
        <i class="fa-solid <?= $needsSetup ? 'fa-user-plus' : 'fa-right-to-bracket' ?>"></i>
        <?= $needsSetup ? 'Создать аккаунт и войти' : 'Войти' ?>
      </button>
    </form>

    <p class="login-hint">Вход запоминается в этом браузере, пока вы явно не выйдете или не очистите куки.</p>
  </div>

<script>
(() => {
  const needsSetup = <?= $needsSetup ? 'true' : 'false' ?>;
  const form = document.getElementById('loginForm');
  const errorEl = document.getElementById('loginError');

  function showError(msg) {
    errorEl.textContent = msg;
    errorEl.style.display = 'block';
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    errorEl.style.display = 'none';
    const username = document.getElementById('loginUsername').value.trim();
    const password = document.getElementById('loginPassword').value;

    if (needsSetup) {
      const confirm = document.getElementById('loginPasswordConfirm').value;
      if (password !== confirm) { showError('Пароли не совпадают'); return; }
    }

    try {
      const res = await fetch(`auth_api.php?action=${needsSetup ? 'register-first' : 'login'}`, {
        method: 'POST',
        body: JSON.stringify({ username, password }),
      });
      const json = await res.json();
      if (!json.ok) { showError(json.error || 'Не удалось войти'); return; }
      window.location.href = 'index.php';
    } catch (err) {
      showError('Ошибка сети — попробуйте ещё раз');
    }
  });
})();
</script>
</body>
</html>
