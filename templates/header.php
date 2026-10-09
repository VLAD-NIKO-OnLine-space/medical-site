<?php $h = content('header'); ?>
<header class="header">
<div class="container">
<div class="header__bar">
<a href="/" class="header__logo">
<svg class="header__logo-icon" width="32" height="32" viewBox="0 0 32 32" aria-hidden="true"><path d="M12 4h8v8h8v8h-8v8h-8v-8H4v-8h8z" fill="currentColor"/><path d="M6 16h5l2-4 4 8 2-4h7" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
<span><?= e(content('site')['name']) ?></span>
</a>
<nav class="header__nav" id="site-menu" aria-label="Основное меню">
<p class="header__menu-cap" style="--i: 0" aria-hidden="true">Меню</p>
<ul class="header__menu">
<?php foreach ($h['nav'] as $i => $item): ?>
<li style="--i: <?= $i + 1 ?>"><a href="<?= e($item['href']) ?>"<?= $item['href'] === $url ? ' aria-current="page"' : '' ?>><?= e($item['label']) ?></a></li>
<?php endforeach; ?>
</ul>
<a href="<?= e($h['button_href']) ?>" class="btn header__menu-cta" style="--i: <?= count($h['nav']) + 1 ?>"><?= icon($h['button_icon'], $h['button_icon_hover']) ?><?= e($h['button_text']) ?></a>
</nav>
<a href="<?= e($h['button_href']) ?>" class="btn header__cta"><?= icon($h['button_icon'], $h['button_icon_hover']) ?><?= e($h['button_text']) ?></a>
<button class="header__burger" type="button" aria-expanded="false" aria-controls="site-menu" aria-label="Открыть меню" data-burger data-close-icon="<?= e(icons()['x']) ?>"><morph-icon reduced-motion="user"><?= icon('menu', '', 22) ?></morph-icon></button>
</div>
</div>
</header>
