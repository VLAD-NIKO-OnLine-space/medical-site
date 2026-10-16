<?php
// Вход клиента по логину или почте и паролю; вход запоминается на 30 дней (client.php).
$c = content('login');
$support = content('footer')['email'];
$page['title'] = $c['meta_title'];
$page['description'] = $c['lead'];
$page['robots'] = 'noindex, follow';
$page['scripts'][] = '/assets/js/login.js';
$error = null;
$login = '';
if (PHP_SAPI !== 'cli') { // при сборке в статику запроса нет
    if (current_client()) {
        header('Location: /kabinet/', true, 303);
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $login = clean_text($_POST['login'] ?? '', 200, false) ?? '';
        $error = same_origin() ? client_login($login, (string) ($_POST['password'] ?? '')) : 'Обновите страницу и попробуйте ещё раз.';
        if ($error === null) {
            header('Location: /kabinet/', true, 303);
            exit;
        }
    }
}
?>
<section class="login" aria-labelledby="login-title">
<div class="container login__grid">
<div class="login__card">
<?php if ($c['eyebrow'] !== ''): ?>
<div class="eyebrow"><?= e($c['eyebrow']) ?></div>
<?php endif; ?>
<h1 class="login__title" id="login-title"><?= e(typo($c['title'])) ?><?php if ($c['title_accent'] !== ''): ?> <span class="text-accent"><?= e(typo($c['title_accent'])) ?></span><?php endif; ?></h1>
<?php if ($c['lead'] !== ''): ?>
<p class="login__lead"><?= nl2br(e(typo($c['lead'])), false) ?></p>
<?php endif; ?>
<form class="login__form" method="post" action="/vhod/" data-login-form>
<?php if ($error !== null): ?>
<div class="login__notice login__notice--error" role="alert">
<?= icon('info', '', 20) ?>
<p><?= e($error) ?></p>
</div>
<?php endif; ?>
<label class="login__field">
<span class="login__label">Почта или логин</span>
<input class="login__input" type="text" name="login" autocomplete="username" autocapitalize="none" spellcheck="false" placeholder="you@mail.ru" value="<?= e($login) ?>" required>
</label>
<div class="login__field">
<div class="login__label-row">
<label class="login__label" for="login-password">Пароль</label>
<?php if ($support !== ''): ?>
<a class="login__forgot" href="mailto:<?= e($support) ?>?subject=<?= rawurlencode('Восстановление пароля') ?>">Забыли пароль?</a>
<?php endif; ?>
</div>
<div class="login__password">
<input class="login__input" id="login-password" type="password" name="password" autocomplete="current-password" required>
<button class="login__eye" type="button" data-password-toggle aria-label="Показать пароль" aria-pressed="false"><?= icon('eye', '', 20) ?><?= icon('eye-off', '', 20) ?></button>
</div>
</div>
<button class="btn login__submit" type="submit"><?= icon('door-closed', 'door-open') ?>Войти</button>
</form>
<p class="login__alt">Ещё нет доступа? <a href="/#dostup">Как получить доступ</a></p>
</div>
<div class="login__side" aria-hidden="true">
<?= picture(content('hero')['image'], '45vw', '', 'loading="lazy" decoding="async"', 'login__bg') ?>
<?php if ($c['side_title'] !== '' || $c['side_points']): ?>
<div class="login__promo">
<?php if ($c['side_title'] !== ''): ?>
<p class="login__promo-title"><?= e(typo($c['side_title'])) ?></p>
<?php endif; ?>
<?php if ($c['side_points']): ?>
<ul class="login__points">
<?php foreach ($c['side_points'] as $p): ?>
<li><?= icon('check', '', 18) ?><?= e(typo($p['text'])) ?></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
</div>
<?php endif; ?>
</div>
</div>
</section>
