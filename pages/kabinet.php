<?php
// Личный кабинет клиента: главы с фрагментами и чатами, выход. Доступ бессрочный.
$me = current_client();
if (!$me) {
    if (PHP_SAPI === 'cli') {
        return; // в статической сборке кабинета нет — нужен сервер с PHP
    }
    header('Location: /vhod/', true, 303);
    exit;
}
$access = client_has_access($me);
$p = content('program');
$page['title'] = 'Личный кабинет — ' . content('site')['name'];
$page['description'] = 'Личный кабинет зрителя видеогида.';
$page['robots'] = 'noindex, nofollow';
?>
<section class="cabinet" aria-labelledby="cabinet-title">
<div class="container">
<div class="cabinet__head">
<div>
<div class="eyebrow">Личный кабинет</div>
<h1 class="cabinet__title" id="cabinet-title">Здравствуйте, <span class="text-accent"><?= e($me['name']) ?></span></h1>
</div>
<form method="post" action="/vyhod/">
<button class="btn btn--ghost" type="submit"><?= icon('log-out', '', 20) ?>Выйти</button>
</form>
</div>
<div class="cabinet__status<?= $access ? '' : ' cabinet__status--off' ?>">
<?= icon($access ? 'badge-check' : 'lock', '', 28) ?>
<div>
<?php if ($access): ?>
<p class="cabinet__status-title">Доступ открыт</p>
<p class="cabinet__status-text">Все главы и обмен опытом — ниже. Смотрите и пересматривайте сколько угодно.</p>
<?php else: ?>
<?php $support = content('footer')['email']; ?>
<p class="cabinet__status-title">Доступ закрыт</p>
<p class="cabinet__status-text">Если это ошибка, напишите в поддержку<?= $support !== '' ? ' — <a href="mailto:' . e($support) . '">' . e($support) . '</a>' : '' ?>.</p>
<?php endif; ?>
</div>
</div>
<?php if ($p['chapters']): ?>
<h2 class="cabinet__subtitle">Главы видеогида</h2>
<ol class="cabinet__chapters">
<?php foreach ($p['chapters'] as $i => $ch): ?>
<?php $video = video_embed($ch['video_url']); $num = sprintf('%02d', $i + 1); $label = 'Глава ' . $num . ' · ' . $ch['title']; ?>
<li class="cabinet__card" style="--tone: <?= $i % 4 ?>">
<?php /* обложка: градиент и крупный номер; с видео — вся обложка открывает его */ ?>
<?php if ($access && $video): ?>
<button class="cabinet__cover" type="button" data-video-open data-video-type="<?= $video[0] ?>" data-video-src="<?= e($video[1]) ?>" aria-label="Смотреть: <?= e($label) ?>">
<?php else: ?>
<div class="cabinet__cover">
<?php endif; ?>
<span class="cabinet__cover-label">Глава</span>
<span class="cabinet__cover-num" aria-hidden="true"><?= $num ?></span>
<?php if ($access && $video): ?>
<span class="cabinet__cover-play" aria-hidden="true"><?= icon('play', '', 30) ?></span>
<?php else: ?>
<span class="cabinet__cover-chip"><?= icon('clock', '', 14) ?>Видео скоро</span>
<?php endif; ?>
<?php if ($ch['duration'] !== ''): ?>
<span class="cabinet__cover-chip cabinet__cover-chip--time"><?= icon('clock', '', 14) ?><?= e($ch['duration']) ?></span>
<?php endif; ?>
<?= $access && $video ? '</button>' : '</div>' ?>
<div class="cabinet__card-body">
<h3 class="cabinet__card-title"><?= e(typo($ch['title'])) ?></h3>
<?php if ($ch['text'] !== ''): ?>
<p class="cabinet__card-text"><?= e(typo($ch['text'])) ?></p>
<?php endif; ?>
<?php if ($access): ?>
<div class="cabinet__card-actions">
<?php if ($video): ?>
<button class="btn cabinet__btn" type="button" data-video-open data-video-type="<?= $video[0] ?>" data-video-src="<?= e($video[1]) ?>" aria-label="Смотреть: <?= e($label) ?>"><?= icon('play', '', 18) ?>Смотреть главу</button>
<?php else: ?>
<span class="cabinet__btn cabinet__btn--off"><?= icon('clock', '', 18) ?>Видео скоро</span>
<?php endif; ?>
<?php $n = chat_count($i + 1); ?>
<button class="btn btn--ghost cabinet__btn" type="button" data-chat="<?= $i + 1 ?>" data-chapter="<?= e($label) ?>" aria-haspopup="dialog"><?= icon('message-circle', '', 18) ?><?= e($p['exchange_label']) ?><?php if ($n): ?><span class="chapter__chat-count"><?= $n ?></span><?php endif; ?></button>
</div>
<?php endif; ?>
</div>
</li>
<?php endforeach; ?>
</ol>
<?php endif; ?>
</div>
<?php if ($access): ?>
<?php require __DIR__ . '/../templates/chat-modal.php'; ?>
<?php endif; ?>
</section>
