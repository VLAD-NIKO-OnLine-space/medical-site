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
$chapters = $p['chapters'];
$support = content('footer')['email'];
$profileName = $me['name'] !== '' ? $me['name'] : $me['login'];
$chapterVideos = [];
$introVideo = null;
foreach ($chapters as $i => $chapter) {
    $chapterVideos[$i] = chapter_full_video($chapter);
    if ($chapter['title'] === 'Вступление' && !$introVideo) {
        $introVideo = $chapterVideos[$i];
    }
}
$availableCount = count(array_filter($chapterVideos));
$chapterCount = count($chapters);
$chapterWord = plural($chapterCount, ['главы', 'глав', 'глав']);
$availability = $chapterCount ? "Можно смотреть $availableCount из $chapterCount $chapterWord" : 'Главы появятся здесь.';
$page['title'] = 'Личный кабинет — ' . content('site')['name'];
$page['description'] = 'Личный кабинет зрителя видеогида.';
?>
<section class="cabinet" aria-labelledby="cabinet-title">
<div class="container">
<div class="cabinet__welcome">
<div class="cabinet__profile-row">
<div class="cabinet__profile">
<span class="cabinet__profile-avatar" aria-hidden="true"><?= e(initials($profileName)) ?></span>
<span class="cabinet__profile-text">
<span>Личный кабинет</span>
<strong title="<?= e($profileName) ?>"><?= e($profileName) ?></strong>
</span>
</div>
<form method="post" action="/vyhod/">
<button class="cabinet__logout" type="submit"><?= icon('log-out', '', 17) ?>Выйти</button>
</form>
</div>
<div class="cabinet__intro">
<h1 class="cabinet__title" id="cabinet-title">Ваш <span>видеогид</span></h1>
<p><?= e(typo('Рак молочной железы. О лечении и жизни после него.')) ?></p>
</div>
<div class="cabinet__status<?= $access ? '' : ' cabinet__status--off' ?>">
<div class="cabinet__status-row">
<span class="cabinet__status-icon"><?= icon($access ? 'badge-check' : 'lock', '', 24) ?></span>
<div>
<?php if ($access): ?>
<p class="cabinet__status-title">Доступ открыт</p>
<p class="cabinet__status-text"><?= e(typo($availability)) ?></p>
<?php else: ?>
<p class="cabinet__status-title">Доступ закрыт</p>
<p class="cabinet__status-text">Если доступ уже оплачен, напишите в поддержку.</p>
<?php endif; ?>
</div>
</div>
<?php if ($access && $chapters): ?>
<a class="cabinet__status-action" href="#kabinet-chapters">Перейти к главам<?= icon('arrow-right', '', 18) ?></a>
<?php elseif (!$access && $support !== ''): ?>
<a class="cabinet__status-action" href="mailto:<?= e($support) ?>">Написать в поддержку<?= icon('arrow-right', '', 18) ?></a>
<?php endif; ?>
</div>
<?php if ($access): ?>
<div class="cabinet__shortcuts">
<?php if ($introVideo): ?>
<button class="cabinet__shortcut" type="button" data-video-open data-video-type="<?= $introVideo[0] ?>" data-video-src="<?= e($introVideo[1]) ?>" aria-label="Смотреть вступление">
<span class="cabinet__shortcut-icon"><?= icon('play', '', 18) ?></span>Вступление
</button>
<?php endif; ?>
<button class="cabinet__shortcut" type="button" data-review-open aria-haspopup="dialog">
<span class="cabinet__shortcut-icon"><?= icon('message-square-plus', '', 18) ?></span>Оставить отзыв
</button>
</div>
<?php endif; ?>
</div>
<?php if ($chapters): ?>
<h2 class="cabinet__subtitle" id="kabinet-chapters">Главы видеогида</h2>
<ol class="cabinet__chapters">
<?php foreach ($chapters as $i => $ch): ?>
<?php
$video = $chapterVideos[$i];
$playable = $access && $video;
$num = sprintf('%02d', $i + 1);
$label = 'Глава ' . $num . ' · ' . $ch['title'];
$portrait = match ($i + 1) {
    1, 14, 16, 17 => 'lecture',
    6, 7, 8, 9, 11, 12 => 'care',
    default => 'consultation',
};
?>
<li class="cabinet__card">
<?php if ($playable): ?>
<button class="cabinet__cover" data-portrait="<?= $portrait ?>" type="button" data-video-open data-video-type="<?= $video[0] ?>" data-video-src="<?= e($video[1]) ?>" aria-label="Смотреть: <?= e($label) ?>">
<?php else: ?>
<div class="cabinet__cover" data-portrait="<?= $portrait ?>">
<?php endif; ?>
<img class="cabinet__cover-photo" src="<?= e(asset('/assets/img/cabinet-cover-' . $portrait . '.webp')) ?>" alt="" width="960" height="1438" loading="<?= $i < 2 ? 'eager' : 'lazy' ?>" decoding="async">
<span class="cabinet__cover-label">Глава</span>
<span class="cabinet__cover-num"><?= $num ?></span>
<?php if ($playable): ?>
<span class="cabinet__cover-play" aria-hidden="true"><?= icon('play', '', 24) ?></span>
<?php else: ?>
<span class="cabinet__cover-chip cabinet__cover-chip--status"><?= icon($video ? 'lock' : 'clock', '', 14) ?><?= $video ? 'Доступ закрыт' : 'Видео скоро' ?></span>
<?php endif; ?>
<?php if ($ch['duration'] !== ''): ?>
<span class="cabinet__cover-chip cabinet__cover-chip--time"><?= icon('clock', '', 14) ?><?= e($ch['duration']) ?></span>
<?php endif; ?>
<?= $playable ? '</button>' : '</div>' ?>
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
<?php require __DIR__ . '/../templates/review-modal.php'; ?>
<?php endif; ?>
</section>
