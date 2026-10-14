<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page['title']) ?></title>
<meta name="description" content="<?= e($page['description']) ?>">
<meta name="robots" content="<?= e($page['robots']) ?>">
<?php if ($url !== '/404/'): ?>
<link rel="canonical" href="<?= e($site['domain'] . $url) ?>">
<meta property="og:url" content="<?= e($site['domain'] . $url) ?>">
<?php endif; ?>
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e(content('site')['name']) ?>">
<meta property="og:title" content="<?= e($page['title']) ?>">
<meta property="og:description" content="<?= e($page['description']) ?>">
<link rel="icon" href="/favicon.ico" sizes="48x48">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<link rel="manifest" href="/site.webmanifest">
<meta name="theme-color" content="#f1f9f9">
<?php /* шрифт нужен уже первому экрану: браузер начинает качать его вместе с CSS, а не после разбора стилей */ ?>
<link rel="preload" href="/assets/fonts/inter-core.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/unbounded-500.woff2" as="font" type="font/woff2" crossorigin>
<?php foreach (array_unique($page['styles']) as $href): ?>
<link rel="stylesheet" href="<?= e(asset($href)) ?>">
<?php endforeach; ?>
<link rel="stylesheet" href="<?= e(asset('/assets/css/style.css')) ?>">
<script>document.documentElement.classList.add("js")</script>
<script type="module" src="<?= e(asset('/assets/js/header.js')) ?>"></script>
<script type="module" src="<?= e(asset('/assets/js/icons.js')) ?>"></script>
<script type="module" src="<?= e(asset('/assets/js/video.js')) ?>"></script>
<?php foreach (array_unique($page['scripts']) as $src): ?>
<script type="module" src="<?= e(asset($src)) ?>"></script>
<?php endforeach; ?>
<?php /* модули Morphicons подгружают друг друга цепочкой — заранее качаем все сразу, без ожидания каждого звена */ ?>
<?php foreach (['element.js', 'controller-CXZuwJ_M.js', 'dom.js', 'spring-CFHloqPP.js', 'normalize-CYnN3Npw.js'] as $f): ?>
<link rel="modulepreload" href="/assets/js/vendor/morphicons/<?= $f ?>">
<?php endforeach; ?>
</head>
