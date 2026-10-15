<?php
// Отзывы с сайта: приём из формы в очередь и модерация в админке.
// Опубликованный отзыв переносится в список отзывов (блок reviews) и дальше редактируется как обычный.

const REVIEW_LIMIT = 3;        // отзывов с одного IP
const REVIEW_WINDOW = 3600;    // за час
const REVIEW_QUEUE_MAX = 300;  // больше в очереди не держим — защита базы от засыпания

/** Дата отзыва: «15.10.26». */
function review_date(int $ts): string
{
    return date('d.m.y', $ts);
}

/** Проверка и запись отзыва; возвращает ошибки по полям (пусто — отзыв принят). */
function review_submit(array $post, string $ip): array
{
    // поле-ловушка: человек его не видит, бот заполняет — делаем вид, что всё прошло
    if (trim((string) ($post['website'] ?? '')) !== '') {
        return [];
    }
    $errors = [];
    $author = clean_text($post['author'] ?? '', 60, false);
    if ($author === null || preg_match_all('/\p{L}/u', $author) < 2) {
        $errors['author'] = 'Напишите имя — от 2 до 60 символов.';
    }
    $rating = (int) ($post['rating'] ?? 0);
    if ($rating < 1 || $rating > 5) {
        $errors['rating'] = 'Поставьте оценку от 1 до 5 звёзд.';
    }
    $text = clean_text($post['text'] ?? '', 2000, true);
    if ($text === null || preg_match_all('/./su', $text) < 10) {
        $errors['text'] = 'Напишите отзыв — от 10 до 2000 символов.';
    }
    if ($errors) {
        return $errors;
    }
    $db = db();
    $q = $db->prepare('SELECT COUNT(*) FROM review_queue WHERE ip = ? AND created_at > ?');
    $q->execute([$ip, time() - REVIEW_WINDOW]);
    if ($q->fetchColumn() >= REVIEW_LIMIT || $db->query('SELECT COUNT(*) FROM review_queue')->fetchColumn() >= REVIEW_QUEUE_MAX) {
        return ['form' => 'Слишком много отзывов подряд. Попробуйте позже.'];
    }
    $db->prepare('INSERT INTO review_queue (author, rating, text, created_at, ip) VALUES (?, ?, ?, ?, ?)')
        ->execute([$author, $rating, $text, time(), $ip]);
    return [];
}

/** Обработчик адреса /otzyv/: fetch получает JSON, обычная отправка формы (без JS) — возврат к отзывам. */
function review_endpoint(): void
{
    $json = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($origin !== '' && parse_url($origin, PHP_URL_HOST) !== parse_url('//' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST))) {
        http_response_code($_SERVER['REQUEST_METHOD'] === 'POST' ? 403 : 405);
        exit;
    }
    $errors = review_submit($_POST, $_SERVER['REMOTE_ADDR'] ?? '');
    if ($json) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($errors ? 422 : 200);
        exit(json_encode(['ok' => !$errors, 'errors' => (object) $errors], JSON_UNESCAPED_UNICODE));
    }
    header('Location: /' . ($errors ? '' : '?otzyv=spasibo') . '#otzyvy', true, 303);
    exit;
}

function review_queue(): array
{
    return db()->query('SELECT id, author, rating, text, created_at FROM review_queue ORDER BY created_at DESC')->fetchAll();
}

/** Опубликовать: отзыв встаёт первым в список отзывов на сайте. */
function review_approve(int $id): bool
{
    $db = db();
    $q = $db->prepare('SELECT * FROM review_queue WHERE id = ?');
    $q->execute([$id]);
    $r = $q->fetch();
    if (!$r) {
        return false;
    }
    $data = content('reviews');
    array_unshift($data['items'], [
        'author' => $r['author'],
        'date' => review_date((int) $r['created_at']),
        'rating' => (string) $r['rating'],
        'text' => $r['text'],
    ]);
    $db->beginTransaction();
    save_content('reviews', $data);
    $db->prepare('DELETE FROM review_queue WHERE id = ?')->execute([$id]);
    $db->commit();
    return true;
}

function review_delete(int $id): void
{
    db()->prepare('DELETE FROM review_queue WHERE id = ?')->execute([$id]);
}
