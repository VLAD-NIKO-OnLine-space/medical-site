<footer class="footer">
<div class="container">
<ul>
<?php foreach ($site['footer_links'] as $href => $label): ?>
<li><a href="<?= e($href) ?>"><?= e($label) ?></a></li>
<?php endforeach; ?>
</ul>
<p>&copy; <?= date('Y') ?> <?= e($site['site_name']) ?></p>
</div>
</footer>
