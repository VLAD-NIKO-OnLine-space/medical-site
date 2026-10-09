<?php $c = content('faq'); ?>
<section class="faq" id="voprosy" aria-labelledby="faq-title">
<div class="container">
<div class="faq__head">
<?php if ($c['eyebrow'] !== ''): ?>
<div class="eyebrow"><?= e($c['eyebrow']) ?></div>
<?php endif; ?>
<h2 class="section-title" id="faq-title"><?= e(typo($c['title'])) ?><?php if ($c['title_accent'] !== ''): ?> <span class="text-accent"><?= e(typo($c['title_accent'])) ?></span><?php endif; ?></h2>
<?php if ($c['lead'] !== ''): ?>
<p class="faq__lead"><?= nl2br(e(typo($c['lead'])), false) ?></p>
<?php endif; ?>
</div>
<div class="faq__list">
<?php foreach ($c['items'] as $i => $item): ?>
<details class="faq__item" name="faq"<?= $i === 0 ? ' open' : '' ?>>
<summary class="faq__question"><h3 class="faq__title"><?= e(typo($item['question'])) ?></h3><span class="faq__toggle" aria-hidden="true" data-open-icon="<?= e(icons()['x']) ?>"><morph-icon reduced-motion="user"><?= icon('plus', '', 20) ?></morph-icon></span></summary>
<div class="faq__answer">
<?= paragraphs($item['answer']) ?>
</div>
</details>
<?php endforeach; ?>
</div>
</div>
<?php
$ld = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(fn ($item) => [
        '@type' => 'Question',
        'name' => $item['question'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['answer']],
    ], $c['items']),
];
?>
<script type="application/ld+json"><?= json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
</section>
