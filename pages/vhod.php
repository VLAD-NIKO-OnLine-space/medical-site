<?php
// Вход клиента по почте и паролю. Аккаунтов клиентов пока нет (появятся с подключением оплаты),
// поэтому вместо проверки пароля форма показывает сообщение из админки. Пароль нигде не сохраняется.
$c = content('login');
$support = content('footer')['email'];
$page['title'] = $c['meta_title'];
$page['description'] = $c['lead'];
$page['robots'] = 'noindex, follow';
$page['scripts'][] = '/assets/js/login.js';
$sent = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'; // при сборке в статику запроса нет
$email = $sent ? (clean_text($_POST['email'] ?? '', 200, false) ?? '') : '';
$notice = link_emails(nl2br(e(typo(strtr($c['notice'], seller_tokens()))), false));
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
<div class="login__notice" role="status" data-login-notice<?= $sent ? '' : ' hidden' ?>>
<?= icon('info', '', 20) ?>
<p><?= $notice ?></p>
</div>
<label class="login__field">
<span class="login__label">Почта</span>
<input class="login__input" type="email" name="email" autocomplete="email" inputmode="email" placeholder="you@mail.ru" value="<?= e($email) ?>" required>
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
