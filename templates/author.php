<?php
$c = content('author');
// нет файла фото — вместо него инициалы
$photo = is_file(__DIR__ . "/../public{$c['photo']['src']}-{$c['photo']['w'][0]}.webp");
?>
<section class="author" id="o-vrache" aria-labelledby="author-title">
<div class="container">
<div class="author__panel">
<div class="author__photo<?= $photo ? '' : ' author__photo--empty' ?>">
<?php if ($photo): ?>
<?= picture($c['photo'], '(max-width: 1024px) 90vw, 40vw', $c['photo_alt'], 'loading="lazy" decoding="async"') ?>
<?php else: ?>
<span class="author__monogram" aria-hidden="true"><?= e(initials($c['name'])) ?></span>
<?php endif; ?>
</div>
<div class="author__info">
<?php if ($c['eyebrow'] !== ''): ?>
<div class="eyebrow"><?= e($c['eyebrow']) ?></div>
<?php endif; ?>
<h2 class="section-title" id="author-title"><?= e(typo($c['title'])) ?><?php if ($c['title_accent'] !== ''): ?> <span class="text-accent"><?= e(typo($c['title_accent'])) ?></span><?php endif; ?></h2>
<div class="author__person">
<?= icon('badge-check', '', 28) ?>
<div>
<p class="author__name"><?= e($c['name']) ?></p>
<?php if ($c['status'] !== ''): ?>
<p class="author__status"><?= e($c['status']) ?></p>
<?php endif; ?>
</div>
</div>
<?php if ($c['availability'] !== ''): ?>
<p class="author__availability"><?= e($c['availability']) ?></p>
<?php endif; ?>
<?php if ($c['bio'] !== ''): ?>
<div class="author__bio">
<?= paragraphs($c['bio']) ?>
</div>
<?php endif; ?>
</div>
</div>
</div>
</section>
