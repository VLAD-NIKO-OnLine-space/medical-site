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
<div class="container hero__inner">
<div class="hero__content">
<?php if ($c['eyebrow'] !== ''): ?>
<div class="eyebrow"><?= e($c['eyebrow']) ?></div>
<?php endif; ?>
<h1 class="hero__title"><?= e($c['title']) ?><?php if ($c['title_accent'] !== ''): ?><br> <span class="text-accent"><?= e($c['title_accent']) ?></span><?php endif; ?></h1>
<?php if ($c['text'] !== ''): ?>
<p class="hero__text"><?= nl2br(e($c['text']), false) ?></p>
<?php endif; ?>
<a href="<?= e($c['button_href']) ?>" class="btn hero__btn"><?= icon($c['button_icon'], $c['button_icon_hover']) ?><?= e($c['button_text']) ?></a>
</div>
</div>
</section>
