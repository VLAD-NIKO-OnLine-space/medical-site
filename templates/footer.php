<?php $f = content('footer'); ?>
<footer class="footer">
<div class="container">
<ul>
<?php foreach ($f['links'] as $link): ?>
<li><a href="<?= e($link['href']) ?>"><?= e($link['label']) ?></a></li>
<?php endforeach; ?>
</ul>
<p>&copy; <?= date('Y') ?> <?= e($f['copyright']) ?></p>
</div>
</footer>
