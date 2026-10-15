<?php /* очередь модерации: и на странице, и в ответе на «Обновить» (admin.php, ?part=moderation) */ ?>
<?php if (!$queue): ?>
<p class="moderation__empty">Новых отзывов нет. Когда посетитель оставит отзыв на сайте, он появится здесь.</p>
<?php else: ?>
<p class="field__hint">Опубликованный отзыв встаёт первым в разделе «Опубликованные» — там его можно поправить, как обычный. Отзывы с оценкой 1–3 звезды на сайте попадают во вкладку «С замечаниями», 4–5 — в «Положительные».</p>
<div class="moderation">
<?php foreach ($queue as $r): ?>
<article class="moderation__item">
<p class="moderation__meta"><strong><?= e($r['author']) ?></strong> · <?= str_repeat('★', (int) $r['rating']) . str_repeat('☆', 5 - (int) $r['rating']) ?> · <?= review_date((int) $r['created_at']) ?>, <?= date('H:i', (int) $r['created_at']) ?></p>
<p class="moderation__text"><?= nl2br(e($r['text']), false) ?></p>
<div class="moderation__actions">
<?php /* кнопки отправляют отдельную форму (form="review-actions"): вложенные формы в HTML запрещены */ ?>
<button class="admin-btn" type="submit" form="review-actions" name="review" value="approve:<?= (int) $r['id'] ?>"><?= icon('check', '', 18) ?>Опубликовать</button>
<button class="admin-btn admin-btn--ghost" type="submit" form="review-actions" name="review" value="delete:<?= (int) $r['id'] ?>" data-confirm="Удалить отзыв: <?= e($r['author']) ?>? Это нельзя отменить."><?= icon('trash', '', 18) ?>Удалить</button>
</div>
</article>
<?php endforeach; ?>
</div>
<?php endif; ?>
