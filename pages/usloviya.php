<?php
$c = content('terms');
$page['title'] = $c['meta_title'];
$page['description'] = $c['meta_description'];
?>
<article class="doc" aria-labelledby="doc-title">
<header class="container doc__head">
<?php if ($c['eyebrow'] !== ''): ?>
<div class="eyebrow"><?= e($c['eyebrow']) ?></div>
<?php endif; ?>
<h1 class="doc__title" id="doc-title"><?= e(typo($c['title'])) ?><?php if ($c['title_accent'] !== ''): ?> <span class="text-accent"><?= e(typo($c['title_accent'])) ?></span><?php endif; ?></h1>
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
<li><a href="#punkt-<?= $i + 1 ?>"><span><?= $i + 1 ?></span><?= e($s['title']) ?></a></li>
<?php endforeach; ?>
</ol>
</nav>
<div class="doc__body">
<?php foreach ($c['sections'] as $i => $s): ?>
<section class="doc__section" id="punkt-<?= $i + 1 ?>" aria-labelledby="punkt-<?= $i + 1 ?>-title">
<h2 class="doc__section-title" id="punkt-<?= $i + 1 ?>-title"><span class="doc__num"><?= $i + 1 ?></span><?= e(typo($s['title'])) ?></h2>
<div class="doc__text">
<?= rich_text($s['text']) ?>
</div>
</section>
<?php endforeach; ?>
</div>
</div>
<?php endif; ?>
</article>
