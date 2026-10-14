<?php
$c = content('about');
// иконки тезисов по порядку; для добавленных в админке тезисов — галочка
$pointIcons = ['message-circle', 'list-checks', 'award'];
?>
<section class="about" id="o-gide" aria-labelledby="about-title">
<div class="container about__grid">
<div class="about__intro">
<?php if ($c['eyebrow'] !== ''): ?>
<div class="eyebrow"><?= e($c['eyebrow']) ?></div>
<?php endif; ?>
<h2 class="section-title" id="about-title"><?= e(typo($c['title'])) ?><?php if ($c['title_accent'] !== ''): ?><br> <span class="text-accent"><?= e(typo($c['title_accent'])) ?></span><?php endif; ?></h2>
<?php if ($c['lead'] !== ''): ?>
<p class="about__lead"><?= nl2br(e(typo($c['lead'])), false) ?></p>
<?php endif; ?>
<?php if ($c['points']): ?>
<ul class="about__points">
<?php foreach ($c['points'] as $i => $point): ?>
<li><?= icon($pointIcons[$i] ?? 'check', '', 20) ?><?= e($point['text']) ?></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
</div>
<div class="about__body">
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
</div>
</section>
