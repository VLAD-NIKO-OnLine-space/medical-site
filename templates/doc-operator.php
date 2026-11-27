<?php
$operatorLines = preg_split('/\R+/u', trim($s['text']), -1, PREG_SPLIT_NO_EMPTY);
$operatorName = array_shift($operatorLines) ?? '';
?>
<p class="doc__operator-name"><?= document_inline($operatorName) ?></p>
<dl class="doc__operator-details">
<?php foreach ($operatorLines as $line):
    $parts = explode(':', $line, 2);
    $label = trim($parts[0]);
    $value = trim($parts[1] ?? '');
?>
<div class="doc__operator-row">
<dt><?= document_inline($label . (isset($parts[1]) ? ':' : '')) ?></dt>
<dd><?php if ($label === 'Телефон'): ?><a href="<?= e(tel_href($value)) ?>"><?= e(typo($value)) ?></a><?php else: ?><?= document_inline($value) ?><?php endif; ?></dd>
</div>
<?php endforeach; ?>
</dl>
