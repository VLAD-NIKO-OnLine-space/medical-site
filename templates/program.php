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
<div class="chapter__top">
<span class="chapter__label">Глава <span class="chapter__num"><?= sprintf('%02d', $i + 1) ?></span></span>
<?php if ($video): ?>
<button class="chapter__play" type="button" data-video-open data-video-type="<?= $video[0] ?>" data-video-src="<?= e($video[1]) ?>" aria-label="Смотреть фрагмент: <?= e($ch['title']) ?>"><?= icon('play', '', 14) ?>Смотреть фрагмент</button>
<?php else: ?>
<span class="chapter__soon"><?= icon('lock', '', 14) ?>Скоро</span>
<?php endif; ?>
</div>
<h3 class="chapter__title"><?= e(typo($ch['title'])) ?></h3>
<?php if ($ch['text'] !== ''): ?>
<p class="chapter__text"><?= e(typo($ch['text'])) ?></p>
<?php endif; ?>
<?php if ($ch['duration'] !== ''): ?>
<p class="chapter__duration"><?= icon('clock', '', 16) ?><?= e($ch['duration']) ?></p>
<?php endif; ?>
<?php /* «Обмен опытом» — чат главы для оплативших. Аккаунтов покупателей пока нет — всем открывается окно с предложением купить */ ?>
<div class="chapter__exchange">
<button class="chapter__chat" type="button" data-exchange data-chapter="Глава <?= sprintf('%02d', $i + 1) ?> · <?= e($ch['title']) ?>" aria-haspopup="dialog"><?= icon('message-circle', '', 20) ?><span><?= e($c['exchange_label']) ?></span><?= icon('lock', '', 16) ?></button>
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
<?php if ($c['chapters']): ?>
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
