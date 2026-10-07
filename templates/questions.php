<?php $c = content('questions'); ?>
<section class="questions" aria-labelledby="questions-title">
<div class="container">
<div class="questions__head">
<?php if ($c['eyebrow'] !== ''): ?>
<div class="eyebrow"><?= e($c['eyebrow']) ?></div>
<?php endif; ?>
<h2 class="questions__title" id="questions-title"><?= e($c['title']) ?><?php if ($c['title_accent'] !== ''): ?><br> <span class="text-accent"><?= e($c['title_accent']) ?></span><?php endif; ?></h2>
<?php if ($c['lead'] !== ''): ?>
<p class="questions__lead"><?= nl2br(e($c['lead']), false) ?></p>
<?php endif; ?>
</div>
<ul class="questions__list">
<?php foreach ($c['cards'] as $i => $card): ?>
<li class="question-card" data-morph-trigger>
<div class="question-card__top">
<span class="question-card__icon"><?= icon($card['icon'], $card['icon_hover'], 26) ?></span>
<span class="question-card__num" aria-hidden="true"><?= sprintf('%02d', $i + 1) ?></span>
</div>
<h3 class="question-card__title"><?= e($card['title']) ?></h3>
<?php if ($card['text'] !== ''): ?>
<p class="question-card__text"><?= nl2br(e($card['text']), false) ?></p>
<?php endif; ?>
</li>
<?php endforeach; ?>
</ul>
</div>
</section>
