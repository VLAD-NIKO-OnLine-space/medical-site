<?php $c = content('access'); ?>
<section class="access" id="dostup" aria-labelledby="access-title">
<div class="container access__grid">
<div class="access__body">
<?php if ($c['eyebrow'] !== ''): ?>
<div class="eyebrow"><?= e($c['eyebrow']) ?></div>
<?php endif; ?>
<h2 class="section-title" id="access-title"><?= e(typo($c['title'])) ?><?php if ($c['title_accent'] !== ''): ?> <span class="text-accent"><?= e(typo($c['title_accent'])) ?></span><?php endif; ?></h2>
<?php if ($c['steps']): ?>
<ol class="access__steps">
<?php foreach ($c['steps'] as $i => $step): ?>
<li class="access__step"><span class="access__num" aria-hidden="true"><?= $i + 1 ?></span><p><?= e(typo($step['text'])) ?></p></li>
<?php endforeach; ?>
</ol>
<?php endif; ?>
<?php if ($c['note'] !== ''): ?>
<p class="access__note"><?= icon('info', '', 18) ?><small><?= e(typo($c['note'])) ?></small></p>
<?php endif; ?>
</div>
<div class="access-card">
<?php if ($c['card_label'] !== ''): ?>
<p class="access-card__label"><?= e($c['card_label']) ?></p>
<?php endif; ?>
<h3 class="access-card__title"><?= e(typo($c['card_title'])) ?></h3>
<p class="access-card__price"><?= e(str_replace(" ", "\u{A0}", $c["price"])) ?></p>
<?php if ($c['features']): ?>
<ul class="access-card__features">
<?php foreach ($c['features'] as $feature): ?>
<li><span class="access-card__icon"><?= icon($feature['icon'], '', 18) ?></span><?= e(typo($feature['text'])) ?></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
<a href="<?= e($c['button_href']) ?>" class="btn access-card__btn"><?= icon($c['button_icon'], $c['button_icon_hover']) ?><?= e($c['button_text']) ?></a>
<?php if ($c['card_note'] !== ''): ?>
<p class="access-card__note"><small><?= nl2br(e(typo($c['card_note'])), false) ?></small></p>
<?php endif; ?>
</div>
</div>
</section>
