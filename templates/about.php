<?php
$c = content('about');
$photo = is_file(__DIR__ . '/../public/assets/img/doctor-960.webp');
?>
<section class="about" id="o-vrache" aria-labelledby="about-title">
<div class="container about__grid">
<div class="about__body">
<?php if ($c['eyebrow'] !== ''): ?>
<div class="eyebrow"><?= e($c['eyebrow']) ?></div>
<?php endif; ?>
<h2 class="section-title" id="about-title"><?= e(typo($c['title'])) ?><?php if ($c['title_accent'] !== ''): ?><br> <span class="text-accent"><?= e(typo($c['title_accent'])) ?></span><?php endif; ?></h2>
<?php if ($c['lead'] !== ''): ?>
<p class="about__lead"><?= nl2br(e(typo($c['lead'])), false) ?></p>
<?php endif; ?>
<?php if ($c['points']): ?>
<ul class="about__points">
<?php foreach ($c['points'] as $point): ?>
<li><?= icon($point['icon'], '', 20) ?><?= e($point['text']) ?></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
<div class="about__text">
<?= paragraphs($c['text']) ?>
</div>
<?php if ($c['highlight'] !== ''): ?>
<blockquote class="about__quote">
<p><?= nl2br(e(typo($c['highlight'])), false) ?></p>
</blockquote>
<?php endif; ?>
<?php if ($c['closing'] !== ''): ?>
<div class="about__text">
<?= paragraphs($c['closing']) ?>
</div>
<?php endif; ?>
</div>
<div class="about__media">
<div class="about__photo<?= $photo ? '' : ' about__photo--empty' ?>">
<?php if ($photo): ?>
<picture>
<source type="image/avif" srcset="/assets/img/doctor-640.avif 640w, /assets/img/doctor-960.avif 960w" sizes="(max-width: 1024px) 90vw, 40vw">
<source type="image/webp" srcset="/assets/img/doctor-640.webp 640w, /assets/img/doctor-960.webp 960w" sizes="(max-width: 1024px) 90vw, 40vw">
<img src="/assets/img/doctor-960.png" width="960" height="1200" alt="<?= e($c['photo_alt']) ?>" loading="lazy" decoding="async">
</picture>
<?php else: ?>
<span class="about__monogram" aria-hidden="true"><?= e(initials($c['name'])) ?></span>
<?php endif; ?>
</div>
<div class="about__person">
<?php if ($c['badge'] !== ''): ?>
<div class="about__badge"><?= icon('badge-check', '', 18) ?><?= e($c['badge']) ?></div>
<?php endif; ?>
<div class="about__name"><?= e($c['name']) ?></div>
<?php if ($c['status'] !== ''): ?>
<div class="about__status"><?= e($c['status']) ?></div>
<?php endif; ?>
<?php if ($c['availability'] !== ''): ?>
<div class="about__availability"><?= e($c['availability']) ?></div>
<?php endif; ?>
</div>
</div>
</div>
</section>
