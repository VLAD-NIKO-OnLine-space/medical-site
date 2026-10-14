<!doctype html>
<html lang="<?= e($site['lang']) ?>">
<?php require __DIR__ . '/head.php'; ?>
<body>
<?php require __DIR__ . '/header.php'; ?>
<main>
<?= $content ?>
</main>
<?php require __DIR__ . '/footer.php'; ?>
<?php /* окно видео — общее для первого экрана и фрагментов глав; название подставляет video.js */ ?>
<dialog class="video-modal" data-video-modal aria-label="Видео">
<button class="video-modal__close" type="button" data-video-close aria-label="Закрыть видео"><?= icon('x', '', 22) ?></button>
<div class="video-modal__frame" data-video-frame></div>
</dialog>
</body>
</html>
