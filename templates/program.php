<?php $c = content('program'); ?>
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
</section>
