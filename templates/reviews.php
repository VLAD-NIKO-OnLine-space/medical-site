<?php
$c = content('reviews');
if (!$c['items']) {
    return;
}
$page['styles'][] = '/assets/js/vendor/swiper/swiper.min.css';
$page['scripts'][] = '/assets/js/reviews.js';
$page['scripts'][] = '/assets/js/review-form.js';
// положительные — 4–5 звёзд, «с замечаниями» — 1–3; тон определяет только оценка автора отзыва
$ratings = array_map(fn($item) => max(1, min(5, (int) $item['rating'])), $c['items']);
$total = count($ratings);
$positive = count(array_filter($ratings, fn($r) => $r >= 4));
$average = array_sum($ratings) / $total;
$filters = ['all' => ['Все', $total], 'positive' => ['Положительные', $positive], 'critical' => ['С замечаниями', $total - $positive]];
?>
<section class="reviews" id="otzyvy" aria-labelledby="reviews-title">
<div class="container">
<div class="reviews__head">
<div>
<?php if ($c['eyebrow'] !== ''): ?>
<div class="eyebrow"><?= e($c['eyebrow']) ?></div>
<?php endif; ?>
<?php $sep = preg_match('/(?<!\p{L})\p{L}{1,2}$/u', $c['title']) ? "\u{A0}" : ' '; // предлог в конце не отрывается от цветной части ?>
<h2 class="section-title" id="reviews-title"><?= e(typo($c['title'])) ?><?php if ($c['title_accent'] !== ''): ?><?= $sep ?><span class="text-accent"><?= e(typo($c['title_accent'])) ?></span><?php endif; ?></h2>
</div>
<div class="reviews__actions">
<button type="button" class="btn reviews__write" data-review-open aria-haspopup="dialog"><?= icon('message-square-plus', 'send') ?>Оставить отзыв</button>
<div class="reviews__nav">
<button type="button" class="slider-arrow" data-reviews-prev aria-label="Предыдущие отзывы"><?= icon('arrow-left', 'chevron-left', 22) ?></button>
<button type="button" class="slider-arrow" data-reviews-next aria-label="Следующие отзывы"><?= icon('arrow-right', 'chevron-right', 22) ?></button>
</div>
</div>
</div>
<div class="reviews__bar">
<p class="reviews__score">
<span class="reviews__score-value"><?= number_format($average, 1, ',', '') ?></span>
<span class="reviews__score-stars" role="img" aria-label="Средняя оценка <?= number_format($average, 1, ',', '') ?> из 5">
<?php for ($n = 1; $n <= 5; $n++): ?>
<span class="review__star<?= $n <= round($average) ? ' is-on' : '' ?>"><?= icon('star', '', 18) ?></span>
<?php endfor; ?>
</span>
<span class="reviews__score-count"><?= $total ?> <?= plural($total, ['отзыв', 'отзыва', 'отзывов']) ?></span>
</p>
<div class="reviews__filter" role="group" aria-label="Показать отзывы">
<?php foreach ($filters as $key => [$label, $count]): ?>
<button type="button" class="reviews__filter-btn" data-reviews-filter="<?= $key ?>" aria-pressed="<?= $key === 'all' ? 'true' : 'false' ?>"><?= $label ?> <span><?= $count ?></span></button>
<?php endforeach; ?>
</div>
</div>
<div class="reviews__slider swiper" data-reviews>
<div class="swiper-wrapper">
<?php foreach ($c['items'] as $i => $item): ?>
<?php $rating = max(1, min(5, (int) $item['rating'])); ?>
<article class="reviews__slide swiper-slide" style="--i: <?= $i ?>" data-tone="<?= $rating >= 4 ? 'positive' : 'critical' ?>">
<div class="review">
<div class="review__top">
<p class="review__stars" role="img" aria-label="Оценка <?= $rating ?> из 5">
<?php for ($n = 1; $n <= 5; $n++): ?>
<span class="review__star<?= $n <= $rating ? ' is-on' : '' ?>"><?= icon('star', '', 20) ?></span>
<?php endfor; ?>
</p>
<span class="review__quote"><?= icon('quote', '', 32) ?></span>
</div>
<?= paragraphs($item['text'], 'review__text') ?>
<footer class="review__author">
<span class="review__avatar" aria-hidden="true"><?= e(initials($item['author'])) ?></span>
<div>
<p class="review__name"><?= e($item['author']) ?></p>
<?php if ($item['date'] !== ''): ?>
<p class="review__date"><?= e($item['date']) ?></p>
<?php endif; ?>
</div>
</footer>
</div>
</article>
<?php endforeach; ?>
</div>
</div>
<p class="reviews__empty" data-reviews-empty hidden>Отзывов с замечаниями пока нет.</p>
<div class="reviews__pagination" data-reviews-pagination></div>
<?php /* на телефоне кнопка — под отзывами, а не над ними (верхняя там скрыта) */ ?>
<div class="reviews__write-bottom">
<button type="button" class="btn reviews__write" data-review-open aria-haspopup="dialog"><?= icon('message-square-plus', 'send') ?>Оставить отзыв</button>
</div>
</div>
<dialog class="review-modal" data-review-modal aria-labelledby="review-modal-title">
<button class="review-modal__close" type="button" data-review-close aria-label="Закрыть"><?= icon('x', '', 22) ?></button>
<form class="review-form" action="/otzyv/" method="post" data-review-form novalidate>
<p class="review-form__title" id="review-modal-title">Оставить отзыв</p>
<p class="review-form__lead">Расскажите, чем вам помогли видеолекции. Отзыв появится на сайте после проверки.</p>
<label class="review-form__field">
<span class="review-form__label">Ваше имя<span class="review-form__req" aria-hidden="true">*</span></span>
<input class="review-form__input" type="text" name="author" maxlength="60" autocomplete="given-name" placeholder="Например, Анна" required aria-describedby="review-err-author">
<span class="review-form__error" id="review-err-author" data-error="author"></span>
</label>
<fieldset class="review-form__field review-form__rating">
<legend class="review-form__label">Оценка<span class="review-form__req" aria-hidden="true">*</span></legend>
<?php /* звёзды в обратном порядке: при row-reverse подсвечиваются выбранная и все левее через ~ */ ?>
<div class="stars">
<?php for ($n = 5; $n >= 1; $n--): ?>
<input class="stars__input" type="radio" name="rating" value="<?= $n ?>" id="review-star-<?= $n ?>" required>
<label class="stars__star" for="review-star-<?= $n ?>"><?= icon('star', '', 32) ?><span class="visually-hidden"><?= $n ?> из 5</span></label>
<?php endfor; ?>
</div>
<span class="review-form__error" data-error="rating"></span>
</fieldset>
<label class="review-form__field">
<span class="review-form__label">Отзыв<span class="review-form__req" aria-hidden="true">*</span></span>
<textarea class="review-form__input review-form__textarea" name="text" rows="3" maxlength="2000" placeholder="Например: всё объяснено понятно и спокойно" required aria-describedby="review-err-text"></textarea>
<span class="review-form__error" id="review-err-text" data-error="text"></span>
</label>
<?php /* ловушка для ботов: человек это поле не видит и не заполняет */ ?>
<div class="review-form__hp" aria-hidden="true">
<label>Сайт <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
</div>
<p class="review-form__error review-form__error--form" data-error="form" role="alert"></p>
<button class="btn review-form__submit" type="submit"><?= icon('send') ?>Отправить отзыв</button>
<p class="review-form__note"><small><span class="review-form__req" aria-hidden="true">*</span> — обязательные поля. Нажимая «Отправить отзыв», вы соглашаетесь на публикацию имени и текста отзыва на сайте и с <a href="/konfidencialnost/" target="_blank">политикой конфиденциальности</a>.</small></p>
</form>
<div class="review-done" data-review-done hidden>
<span class="review-done__icon"><?= icon('circle-check', '', 40) ?></span>
<p class="review-done__title">Спасибо за отзыв!</p>
<p class="review-done__text">Он появится на сайте после проверки.</p>
<button class="btn" type="button" data-review-close>Закрыть</button>
</div>
</dialog>
</section>
