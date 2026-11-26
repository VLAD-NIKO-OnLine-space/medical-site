<?php
$c = content('author');
$bio = rich_text($c['bio']);
preg_match('/\A<p>.*?<\/p>/s', $bio, $intro);
$experience = $intro[0] ?? '';
// нет файла фото — вместо него инициалы
$photo = is_file(__DIR__ . "/../public{$c['photo']['src']}-{$c['photo']['w'][0]}.webp");
?>
<section class="author" id="o-vrache" aria-labelledby="author-title">
<div class="container">
<div class="author__panel">
<?php /* рамка в пропорциях самого фото — фигура занимает всю ширину колонки */ ?>
<div class="author__photo<?= $photo ? '' : ' author__photo--empty' ?>"<?= $photo ? ' style="aspect-ratio: ' . (int) $c['photo']['width'] . ' / ' . (int) $c['photo']['height'] . '"' : '' ?>>
<?php if ($photo): ?>
<?= picture($c['photo'], '(max-width: 640px) 45vw, (max-width: 1024px) 90vw, 40vw', $c['photo_alt'], 'loading="lazy" decoding="async"') ?>
<?php else: ?>
<span class="author__monogram" aria-hidden="true"><?= e(initials($c['name'])) ?></span>
<?php endif; ?>
</div>
<div class="author__info">
<div class="author__heading">
<h2 class="section-title" id="author-title"><?= e(typo($c['title'])) ?><?php if ($c['title_accent'] !== ''): ?> <span class="text-accent"><?= e(typo($c['title_accent'])) ?></span><?php endif; ?></h2>
</div>
<div class="author__profile">
<div class="author__person">
<?= icon('badge-check', '', 28) ?>
<div>
<p class="author__name"><?= e($c['name']) ?></p>
<?php if ($experience !== ''): ?>
<div class="author__experience"><?= $experience ?></div>
<?php endif; ?>
<?php if ($c['status'] !== ''): ?>
<p class="author__status"><?= e($c['status']) ?></p>
<?php endif; ?>
</div>
</div>
<?php if ($c['availability'] !== ''): ?>
<p class="author__availability"><?= e($c['availability']) ?></p>
<?php endif; ?>
</div>
<?php if ($bio !== ''): ?>
<div class="author__bio<?= trim($bio) === $experience ? ' author__bio--intro-only' : '' ?>">
<?= $bio ?>
</div>
<?php endif; ?>
</div>
</div>
</div>
</section>
