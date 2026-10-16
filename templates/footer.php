<?php $f = content('footer'); ?>
<footer class="footer">
<div class="container">
<div class="footer__panel">
<?php
// колонки подвала берут ссылки из шапки и карточки доступа — в одном месте меняется везде
$nav = nav_items();
$docs = content('access')['docs'];
$login = content('header');
?>
<div class="footer__top">
<div class="footer__brand">
<a href="/" class="footer__logo"><?= logo_mark('footer__logo-icon', 36) ?><?= logo_text() ?></a>
<?php if ($f['tagline'] !== ''): ?>
<p class="footer__tagline"><?= nl2br(e(typo($f['tagline'])), false) ?></p>
<?php endif; ?>
</div>
<?php if ($nav): ?>
<nav class="footer__col" aria-labelledby="footer-nav-title">
<p class="footer__col-title" id="footer-nav-title">Разделы</p>
<ul class="footer__links">
<?php foreach ($nav as $link): ?>
<li><a href="<?= e($link['href']) ?>"><?= e($link['label']) ?></a></li>
<?php endforeach; ?>
</ul>
</nav>
<?php endif; ?>
<?php if ($docs): ?>
<nav class="footer__col" aria-labelledby="footer-docs-title">
<p class="footer__col-title" id="footer-docs-title">Документы</p>
<ul class="footer__links">
<?php foreach ($docs as $doc): ?>
<li><a href="<?= e($doc['href']) ?>"><?= e($doc['label']) ?></a></li>
<?php endforeach; ?>
</ul>
</nav>
<?php endif; ?>
<div class="footer__col">
<p class="footer__col-title">Связь</p>
<ul class="footer__links footer__links--contacts">
<?php if ($f['email'] !== ''): ?>
<li><a class="footer__contact" href="mailto:<?= e($f['email']) ?>"><?= icon('mail', '', 18) ?><?= e($f['email']) ?></a></li>
<?php endif; ?>
<?php if ($f['phone'] !== ''): ?>
<li><a class="footer__contact" href="<?= e(tel_href($f['phone'])) ?>"><?= icon('phone', '', 18) ?><?= e($f['phone']) ?></a></li>
<?php endif; ?>
<li><a class="footer__contact" href="<?= current_client() ? '/kabinet/' : e($login['button_href']) ?>"><?= icon(current_client() ? 'circle-user' : 'door-closed', '', 18) ?><?= current_client() ? 'Личный кабинет' : 'Вход в кабинет' ?></a></li>
</ul>
</div>
</div>
<div class="footer__bottom">
<?php if ($f['disclaimer'] !== ''): ?>
<p class="footer__disclaimer"><?= icon('info', '', 18) ?><small><?= nl2br(e(typo($f['disclaimer'])), false) ?></small></p>
<?php endif; ?>
<p class="footer__copy"><small>&copy; <?= date('Y') ?> <?= e($f['copyright']) ?></small></p>
<?php
// реквизиты продавца — платёжные сервисы требуют их в подвале
$legal = array_filter([
    $f['seller'],
    $f['inn'] !== '' ? 'ИНН ' . $f['inn'] : '',
    $f['ogrnip'] !== '' ? 'ОГРНИП ' . $f['ogrnip'] : '',
    $f['city'] !== '' ? 'г. ' . $f['city'] : '',
], 'strlen');
?>
<?php if ($legal): ?>
<p class="footer__legal"><small><?= e(implode(' · ', $legal)) ?></small></p>
<?php endif; ?>
</div>
</div>
</div>
</footer>
