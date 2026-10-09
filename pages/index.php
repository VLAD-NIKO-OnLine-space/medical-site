<?php
$page['title'] = content('site')['home_title'];
$page['description'] = content('site')['home_description'];
?>
<?php require __DIR__ . '/../templates/hero.php'; ?>
<?php require __DIR__ . '/../templates/about.php'; ?>
<?php require __DIR__ . '/../templates/faq.php'; ?>
