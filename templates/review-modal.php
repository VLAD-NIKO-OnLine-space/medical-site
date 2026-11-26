<?php
$page['scripts'][] = '/assets/js/review-form.js';
?>
<dialog class="review-modal" data-review-modal aria-labelledby="review-modal-title">
<button class="review-modal__close" type="button" data-review-close aria-label="Закрыть"><?= icon('x', '', 22) ?></button>
<form class="review-form" action="/otzyv/" method="post" data-review-form novalidate>
<p class="review-form__title" id="review-modal-title">Оставить отзыв</p>
<p class="review-form__lead">Расскажите о своём впечатлении от видеогида. Отзыв появится на сайте после проверки.</p>
<label class="review-form__field">
<span class="review-form__label">Ваше имя<span class="review-form__req" aria-hidden="true">*</span></span>
<input class="review-form__input" type="text" name="author" maxlength="60" autocomplete="given-name" placeholder="Например, Анна" required aria-describedby="review-err-author">
<span class="review-form__error" id="review-err-author" data-error="author"></span>
</label>
<fieldset class="review-form__field review-form__rating">
<legend class="review-form__label">Оценка<span class="review-form__req" aria-hidden="true">*</span></legend>
<?php /* звёзды в обратном порядке: при row-reverse подсвечиваются выбранная и все левее через ~ */ ?>
<div class="stars">
<?php for ($n = 5; $n >= 1; $n--): ?>
<input class="stars__input" type="radio" name="rating" value="<?= $n ?>" id="review-star-<?= $n ?>" required>
<label class="stars__star" for="review-star-<?= $n ?>"><?= icon('star', '', 32) ?><span class="visually-hidden"><?= $n ?> из 5</span></label>
<?php endfor; ?>
</div>
<span class="review-form__error" data-error="rating"></span>
</fieldset>
<label class="review-form__field">
<span class="review-form__label">Отзыв<span class="review-form__req" aria-hidden="true">*</span></span>
<textarea class="review-form__input review-form__textarea" name="text" rows="3" maxlength="2000" placeholder="Например: всё объяснено понятно и спокойно" required aria-describedby="review-err-text"></textarea>
<span class="review-form__error" id="review-err-text" data-error="text"></span>
</label>
<?php /* ловушка для ботов: человек это поле не видит и не заполняет */ ?>
<div class="review-form__hp" aria-hidden="true">
<label>Сайт <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
</div>
<p class="review-form__error review-form__error--form" data-error="form" role="alert"></p>
<button class="btn review-form__submit" type="submit"><?= icon('send') ?>Отправить отзыв</button>
<p class="review-form__note"><small><span class="review-form__req" aria-hidden="true">*</span> — обязательные поля. Нажимая «Отправить отзыв», вы соглашаетесь на публикацию имени и текста отзыва на сайте и с <a href="/konfidencialnost/" target="_blank">политикой конфиденциальности</a>.</small></p>
</form>
<div class="review-done" data-review-done hidden>
<span class="review-done__icon"><?= icon('circle-check', '', 40) ?></span>
<p class="review-done__title">Спасибо за отзыв!</p>
<p class="review-done__text">Он появится на сайте после проверки.</p>
<button class="btn" type="button" data-review-close>Закрыть</button>
</div>
</dialog>
