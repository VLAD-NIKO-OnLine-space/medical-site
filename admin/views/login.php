<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Вход — админка</title>
<link rel="stylesheet" href="/assets/admin/admin.css">
</head>
<body class="admin admin--login">
<form class="login" method="post" action="<?= ADMIN_URL ?>">
<h1 class="login__title">Вход в админку</h1>
<?php if ($loginError): ?>
<p class="alert alert--error" role="alert"><?= e($loginError) ?></p>
<?php endif; ?>
<input type="hidden" name="csrf" value="<?= e($csrf) ?>">
<input type="hidden" name="action" value="login">
<div class="field">
<label class="field__label" for="login">Логин</label>
<input class="field__input" id="login" name="login" autocomplete="username" required autofocus value="<?= e((string) ($_POST['login'] ?? '')) ?>">
</div>
<div class="field">
<label class="field__label" for="password">Пароль</label>
<input class="field__input" id="password" name="password" type="password" autocomplete="current-password" required>
</div>
<button class="admin-btn" type="submit">Войти</button>
</form>
</body>
</html>
