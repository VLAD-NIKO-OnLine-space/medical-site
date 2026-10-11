<?php $c = content('contacts'); ?>
<section class="contacts" id="kontakty" aria-labelledby="contacts-title">
<div class="container">
<?php if ($c['eyebrow'] !== ''): ?>
<div class="eyebrow"><?= e($c['eyebrow']) ?></div>
<?php endif; ?>
<h2 class="section-title" id="contacts-title"><?= e(typo($c['title'])) ?><?php if ($c['title_accent'] !== ''): ?> <span class="text-accent"><?= e(typo($c['title_accent'])) ?></span><?php endif; ?></h2>
<?php if ($c['items']): ?>
<div class="contacts__grid">
<?php foreach ($c['items'] as $item): ?>
<article class="contact-card">
<div class="contact-card__head">
<?php if ($item['icon'] !== ''): ?>
<span class="contact-card__icon"><?= icon($item['icon'], '', 22) ?></span>
<?php endif; ?>
<h3 class="contact-card__title"><?= e(typo($item['title'])) ?></h3>
</div>
<?= paragraphs($item['text'], 'contact-card__text') ?>
<?php if ($item['note'] !== ''): ?>
<p class="contact-card__note"><small><?= e(typo($item['note'])) ?></small></p>
<?php endif; ?>
</article>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
</section>
