<?php
$c = content('reviews');
if (!$c['items']) {
    return;
}
$page['styles'][] = '/assets/js/vendor/swiper/swiper.min.css';
$page['scripts'][] = '/assets/js/reviews.js';
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
<div class="reviews__nav">
<button type="button" class="slider-arrow" data-reviews-prev aria-label="Предыдущие отзывы"><?= icon('arrow-left', 'chevron-left', 22) ?></button>
<button type="button" class="slider-arrow" data-reviews-next aria-label="Следующие отзывы"><?= icon('arrow-right', 'chevron-right', 22) ?></button>
</div>
</div>
<div class="reviews__slider swiper" data-reviews>
<div class="swiper-wrapper">
<?php foreach ($c['items'] as $i => $item): ?>
<?php $rating = max(1, min(5, (int) $item['rating'])); ?>
<article class="reviews__slide swiper-slide" style="--i: <?= $i ?>">
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
<div class="reviews__pagination" data-reviews-pagination></div>
</div>
</section>
