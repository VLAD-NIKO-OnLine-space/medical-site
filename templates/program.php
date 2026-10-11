<?php
$c = content('program');
// пока своей картинки нет — тот же фон, что у первого экрана (файлы уже в кэше браузера)
$img = '/assets/img/hero';
?>
<section class="program" id="programma" aria-labelledby="program-title">
<picture class="program__bg">
<source media="(max-width: 640px)" type="image/avif" srcset="<?= $img ?>-mobile-480.avif 480w, <?= $img ?>-mobile-700.avif 700w" sizes="100vw">
<source media="(max-width: 640px)" type="image/webp" srcset="<?= $img ?>-mobile-480.webp 480w, <?= $img ?>-mobile-700.webp 700w" sizes="100vw">
<source media="(max-width: 640px)" srcset="<?= $img ?>-mobile-480.jpg 480w, <?= $img ?>-mobile-700.jpg 700w" sizes="100vw">
<source type="image/avif" srcset="<?= $img ?>-960.avif 960w, <?= $img ?>-1280.avif 1280w, <?= $img ?>-1672.avif 1672w" sizes="100vw">
<source type="image/webp" srcset="<?= $img ?>-960.webp 960w, <?= $img ?>-1280.webp 1280w, <?= $img ?>-1672.webp 1672w" sizes="100vw">
<img src="<?= $img ?>-1280.jpg" srcset="<?= $img ?>-960.jpg 960w, <?= $img ?>-1280.jpg 1280w, <?= $img ?>-1672.jpg 1672w" sizes="100vw" width="1672" height="941" alt="" loading="lazy" decoding="async">
</picture>
<div class="container program__head">
<div class="program__intro">
<?php if ($c['eyebrow'] !== ''): ?>
<div class="eyebrow"><?= e($c['eyebrow']) ?></div>
<?php endif; ?>
<h2 class="section-title" id="program-title"><?= e(typo($c['title'])) ?><?php if ($c['title_accent'] !== ''): ?><br> <span class="text-accent"><?= e(typo($c['title_accent'])) ?></span><?php endif; ?></h2>
</div>
<?php if ($c['lead'] !== ''): ?>
<p class="program__lead"><?= nl2br(e(typo($c['lead'])), false) ?></p>
<?php endif; ?>
</div>
</section>
