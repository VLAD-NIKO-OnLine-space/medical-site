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
<meta property="og:title" content="<?= e($page['title']) ?>">
<meta property="og:description" content="<?= e($page['description']) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap">
<link rel="stylesheet" href="/assets/css/style.css">
<script>document.documentElement.classList.add("js")</script>
<script type="module" src="/assets/js/header.js"></script>
<script type="module" src="/assets/js/icons.js"></script>
</head>
