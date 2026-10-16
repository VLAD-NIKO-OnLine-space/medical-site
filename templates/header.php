<?php
$h = content('header');
// вошедшему клиенту вместо «Войти» — его почта (или логин) с выпадающим меню
$me = current_client();
$account = $me ? ($me['email'] !== '' ? $me['email'] : $me['login']) : '';
?>
<header class="header">
<div class="container">
<div class="header__bar">
<a href="/" class="header__logo">
<?= logo_mark('header__logo-icon') ?>
<?= logo_text() ?>
</a>
<nav class="header__nav" id="site-menu" aria-label="Основное меню">
<p class="header__menu-cap" style="--i: 0" aria-hidden="true">Меню</p>
<ul class="header__menu">
<?php $nav = nav_items(); ?>
<?php foreach ($nav as $i => $item): ?>
<li style="--i: <?= $i + 1 ?>"><a href="<?= e($item['href']) ?>"<?= $item['href'] === $url ? ' aria-current="page"' : '' ?>><?= e($item['label']) ?></a></li>
<?php endforeach; ?>
</ul>
<?php if ($me): ?>
<?php /* телефон: в бургер-меню — почта и две кнопки вместо выпадающего меню */ ?>
<div class="header__menu-account" style="--i: <?= count($nav) + 1 ?>">
<p class="header__menu-email"><?= icon('circle-user', '', 18) ?><span><?= e($account) ?></span></p>
<a href="/kabinet/" class="btn"><?= icon('circle-user') ?>Личный кабинет</a>
<form method="post" action="/vyhod/"><button class="btn btn--ghost" type="submit"><?= icon('log-out') ?>Выйти</button></form>
</div>
<?php else: ?>
<a href="<?= e($h['button_href']) ?>" class="btn header__menu-cta" style="--i: <?= count($nav) + 1 ?>"><?= icon('door-closed', 'door-open') ?><?= e($h['button_text']) ?></a>
<?php endif; ?>
</nav>
<?php if ($me): ?>
<div class="account header__cta" data-account>
<button class="account__btn" type="button" aria-expanded="false" aria-controls="account-menu" title="<?= e($account) ?>" data-account-toggle>
<span class="account__avatar" aria-hidden="true"><?= e(initials($me['name']) ?: substr($account, 0, 1)) ?></span>
<span class="account__name"><?= e($account) ?></span>
<?= icon('chevron-down', '', 18) ?>
</button>
<div class="account__menu" id="account-menu" data-account-menu>
<p class="account__info"><span class="account__full"><?= e($account) ?></span><?php if (!client_has_access($me)): ?><span class="account__until account__until--off">доступ закрыт</span><?php endif; ?></p>
<a class="account__item" href="/kabinet/"><?= icon('circle-user', '', 18) ?>Личный кабинет</a>
<form method="post" action="/vyhod/"><button class="account__item account__item--exit" type="submit"><?= icon('log-out', '', 18) ?>Выйти из аккаунта</button></form>
</div>
</div>
<?php else: ?>
<a href="<?= e($h['button_href']) ?>" class="btn header__cta"><?= icon('door-closed', 'door-open') ?><?= e($h['button_text']) ?></a>
<?php endif; ?>
<button class="header__burger" type="button" aria-expanded="false" aria-controls="site-menu" aria-label="Открыть меню" data-burger data-close-icon="<?= e(icons()['x']) ?>"><morph-icon reduced-motion="user"><?= icon('menu', '', 22) ?></morph-icon></button>
</div>
</div>
</header>
