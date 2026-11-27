<?php
$page['title'] = $consent['label'] . ' — BestOncolog';
$page['description'] = $consent['label'] . ' для пользователей сайта BestOncolog.';
?>
<article class="doc doc--consent" aria-labelledby="doc-title">
<header class="container doc__head">
<h1 class="doc__title" id="doc-title"><?= e(typo($consent['title'])) ?></h1>
</header>
<div class="container">
<div class="doc__text doc__consent-body">
<?php foreach ($consent['body'] as $block): ?>
<?php if (is_array($block)): ?>
<ol>
<?php foreach ($block as $item): ?>
<li><?= document_inline($item) ?></li>
<?php endforeach; ?>
</ol>
<?php else: ?>
<?= document_text($block) ?>
<?php endif; ?>
<?php endforeach; ?>
<?php if (isset($consent['rows'])): ?>
<table class="doc__table doc__consent-table" role="table">
<tbody role="rowgroup">
<?php foreach ($consent['rows'] as $row): ?>
<tr role="row">
<th scope="row" role="rowheader"><?= document_inline($row['label']) ?></th>
<td role="cell">
<?php if (isset($row['note_prefix'])): ?>
<p><?= document_inline($row['note_prefix']) ?><sup><a id="doc-note-ref" href="#doc-note" aria-label="Примечание 1">1</a></sup><?= document_inline($row['text']) ?></p>
<?php else: ?>
<?= document_text($row['text']) ?>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<p class="doc__consent-note" id="doc-note"><a href="#doc-note-ref" aria-label="Вернуться к сноске 1">1.</a> <?= document_inline($consent['note']) ?></p>
<?php endif; ?>
</div>
</div>
</article>
