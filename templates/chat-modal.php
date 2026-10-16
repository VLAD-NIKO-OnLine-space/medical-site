<?php
// окно чата «Обмен опытом» — только для клиента с доступом; глава подставляется скриптом chat.js
$page['scripts'][] = '/assets/js/chat.js';
?>
<dialog class="review-modal chat-modal" data-chat-modal aria-labelledby="chat-title" tabindex="-1">
<button class="review-modal__close" type="button" data-chat-close aria-label="Закрыть"><?= icon('x', '', 22) ?></button>
<div class="exchange-modal__head">
<span class="exchange-modal__icon"><?= icon('message-circle', '', 28) ?></span>
<div>
<p class="exchange-modal__title" id="chat-title"><?= e(content('program')['exchange_label']) ?></p>
<p class="exchange-modal__chapter" data-chat-chapter></p>
</div>
</div>
<div class="chat-modal__list" data-chat-list aria-live="polite"></div>
<form class="chat-modal__form" data-chat-form>
<label class="visually-hidden" for="chat-text">Сообщение</label>
<textarea class="chat-modal__input" id="chat-text" name="text" rows="2" maxlength="<?= CHAT_MAX ?>" placeholder="Поделитесь опытом или задайте вопрос" required></textarea>
<button class="chat-modal__send" type="submit" aria-label="Отправить"><?= icon('send', '', 20) ?></button>
</form>
<p class="chat-modal__error" data-chat-error role="alert" hidden></p>
<div class="chat-modal__auth" data-chat-auth hidden>
<p data-chat-auth-text></p>
<a class="btn" href="/vhod/" data-chat-auth-link><?= icon('door-closed', 'door-open') ?>Войти снова</a>
</div>
<p class="chat-modal__note"><small>Сообщения видят все зрители с доступом. Не публикуйте личные данные. Советы участников — не замена консультации врача.</small></p>
</dialog>
