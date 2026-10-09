<?php $f = content('footer'); ?>
<footer class="footer">
<div class="container">
<div class="footer__panel">
<div class="footer__top">
<div class="footer__brand">
<a href="/" class="footer__logo"><?= logo_mark('footer__logo-icon', 36) ?><span><?= e(content('site')['name']) ?></span></a>
<?php if ($f['tagline'] !== ''): ?>
<p class="footer__tagline"><?= nl2br(e(typo($f['tagline'])), false) ?></p>
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
</div>
</div>
</div>
</footer>
