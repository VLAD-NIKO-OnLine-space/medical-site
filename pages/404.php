<?php
$c = content('notfound');
$page['title'] = 'Страница не найдена — ' . content('site')['name'];
$page['description'] = $c['text'];
$page['robots'] = 'noindex, follow';
?>
<section class="notfound" aria-labelledby="notfound-title">
<div class="container">
<div class="notfound__card">
<p class="notfound__code" aria-hidden="true">404</p>
<h1 class="notfound__title" id="notfound-title"><?= e(typo($c['title'])) ?></h1>
<?php if ($c['text'] !== ''): ?>
<p class="notfound__text"><?= nl2br(e(typo($c['text'])), false) ?></p>
<?php endif; ?>
<div class="notfound__actions">
<a class="btn" href="/"><?= icon('arrow-left', 'chevron-left') ?>На главную</a>
<a class="btn btn--ghost" href="/#programma"><?= icon('book-open', 'book-open-check') ?>Программа</a>
</div>
</div>
</div>
</section>
