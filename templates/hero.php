<?php $c = content('hero'); ?>
<section class="hero">
<picture class="hero__bg">
<source media="(max-width: 640px)" type="image/avif" srcset="/assets/img/hero-mobile-480.avif 480w, /assets/img/hero-mobile-700.avif 700w" sizes="100vw">
<source media="(max-width: 640px)" type="image/webp" srcset="/assets/img/hero-mobile-480.webp 480w, /assets/img/hero-mobile-700.webp 700w" sizes="100vw">
<source media="(max-width: 640px)" srcset="/assets/img/hero-mobile-480.jpg 480w, /assets/img/hero-mobile-700.jpg 700w" sizes="100vw">
<source type="image/avif" srcset="/assets/img/hero-960.avif 960w, /assets/img/hero-1280.avif 1280w, /assets/img/hero-1672.avif 1672w" sizes="100vw">
<source type="image/webp" srcset="/assets/img/hero-960.webp 960w, /assets/img/hero-1280.webp 1280w, /assets/img/hero-1672.webp 1672w" sizes="100vw">
<img src="/assets/img/hero-1280.jpg" srcset="/assets/img/hero-960.jpg 960w, /assets/img/hero-1280.jpg 1280w, /assets/img/hero-1672.jpg 1672w" sizes="100vw" width="1672" height="941" alt="<?= e($c['image_alt']) ?>" fetchpriority="high">
</picture>
<?php $video = video_embed($c['video_url']); ?>
<div class="container hero__inner">
<div class="hero__content">
<?php if ($c['eyebrow'] !== ''): ?>
<div class="eyebrow"><?= e($c['eyebrow']) ?></div>
<?php endif; ?>
<h1 class="hero__title"><?= e(typo($c['title'])) ?><?php if ($c['title_accent'] !== ''): ?><br> <span class="text-accent"><?= e(typo($c['title_accent'])) ?></span><?php endif; ?></h1>
<?php if ($c['text'] !== ''): ?>
<p class="hero__text"><?= nl2br(e(typo($c['text'])), false) ?></p>
<?php endif; ?>
<a href="<?= e($c['button_href']) ?>" class="btn hero__btn"><?= icon($c['button_icon'], $c['button_icon_hover']) ?><?= e($c['button_text']) ?></a>
</div>
<div class="video-card">
<button class="video-card__media" type="button" aria-label="Смотреть видео: <?= e(implode(' — ', array_filter([$c['video_label'], $c['video_author']], 'strlen'))) ?>"<?= $video ? ' data-video-open data-video-type="' . $video[0] . '" data-video-src="' . e($video[1]) . '"' : ' disabled' ?>>
<picture>
<source type="image/avif" srcset="/assets/img/video-cover-960.avif 960w, /assets/img/video-cover-1280.avif 1280w, /assets/img/video-cover-1672.avif 1672w" sizes="(max-width: 1024px) 100vw, 55vw">
<source type="image/webp" srcset="/assets/img/video-cover-960.webp 960w, /assets/img/video-cover-1280.webp 1280w, /assets/img/video-cover-1672.webp 1672w" sizes="(max-width: 1024px) 100vw, 55vw">
<img src="/assets/img/video-cover-960.jpg" srcset="/assets/img/video-cover-960.jpg 960w, /assets/img/video-cover-1280.jpg 1280w, /assets/img/video-cover-1672.jpg 1672w" sizes="(max-width: 1024px) 100vw, 55vw" width="960" height="540" alt="" decoding="async">
</picture>
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
<?php if ($video): ?>
<dialog class="video-modal" data-video-modal aria-label="<?= e($c['video_label'] !== '' ? $c['video_label'] : 'Видео') ?>">
<button class="video-modal__close" type="button" data-video-close aria-label="Закрыть видео"><?= icon('x', '', 22) ?></button>
<div class="video-modal__frame" data-video-frame></div>
</dialog>
<?php endif; ?>
</section>
