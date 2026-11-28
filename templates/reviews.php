<?php
$c = content('reviews');
if (!$c['items']) {
    return;
}
$page['styles'][] = '/assets/js/vendor/swiper/swiper.min.css';
$page['scripts'][] = '/assets/js/reviews.js';
// положительные — 4–5 звёзд, «с замечаниями» — 1–3; тон определяет только оценка автора отзыва
$ratings = array_map(fn($item) => max(1, min(5, (int) $item['rating'])), $c['items']);
$total = count($ratings);
$positive = count(array_filter($ratings, fn($r) => $r >= 4));
$critical = $total - $positive;
$average = array_sum($ratings) / $total;
$filters = ['all' => ['Все', $total], 'positive' => ['Положительные', $positive], 'critical' => ['С замечаниями', $critical]];
?>
<section class="reviews" id="otzyvy" aria-labelledby="reviews-title">
<div class="container">
<div class="reviews__head">
<div>
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
<?php $fill = round(max(0, min(1, $average - $n + 1)) * 100); ?>
<?php if ($fill > 0 && $fill < 100): /* дробная звезда: 4,6 — пятая закрашена на 60 % */ ?>
<span class="review__star review__star--part" style="--fill: <?= $fill ?>%"><?= icon('star', '', 18) ?><span class="review__star-fill"><?= icon('star', '', 18) ?></span></span>
<?php else: ?>
<span class="review__star<?= $fill >= 100 ? ' is-on' : '' ?>"><?= icon('star', '', 18) ?></span>
<?php endif; ?>
<?php endfor; ?>
</span>
<span class="reviews__score-count"><?= $total ?> <?= plural($total, ['отзыв', 'отзыва', 'отзывов']) ?></span>
</p>
<?php if ($critical > 0): ?>
<div class="reviews__filter" role="group" aria-label="Показать отзывы">
<?php foreach ($filters as $key => [$label, $count]): ?>
<button type="button" class="reviews__filter-btn" data-reviews-filter="<?= $key ?>" aria-pressed="<?= $key === 'all' ? 'true' : 'false' ?>"><?= $label ?> <span><?= $count ?></span></button>
<?php endforeach; ?>
</div>
<?php endif; ?>
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
<?php require __DIR__ . '/review-modal.php'; ?>
</section>
