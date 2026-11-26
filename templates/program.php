<?php
$c = content('program');
$access = client_has_access(current_client());
if ($c['chapters']) {
    $page['styles'][] = '/assets/js/vendor/swiper/swiper.min.css';
    $page['scripts'][] = '/assets/js/program.js';
}
?>
<section class="program" id="programma" aria-labelledby="program-title">
<?= picture($c['image'], '100vw', '', 'loading="lazy" decoding="async"', 'program__bg') ?>
<div class="container program__head">
<h2 class="section-title" id="program-title"><?= e(typo($c['title'])) ?><?php if ($c['title_accent'] !== ''): ?><br> <span class="text-accent"><?= e(typo($c['title_accent'])) ?></span><?php endif; ?></h2>
</div>
<?php if ($c['chapters']): ?>
<div class="container">
<div class="chapters swiper" data-chapters>
<ol class="swiper-wrapper">
<?php foreach ($c['chapters'] as $i => $ch): ?>
<?php
$isPreview = false;
if ($access) {
    $video = chapter_full_video($ch);
    $playLabel = 'Смотреть';
    $playAction = 'Смотреть главу';
} else {
    $videoUrl = $ch['video_url'] ?? '';
    if ($videoUrl === '' && preg_match('~\A/course-video/(chapter-[0-9]{2})/index\.m3u8\z~', $ch['full_url'] ?? '', $stream)) {
        $previewUrl = '/assets/video/chapter-previews/' . $stream[1] . '/index.m3u8';
        if (is_file(__DIR__ . '/../public' . $previewUrl)) {
            $videoUrl = asset($previewUrl);
        }
    }
    $isPreview = str_starts_with($videoUrl, '/assets/video/chapter-previews/');
    $video = video_embed($videoUrl);
    $playLabel = match (true) {
        $isPreview => 'Фрагмент',
        $ch['title'] === 'Вступление' => 'Смотреть',
        default => 'Смотреть фрагмент',
    };
    $playAction = $isPreview ? 'Смотреть первые 30 секунд' : $playLabel;
}
?>
<li class="chapter swiper-slide">
<div class="chapter__top">
<span class="chapter__label">Глава <span class="chapter__num"><?= sprintf('%02d', $i + 1) ?></span></span>
<?php if ($video): ?>
<button class="chapter__play" type="button" data-video-open data-video-type="<?= $video[0] ?>" data-video-src="<?= e($video[1]) ?>"<?= $isPreview ? ' data-video-preview' : '' ?> aria-label="<?= $playAction ?>: <?= e($ch['title']) ?>">
<span class="chapter__play-icon"><?= icon('play', '', 13) ?></span>
<span><?= $playLabel ?></span>
<?php if ($isPreview): ?><span class="chapter__play-time">0:30</span><?php endif; ?>
</button>
<?php else: ?>
<span class="chapter__soon"><?= icon('lock', '', 14) ?><?= $access || empty($ch['full_url']) ? 'Скоро' : 'В кабинете' ?></span>
<?php endif; ?>
</div>
<h3 class="chapter__title"><?= e(typo($ch['title'])) ?></h3>
<?php if ($ch['text'] !== ''): ?>
<p class="chapter__text"><?= e(typo($ch['text'])) ?></p>
<?php endif; ?>
<?php /* «Обмен опытом»: с доступом — чат главы (chat.js), без доступа — окно с предложением купить */ ?>
<div class="chapter__exchange">
<?php if ($access): ?>
<?php $n = chat_count($i + 1); ?>
<button class="chapter__chat chapter__chat--open" type="button" data-chat="<?= $i + 1 ?>" data-chapter="Глава <?= sprintf('%02d', $i + 1) ?> · <?= e($ch['title']) ?>" aria-haspopup="dialog"><?= icon('message-circle', '', 20) ?><span><?= e($c['exchange_label']) ?></span><?php if ($n): ?><span class="chapter__chat-count" aria-label="сообщений: <?= $n ?>"><?= $n ?></span><?php endif; ?></button>
<?php else: ?>
<button class="chapter__chat" type="button" data-exchange data-chapter="Глава <?= sprintf('%02d', $i + 1) ?> · <?= e($ch['title']) ?>" aria-haspopup="dialog"><?= icon('message-circle', '', 20) ?><span><?= e($c['exchange_label']) ?></span><?= icon('lock', '', 16) ?></button>
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
<?php if (!$access): ?>
<template data-video-preview-end-template>
<div class="video-modal__preview-end">
<p tabindex="-1">Откройте доступ, чтобы посмотреть главу целиком.</p>
<a class="btn video-modal__preview-action" href="#kupit-dostup" data-video-close>Купить доступ<?= icon('arrow-right', '', 18) ?></a>
</div>
</template>
<?php endif; ?>
<?php endif; ?>
<?php if ($c['chapters'] && $access): ?>
<?php require __DIR__ . '/chat-modal.php'; ?>
<?php elseif ($c['chapters']): ?>
<?php $a = content('access'); ?>
<dialog class="review-modal exchange-modal" data-exchange-modal aria-labelledby="exchange-title" tabindex="-1">
<button class="review-modal__close" type="button" data-exchange-close aria-label="Закрыть"><?= icon('x', '', 22) ?></button>
<div class="exchange-modal__head">
<span class="exchange-modal__icon"><?= icon('message-circle', '', 28) ?></span>
<div>
<p class="exchange-modal__title" id="exchange-title"><?= e($c['exchange_label']) ?></p>
<p class="exchange-modal__chapter" data-exchange-chapter></p>
</div>
</div>
<?php /* превью чата — только силуэты сообщений, без текста: показываем формат, не выдумывая отзывов */ ?>
<div class="exchange-modal__preview" aria-hidden="true">
<span class="exchange-modal__bubble"></span>
<span class="exchange-modal__bubble exchange-modal__bubble--right"></span>
<span class="exchange-modal__bubble exchange-modal__bubble--short"></span>
<span class="exchange-modal__lock"><?= icon('lock', '', 22) ?></span>
</div>
<p class="exchange-modal__text"><?= e(typo($c['exchange_hint'])) ?></p>
<div class="exchange-modal__buy">
<p class="exchange-modal__price"><?= e(str_replace(' ', "\u{A0}", $a['price'])) ?></p>
<a class="btn exchange-modal__btn" href="<?= e($a['button_href']) ?>"><?= icon('lock', 'lock-open') ?><?= e($a['button_text']) ?></a>
</div>
<p class="exchange-modal__links"><a href="/#dostup" data-exchange-close>Подробнее о доступе</a><span aria-hidden="true">·</span><a href="/vhod/">Уже есть доступ? Войти</a></p>
</dialog>
<?php endif; ?>
</section>
