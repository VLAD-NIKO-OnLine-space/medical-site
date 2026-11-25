<?php
$c = content('hero');
$accentParts = preg_split('/[ \t\r\n]+/', typo($c['title_accent']), -1, PREG_SPLIT_NO_EMPTY);
$chapterCount = count(content('program')['chapters']);
$programUrl = e($c['button_href']);
$programButtonContent = icon('book-open', 'book-open-check') . e($c['button_text']);
?>
<section class="hero">
<?= picture($c['image'], '100vw', $c['image_alt'], 'fetchpriority="high"', 'hero__bg') ?>
<?php $video = video_embed($c['video_url']); ?>
<div class="container hero__inner">
<div class="hero__content">
<?php if ($c['eyebrow'] !== ''): ?>
<div class="eyebrow"><?= e($c['eyebrow']) ?></div>
<?php endif; ?>
<h1 class="hero__title"><?= e(typo($c['title'])) ?><?php if ($accentParts): ?>
 <span class="text-accent hero__title-accent"><?= implode(' ', array_map(
    fn($part) => '<span class="hero__title-accent-part">' . e($part) . '</span>',
    $accentParts
)) ?></span><?php endif; ?></h1>
<?php if ($c['text'] !== ''): ?>
<p class="hero__text"><?= nl2br(e(typo($c['text'])), false) ?></p>
<?php endif; ?>
<a href="<?= $programUrl ?>" class="btn hero__btn hero__btn--desktop"><?= $programButtonContent ?></a>
</div>
<div class="video-card">
<button class="video-card__media" type="button" aria-label="Смотреть видео: <?= e(implode(' — ', array_filter([$c['video_badge'], $c['video_label'], $c['video_author']], 'strlen'))) ?>"<?= $video ? ' data-video-open data-video-type="' . $video[0] . '" data-video-src="' . e($video[1]) . '"' : ' disabled' ?>>
<?= picture($c['video_cover'], '(max-width: 1024px) 100vw, 55vw', '', 'decoding="async"') ?>
<span class="video-card__kicker" aria-hidden="true">Презентация автора</span>
<?php if ($c['video_badge'] !== ''): ?>
<span class="video-card__caption"><?= e($c['video_badge']) ?></span>
<?php endif; ?>
<span class="video-card__play" aria-hidden="true">
<svg viewBox="3 1 20 22" focusable="false">
<defs>
<linearGradient id="hero-play-glass" x2="76%" y2="100%">
<stop stop-color="#e7f3f1" stop-opacity=".62"/>
<stop offset=".52" stop-color="#9fb7b5" stop-opacity=".44"/>
<stop offset="1" stop-color="#395955" stop-opacity=".76"/>
</linearGradient>
</defs>
<path d="<?= icons()['play'] ?>" fill="url(#hero-play-glass)" stroke="#e3f6f2" stroke-opacity=".56" stroke-width=".24"/>
</svg>
</span>
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
<div class="hero__actions-mobile">
<a href="<?= $programUrl ?>" class="btn hero__btn hero__btn--mobile"><?= $programButtonContent ?></a>
<ul class="hero-cards" aria-label="О видеогиде">
<li class="hero-card hero-card--chapters">
<strong class="hero-card__heading hero-card__heading--chapters"><span class="hero-card__count"><?= $chapterCount ?></span> <?= e(plural($chapterCount, ['глава', 'главы', 'глав'])) ?></strong>
<p><?= e(typo('От первых шагов до жизни после лечения')) ?></p>
</li>
<li class="hero-card hero-card--permanent">
<div class="hero-card__heading">
<strong class="hero-card__title">Доступ навсегда</strong>
<span class="hero-card__icon"><?= icon('infinity', '', 20) ?></span>
</div>
<p><?= e(typo('Пересматривайте сколько угодно — на телефоне, планшете и компьютере')) ?></p>
</li>
<?php if (!client_has_access(current_client())): ?>
<li class="hero-card hero-card--access">
<strong class="hero-card__access-title">Полная версия видеогида</strong>
<p><?= e(typo('Все главы открываются в личном кабинете.')) ?></p>
<a class="btn hero-card__access-link" href="#dostup">Как получить доступ<?= icon('arrow-right', '', 18) ?></a>
</li>
<?php endif; ?>
</ul>
</div>
</div>
</section>
