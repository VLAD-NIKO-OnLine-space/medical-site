<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($sectionTitle) ?> — админка <?= e(content('site')['name']) ?></title>
<link rel="stylesheet" href="<?= e(asset('/assets/admin/admin.css')) ?>">
</head>
<body class="admin">
<header class="topbar">
<p class="topbar__title">Админка <span><?= e(content('site')['name']) ?></span></p>
<?php $queue = review_queue(); ?>
<nav class="tabs" aria-label="Разделы админки">
<?php foreach (ADMIN_SECTIONS as $href => [$g, $title]): ?>
<a class="tabs__link" href="<?= $href ?>"<?= $href === $url ? ' aria-current="page"' : '' ?>><?= e($title) ?><?php if ($g === 'reviews'): ?> <span class="badge" title="Ждут модерации" data-queue-count<?= $queue ? '' : ' hidden' ?>><?= count($queue) ?></span><?php endif; ?></a>
<?php endforeach; ?>
</nav>
<?php /* кнопка снаружи формы редактора — привязана к ней атрибутом form; неактивна, пока ничего не изменено
   (после ошибки сохранения изменения на странице не сохранены — тогда активна сразу) */ ?>
<span class="topbar__status" data-dirty-note<?= $errors ? '' : ' hidden' ?>>Есть несохранённые изменения</span>
<button class="admin-btn topbar__save" type="submit" form="editor-form" data-save<?= $errors ? '' : ' disabled' ?><?= $group === 'chats' ? ' hidden' : '' ?>><?= icon('save', '', 18) ?><span>Сохранить</span></button>
<a class="topbar__link" href="/" target="_blank" rel="noopener" aria-label="Открыть сайт"><?= icon('external-link', '', 18) ?><span>Открыть сайт</span></a>
<form method="post" action="<?= ADMIN_URL ?>">
<input type="hidden" name="csrf" value="<?= e($csrf) ?>">
<input type="hidden" name="action" value="logout">
<button class="topbar__link" type="submit" aria-label="Выйти"><?= icon('log-out', '', 18) ?><span>Выйти</span></button>
</form>
</header>
<?php
// «Отзывы» — три панели, видна одна (переключает меню слева). После ошибки сохранения открыта панель с ошибкой
$pane = null;
if ($group === 'reviews') {
    $panes = ['moderation' => 'На модерации', 'published' => 'Опубликованные', 'heading' => 'Заголовок секции'];
    $errKeys = implode(' ', array_keys($errors));
    $pane = str_contains($errKeys, 'content[reviews][items]') ? 'published' : ($errors ? 'heading' : (isset($panes[$_POST['pane'] ?? '']) ? $_POST['pane'] : 'moderation'));
    $published = count($values['reviews']['items'] ?? []);
}
?>
<div class="layout">
<nav class="sidenav" aria-label="Блоки раздела «<?= e($sectionTitle) ?>»"<?= $pane ? ' data-panes' : '' ?>>
<?php if ($group === 'chats'): ?>
<a href="#chats">Сообщения</a>
<a href="#clients">Клиенты</a>
<?php elseif ($group === 'reviews'): ?>
<?php foreach ($panes as $id => $title): ?>
<a href="#<?= $id ?>" data-pane-link="<?= $id ?>"<?= $id === $pane ? ' aria-current="page"' : '' ?>><?= $title ?><?php if ($id === 'moderation'): ?> <span class="badge" data-queue-count<?= $queue ? '' : ' hidden' ?>><?= count($queue) ?></span><?php elseif ($id === 'published'): ?> <span class="badge badge--muted"><?= $published ?></span><?php endif; ?></a>
<?php endforeach; ?>
<?php else: ?>
<?php foreach ($blocks as $block => $b): ?>
<a href="#block-<?= e($block) ?>"><?= e($b['title']) ?></a>
<?php endforeach; ?>
<?php endif; ?>
</nav>
<form class="editor" id="editor-form" method="post" action="<?= e($url) ?>" enctype="multipart/form-data" data-editor novalidate>
<input type="hidden" name="csrf" value="<?= e($csrf) ?>">
<input type="hidden" name="action" value="save">
<?php if ($pane): ?>
<input type="hidden" name="pane" value="<?= $pane ?>" data-pane-input>
<?php endif; ?>
<?php if ($flash): ?>
<p class="alert alert--ok" role="status"><?= icon('check', '', 18) ?><?= e($flash) ?></p>
<?php endif; ?>
<?php if ($errors): ?>
<p class="alert alert--error" role="alert">Не сохранено: исправьте поля, отмеченные красным.</p>
<?php endif; ?>
<?php if ($group === 'chats'): ?>
<?php
$messages = chat_recent();
$chapters = content('program')['chapters'];
$clients = client_db()->query('SELECT login, name, email, access_until, created_at FROM clients ORDER BY id DESC')->fetchAll();
?>
<section class="panel" id="chats">
<h2 class="panel__title">Сообщения в «Обмене опытом» <span class="badge badge--muted"><?= count($messages) ?></span></h2>
<p class="field__hint">Сообщения клиентов с доступом появляются в чатах глав сразу. Здесь последние 300 со всех глав — неподходящие можно удалить.</p>
<?php if (!$messages): ?>
<p class="moderation__empty">Сообщений пока нет.</p>
<?php else: ?>
<div class="moderation">
<?php foreach ($messages as $m): ?>
<article class="moderation__item">
<p class="moderation__meta"><strong><?= e($m['name']) ?></strong> (<?= e($m['login']) ?>) · Глава <?= sprintf('%02d', $m['chapter']) ?><?= isset($chapters[$m['chapter'] - 1]) ? ' «' . e($chapters[$m['chapter'] - 1]['title']) . '»' : '' ?> · <?= date('d.m.y, H:i', (int) $m['created_at']) ?></p>
<p class="moderation__text"><?= nl2br(e($m['text']), false) ?></p>
<div class="moderation__actions">
<button class="admin-btn admin-btn--ghost" type="submit" form="chat-actions" name="id" value="<?= (int) $m['id'] ?>" data-confirm="Удалить это сообщение? Это нельзя отменить."><?= icon('trash', '', 18) ?>Удалить</button>
</div>
</article>
<?php endforeach; ?>
</div>
<?php endif; ?>
</section>
<section class="panel" id="clients">
<h2 class="panel__title">Клиенты <span class="badge badge--muted"><?= count($clients) ?></span></h2>
<p class="field__hint">Пока оплата не подключена, клиентов добавляют командой на сервере: <code>php client-user.php логин пароль "Имя" почта</code>, доступ бессрочный; закрыть доступ — то же с <code>--close</code>.</p>
<div class="clients">
<?php foreach ($clients as $cl): ?>
<p class="clients__row"><strong><?= e($cl['name']) ?></strong> · <?= e($cl['login']) ?><?= $cl['email'] !== '' ? ' · ' . e($cl['email']) : '' ?> · <?= client_has_access($cl) ? 'доступ бессрочный' : '<span class="clients__off">доступ закрыт</span>' ?></p>
<?php endforeach; ?>
</div>
</section>
<?php endif; ?>
<?php if ($group === 'reviews'): ?>
<section class="panel" id="moderation" data-pane="moderation"<?= $pane === 'moderation' ? '' : ' hidden' ?>>
<h2 class="panel__title">Отзывы на модерации <span class="badge" data-queue-count<?= $queue ? '' : ' hidden' ?>><?= count($queue) ?></span>
<button type="button" class="panel__refresh" data-moderation-refresh><?= icon('refresh-cw', '', 16) ?><span>Обновить</span></button>
<span class="panel__updated" data-moderation-updated aria-live="polite"></span></h2>
<div data-moderation-list>
<?php require __DIR__ . '/moderation.php'; ?>
</div>
</section>
<?php endif; ?>
<?php if ($group === 'reviews'): ?>
<?php $rf = $blocks['reviews']['fields']; ?>
<section class="panel" id="published" data-pane="published"<?= $pane === 'published' ? '' : ' hidden' ?>>
<h2 class="panel__title">Опубликованные отзывы <span class="badge badge--muted"><?= $published ?></span></h2>
<div class="panel__fields">
<?php /* подпись списка повторяла бы заголовок панели */ ?>
<?= field_html(['label' => ''] + $rf['items'], $values['reviews']['items'] ?? [], 'content[reviews][items]', $errors) ?>
</div>
</section>
<section class="panel" id="heading" data-pane="heading"<?= $pane === 'heading' ? '' : ' hidden' ?>>
<h2 class="panel__title">Заголовок секции отзывов</h2>
<div class="panel__fields">
<?php foreach ($rf as $key => $f): ?>
<?php if ($key !== 'items'): ?>
<?= field_html($f, $values['reviews'][$key] ?? '', "content[reviews][$key]", $errors) ?>
<?php endif; ?>
<?php endforeach; ?>
</div>
</section>
<?php endif; ?>
<?php foreach ($group === 'reviews' ? [] : $blocks as $block => $b): ?>
<section class="panel" id="block-<?= e($block) ?>">
<h2 class="panel__title"><?= e($b['title']) ?><?php if (isset($b['url'])): ?> <a class="panel__link" href="<?= e($b['url']) ?>" target="_blank" rel="noopener"><?= icon('external-link', '', 16) ?>Открыть страницу</a><?php endif; ?></h2>
<div class="panel__fields">
<?php foreach ($b['fields'] as $key => $f): ?>
<?= field_html($f, $values[$block][$key] ?? '', "content[$block][$key]", $errors) ?>
<?php endforeach; ?>
</div>
</section>
<?php endforeach; ?>
</form>
</div>
<form id="chat-actions" method="post" action="<?= ADMIN_URL ?>chaty/" hidden>
<input type="hidden" name="csrf" value="<?= e($csrf) ?>">
<input type="hidden" name="action" value="chat_delete">
</form>
<form id="review-actions" method="post" action="<?= ADMIN_URL ?>otzyvy/" hidden>
<input type="hidden" name="csrf" value="<?= e($csrf) ?>">
<input type="hidden" name="action" value="review">
</form>
<script src="<?= e(asset('/assets/admin/admin.js')) ?>" defer></script>
</body>
</html>
