<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Админка — <?= e(content('site')['name']) ?></title>
<link rel="stylesheet" href="/assets/admin/admin.css">
</head>
<body class="admin">
<header class="topbar">
<p class="topbar__title">Админка <span><?= e(content('site')['name']) ?></span></p>
<a class="topbar__link" href="/" target="_blank" rel="noopener"><?= icon('external-link', '', 18) ?>Открыть сайт</a>
<form method="post" action="<?= ADMIN_URL ?>">
<input type="hidden" name="csrf" value="<?= e($csrf) ?>">
<input type="hidden" name="action" value="logout">
<button class="topbar__link" type="submit"><?= icon('log-out', '', 18) ?>Выйти</button>
</form>
</header>
<div class="layout">
<nav class="sidenav" aria-label="Разделы">
<?php foreach (schema() as $block => $b): ?>
<a href="#block-<?= e($block) ?>"><?= e($b['title']) ?></a>
<?php endforeach; ?>
</nav>
<form class="editor" method="post" action="<?= ADMIN_URL ?>" data-editor novalidate>
<input type="hidden" name="csrf" value="<?= e($csrf) ?>">
<input type="hidden" name="action" value="save">
<?php if ($flash): ?>
<p class="alert alert--ok" role="status"><?= icon('check', '', 18) ?><?= e($flash) ?></p>
<?php endif; ?>
<?php if ($errors): ?>
<p class="alert alert--error" role="alert">Не сохранено: исправьте поля, отмеченные красным.</p>
<?php endif; ?>
<?php foreach (schema() as $block => $b): ?>
<section class="panel" id="block-<?= e($block) ?>">
<h2 class="panel__title"><?= e($b['title']) ?></h2>
<div class="panel__fields">
<?php foreach ($b['fields'] as $key => $f): ?>
<?= field_html($f, $values[$block][$key] ?? '', "content[$block][$key]", $errors) ?>
<?php endforeach; ?>
</div>
</section>
<?php endforeach; ?>
<div class="savebar">
<span class="savebar__status" data-dirty-note hidden>Есть несохранённые изменения</span>
<button class="admin-btn" type="submit"><?= icon('save', '', 18) ?>Сохранить</button>
</div>
</form>
</div>
<script type="application/json" id="icons-data"><?= json_encode(icons(), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
<script src="/assets/admin/admin.js" defer></script>
</body>
</html>
