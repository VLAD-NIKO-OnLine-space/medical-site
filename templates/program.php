<?php
$c = content('program');
if ($c['chapters']) {
    $page['styles'][] = '/assets/js/vendor/swiper/swiper.min.css';
    $page['scripts'][] = '/assets/js/program.js';
}
?>
<section class="program" id="programma" aria-labelledby="program-title">
<?= picture($c['image'], '100vw', '', 'loading="lazy" decoding="async"', 'program__bg') ?>
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
<?php if ($c['chapters']): ?>
<div class="container">
<div class="chapters swiper" data-chapters>
<ol class="swiper-wrapper">
<?php foreach ($c['chapters'] as $i => $ch): ?>
<?php $video = video_embed($ch['video_url']); ?>
<li class="chapter swiper-slide">
<span class="chapter__label">Глава <span class="chapter__num"><?= sprintf('%02d', $i + 1) ?></span></span>
<h3 class="chapter__title"><?= e(typo($ch['title'])) ?></h3>
<?php if ($ch['text'] !== ''): ?>
<p class="chapter__text"><?= e(typo($ch['text'])) ?></p>
<?php endif; ?>
<div class="chapter__meta">
<?php if ($video): ?>
<button class="chapter__play" type="button" data-video-open data-video-type="<?= $video[0] ?>" data-video-src="<?= e($video[1]) ?>" aria-label="Смотреть фрагмент: <?= e($ch['title']) ?>"><?= icon('play', '', 16) ?>Смотреть фрагмент</button>
<?php else: ?>
<span class="chapter__soon"><?= icon('lock', '', 16) ?>Скоро</span>
<?php endif; ?>
<?php if ($ch['duration'] !== ''): ?>
<span class="chapter__duration"><?= icon('clock', '', 16) ?><?= e($ch['duration']) ?></span>
<?php endif; ?>
</div>
</li>
<?php endforeach; ?>
</ol>
</div>
<div class="chapters__controls">
<div class="chapters__scrollbar" data-chapters-scrollbar></div>
<div class="chapters__nav">
<button type="button" class="slider-arrow" data-chapters-prev aria-label="Предыдущие главы"><?= icon('arrow-left', 'chevron-left', 22) ?></button>
<button type="button" class="slider-arrow" data-chapters-next aria-label="Следующие главы"><?= icon('arrow-right', 'chevron-right', 22) ?></button>
</div>
</div>
</div>
<?php endif; ?>
</section>
