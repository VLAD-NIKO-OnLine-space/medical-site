<?php
// Страница-документ: шапка, оглавление и текст.
// Перед подключением страница кладёт в $c блок из content.php (поля как у terms).
// реквизиты продавца подставляются из «Подвала» — в документах они не дублируются
$tokens = seller_tokens();
$structuredDocument ??= false;
$c['intro'] = strtr($c['intro'], $tokens);
foreach ($c['sections'] as $i => &$s) {
    $s['text'] = strtr($s['text'], $tokens);
    $s['heading'] = $s['title'];
    $s['number'] = $structuredDocument ? '' : ($i + 1) . '.';
    if ($structuredDocument && preg_match('/^(\d+\.)\s+(.+)$/u', $s['title'], $matches)) {
        [, $s['number'], $s['heading']] = $matches;
    }
}
unset($s);
$page['title'] = $c['meta_title'];
$page['description'] = $c['meta_description'];
$title = trim($c['title'] . ' ' . ($c['title_accent'] ?? ''));
if ($c['sections']) {
    $page['scripts'][] = '/assets/js/doc-toc.js';
}
?>
<article class="doc" aria-labelledby="doc-title">
<header class="container doc__head">
<h1 class="doc__title" id="doc-title"><?= e(typo($title)) ?></h1>
<?php if (!empty($c['location'])): ?>
<p class="doc__location"><?= e(typo($c['location'])) ?></p>
<?php endif; ?>
<?php if ($c['updated'] !== ''): ?>
<p class="doc__updated"><?= icon('calendar', '', 18) ?><?= e($c['updated']) ?></p>
<?php endif; ?>
<?php if ($c['intro'] !== ''): ?>
<p class="doc__intro"><?= nl2br(e(typo($c['intro'])), false) ?></p>
<?php endif; ?>
</header>
<?php if ($c['sections']): ?>
<div class="container doc__grid">
<nav class="doc__toc" aria-label="Содержание">
<p class="doc__toc-title">Содержание</p>
<ol>
<?php foreach ($c['sections'] as $i => $s): ?>
<li><a href="#punkt-<?= $i + 1 ?>"><?php if ($s['number'] !== ''): ?><span><?= e($s['number']) ?></span> <?php endif; ?><?= e($s['heading']) ?></a></li>
<?php endforeach; ?>
</ol>
</nav>
<div class="doc__body">
<?php foreach ($c['sections'] as $i => $s): ?>
<section class="doc__section" id="punkt-<?= $i + 1 ?>" aria-labelledby="punkt-<?= $i + 1 ?>-title">
<h2 class="doc__section-title" id="punkt-<?= $i + 1 ?>-title"><?php if ($s['number'] !== ''): ?><span class="doc__num"><?= e($s['number']) ?></span> <?php endif; ?><?= e(typo($s['heading'])) ?></h2>
<div class="doc__text">
<?php if (in_array($s['heading'], ['ИНФОРМАЦИЯ ОБ ОПЕРАТОРЕ', 'Реквизиты продавца'], true)): ?>
<?php require __DIR__ . '/doc-operator.php'; ?>
<?php else: ?>
<?= $structuredDocument ? document_text($s['text']) : rich_text($s['text']) ?>
<?php endif; ?>
</div>
</section>
<?php endforeach; ?>
</div>
</div>
<?php endif; ?>
</article>
