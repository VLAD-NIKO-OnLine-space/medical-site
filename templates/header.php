<header class="header">
<div class="container">
<div class="header__bar">
<a href="/" class="header__logo">
<svg class="header__logo-icon" width="32" height="32" viewBox="0 0 32 32" aria-hidden="true"><path d="M12 4h8v8h8v8h-8v8h-8v-8H4v-8h8z" fill="currentColor"/><path d="M6 16h5l2-4 4 8 2-4h7" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
<span><?= e($site['site_name']) ?></span>
</a>
<nav class="header__nav" aria-label="Основное меню">
<ul class="header__menu">
<?php foreach ($site['nav'] as $href => $label): ?>
<li><a href="<?= e($href) ?>"<?= $href === $url ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
<?php endforeach; ?>
</ul>
</nav>
<a href="/vhod/" class="btn header__cta"><?= icon('door-closed', 'door-open') ?>Войти</a>
</div>
</div>
</header>
