<?php $f = content('footer'); ?>
<footer class="footer">
<div class="container">
<div class="footer__panel">
<div class="footer__top">
<div class="footer__brand">
<a href="/" class="footer__logo"><?= logo_mark('footer__logo-icon', 36) ?><?= logo_text() ?></a>
<?php if ($f['tagline'] !== ''): ?>
<p class="footer__tagline"><?= nl2br(e(typo($f['tagline'])), false) ?></p>
<?php endif; ?>
<?php if ($f['email'] !== ''): ?>
<a class="footer__contact" href="mailto:<?= e($f['email']) ?>"><?= icon('mail', '', 18) ?><?= e($f['email']) ?></a>
<?php endif; ?>
<?php if ($f['phone'] !== ''): ?>
<a class="footer__contact" href="<?= e(tel_href($f['phone'])) ?>"><?= icon('phone', '', 18) ?><?= e($f['phone']) ?></a>
<?php endif; ?>
</div>
<?php if ($f['links']): ?>
<nav class="footer__nav" aria-label="Информация">
<ul class="footer__links">
<?php foreach ($f['links'] as $link): ?>
<li><a href="<?= e($link['href']) ?>"><?= e($link['label']) ?></a></li>
<?php endforeach; ?>
</ul>
</nav>
<?php endif; ?>
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
