<!doctype html>
<html lang="<?= e($site['lang']) ?>">
<?php require __DIR__ . '/head.php'; ?>
<body>
<?php require __DIR__ . '/header.php'; ?>
<main>
<?= $content ?>
</main>
<?php require __DIR__ . '/footer.php'; ?>
<?php $cookieConsent = legal_consents()['cookie']; ?>
<aside class="cookie-banner" aria-labelledby="cookie-banner-title" data-cookie-banner hidden>
<div class="cookie-banner__content">
<h2 id="cookie-banner-title"><span class="cookie-banner__icon"><?= icon('shield-check', '', 18) ?></span>Файлы cookie</h2>
<p>Технические cookie нужны для работы сайта. Нажимая «Согласен», вы <a href="<?= e($cookieConsent['path']) ?>">даёте согласие</a> на использование аналитических cookie и обработку данных Яндекс.Метрикой для улучшения сайта. <a href="/konfidencialnost/#punkt-6">Больше о файлах cookie</a>.</p>
</div>
<div class="cookie-banner__actions">
<button type="button" data-cookie-choice="accepted">Согласен</button>
<button type="button" data-cookie-choice="necessary">Только необходимые</button>
</div>
</aside>
<script type="module" src="<?= e(asset('/assets/js/cookie-consent.js')) ?>"></script>
<?php /* окно видео — общее для первого экрана и фрагментов глав; название подставляет video.js */ ?>
<dialog class="video-modal" data-video-modal aria-label="Видео">
<button class="video-modal__close" type="button" data-video-close aria-label="Закрыть видео"><?= icon('x', '', 22) ?></button>
<div class="video-modal__frame" data-video-frame></div>
</dialog>
<template data-video-controls-template>
<div class="video-controls" role="group" aria-label="Управление видео">
<input class="video-controls__seek" type="range" min="0" max="0" step="any" value="0" aria-label="Позиция воспроизведения" data-video-seek disabled>
<div class="video-controls__bar">
<button class="video-controls__button" type="button" aria-label="Воспроизвести" data-video-play>
<?= icon('play') ?><?= icon('pause') ?>
</button>
<span class="video-controls__time" data-video-time>0:00 / —</span>
<button class="video-controls__button" type="button" aria-label="Выключить звук" data-video-mute>
<?= icon('volume-2') ?><?= icon('volume-x') ?>
</button>
<button class="video-controls__button" type="button" aria-label="Полноэкранный режим" data-video-fullscreen hidden><?= icon('maximize') ?></button>
</div>
<p class="video-controls__notice" role="status" data-video-notice></p>
</div>
</template>
</body>
</html>
