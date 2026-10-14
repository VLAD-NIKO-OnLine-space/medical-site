<?php $c = content('hero'); ?>
<section class="hero">
<?= picture($c['image'], '100vw', $c['image_alt'], 'fetchpriority="high"', 'hero__bg') ?>
<?php $video = video_embed($c['video_url']); ?>
<div class="container hero__inner">
<div class="hero__content">
<?php if ($c['eyebrow'] !== ''): ?>
<div class="eyebrow"><?= e($c['eyebrow']) ?></div>
<?php endif; ?>
<h1 class="hero__title"><?= e(typo($c['title'])) ?><?php if ($c['title_accent'] !== ''): ?> <span class="text-accent hero__title-accent"><?= e(typo($c['title_accent'])) ?></span><?php endif; ?></h1>
<?php if ($c['text'] !== ''): ?>
<p class="hero__text"><?= nl2br(e(typo($c['text'])), false) ?></p>
<?php endif; ?>
<a href="<?= e($c['button_href']) ?>" class="btn hero__btn"><?= icon('book-open', 'book-open-check') ?><?= e($c['button_text']) ?></a>
</div>
<div class="video-card">
<button class="video-card__media" type="button" aria-label="Смотреть видео: <?= e(implode(' — ', array_filter([$c['video_label'], $c['video_author']], 'strlen'))) ?>"<?= $video ? ' data-video-open data-video-type="' . $video[0] . '" data-video-src="' . e($video[1]) . '"' : ' disabled' ?>>
<?= picture($c['video_cover'], '(max-width: 1024px) 100vw, 55vw', '', 'decoding="async"') ?>
<span class="video-card__play"><svg width="30" height="30" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="<?= icons()['play'] ?>"/></svg></span>
<?php if ($video && $c['video_duration'] !== ''): ?>
<span class="video-card__chip"><?= icon('clock', '', 16) ?><?= e($c['video_duration']) ?></span>
<?php endif; ?>
</button>
<div class="video-card__info">
<div>
<?php if ($c['video_label'] !== ''): ?>
<div class="video-card__label"><?= e($c['video_label']) ?></div>
<?php endif; ?>
<div class="video-card__author"><?= e($c['video_author']) ?></div>
</div>
<?php if ($c['video_badge'] !== ''): ?>
<span class="video-card__badge"><?= e($c['video_badge']) ?></span>
<?php endif; ?>
</div>
</div>
</div>
</section>
