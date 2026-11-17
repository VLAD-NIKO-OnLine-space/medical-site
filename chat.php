<?php
// Чат «Обмен опытом» по главам: читать и писать могут клиенты с действующим доступом,
// сообщения появляются сразу; неподходящие удаляются в админке (вкладка «Чаты»).
// Глава определяется номером по порядку (1, 2, …): если переставить главы в админке, чаты останутся за номерами.

const CHAT_MAX = 1000;        // символов в сообщении
const CHAT_LIMIT = 10;        // сообщений от клиента
const CHAT_WINDOW = 60;       // за минуту
const CHAT_TITLE_MAX = 120;   // символов в «главной мысли»

function chat_db(): PDO
{
    static $ready = false;
    $db = client_db();
    if (!$ready) {
        $db->exec('CREATE TABLE IF NOT EXISTS chat_messages (id INTEGER PRIMARY KEY AUTOINCREMENT, chapter INTEGER NOT NULL, client_id INTEGER NOT NULL, text TEXT NOT NULL, created_at INTEGER NOT NULL)');
        $db->exec('CREATE INDEX IF NOT EXISTS chat_by_chapter ON chat_messages (chapter, id)');
        // схема сообщения как в «Опыте» заказчика: дата, имя, главная мысль, комментарий.
        // title — главная мысль (у клиентов необязательна); author — имя у записей, добавленных из админки (client_id = 0)
        $cols = array_column($db->query('PRAGMA table_info(chat_messages)')->fetchAll(), 'name');
        if (!in_array('title', $cols, true)) {
            $db->exec("ALTER TABLE chat_messages ADD COLUMN title TEXT NOT NULL DEFAULT ''");
        }
        if (!in_array('author', $cols, true)) {
            $db->exec("ALTER TABLE chat_messages ADD COLUMN author TEXT NOT NULL DEFAULT ''");
        }
        $ready = true;
    }
    return $db;
}

/** Сообщения главы для показа: последние 200, старые сверху. */
function chat_messages(int $chapter, int $viewerId): array
{
    // порядок — по дате (у записей из админки дата задаётся вручную), при равной — по очереди добавления
    $q = chat_db()->prepare("SELECT m.id, m.title, m.text, m.created_at, m.client_id, CASE WHEN m.author <> '' THEN m.author ELSE COALESCE(c.name, 'Участник') END AS name FROM chat_messages m LEFT JOIN clients c ON c.id = m.client_id WHERE m.chapter = ? ORDER BY m.created_at DESC, m.id DESC LIMIT 200");
    $q->execute([$chapter]);
    return array_map(fn($m) => [
        'id' => (int) $m['id'],
        'name' => $m['name'],
        'initials' => initials($m['name']),
        'title' => $m['title'],
        'text' => $m['text'],
        'time' => date('d.m.Y', (int) $m['created_at']),
        'own' => (int) $m['client_id'] !== 0 && (int) $m['client_id'] === $viewerId,
    ], array_reverse($q->fetchAll()));
}

function chat_count(int $chapter): int
{
    $q = chat_db()->prepare('SELECT COUNT(*) FROM chat_messages WHERE chapter = ?');
    $q->execute([$chapter]);
    return (int) $q->fetchColumn();
}

/** /chat/: GET ?ch=N — сообщения главы; POST ch, text — отправить. Ответ — JSON. */
function chat_endpoint(): void
{
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    $client = current_client();
    // не вошёл (или вход сброшен) — 401, вошёл, но доступ закрыт — 403; окно чата покажет, что делать
    if ($client === null) {
        http_response_code(401);
        exit(json_encode(['ok' => false, 'auth' => 'login', 'error' => 'Вы вышли из аккаунта. Войдите снова, чтобы читать и писать в чате.'], JSON_UNESCAPED_UNICODE));
    }
    if (!client_has_access($client)) {
        http_response_code(403);
        exit(json_encode(['ok' => false, 'auth' => 'expired', 'error' => 'Ваш доступ закрыт. Если это ошибка, напишите в поддержку.'], JSON_UNESCAPED_UNICODE));
    }
    $total = count(content('program')['chapters']);
    $chapter = (int) ($_REQUEST['ch'] ?? 0);
    if ($chapter < 1 || $chapter > $total) {
        http_response_code(404);
        exit(json_encode(['ok' => false, 'error' => 'Такой главы нет.'], JSON_UNESCAPED_UNICODE));
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!same_origin()) {
            http_response_code(403);
            exit;
        }
        $text = clean_text($_POST['text'] ?? '', CHAT_MAX, true);
        if ($text === null || $text === '') {
            http_response_code(422);
            exit(json_encode(['ok' => false, 'error' => 'Напишите сообщение — до ' . CHAT_MAX . ' символов.'], JSON_UNESCAPED_UNICODE));
        }
        $title = clean_text($_POST['title'] ?? '', CHAT_TITLE_MAX, false);
        if ($title === null) {
            http_response_code(422);
            exit(json_encode(['ok' => false, 'error' => 'Главная мысль — до ' . CHAT_TITLE_MAX . ' символов.'], JSON_UNESCAPED_UNICODE));
        }
        $db = chat_db();
        $q = $db->prepare('SELECT COUNT(*) FROM chat_messages WHERE client_id = ? AND created_at > ?');
        $q->execute([$client['id'], time() - CHAT_WINDOW]);
        if ($q->fetchColumn() >= CHAT_LIMIT) {
            http_response_code(429);
            exit(json_encode(['ok' => false, 'error' => 'Слишком много сообщений подряд. Подождите минуту.'], JSON_UNESCAPED_UNICODE));
        }
        $db->prepare('INSERT INTO chat_messages (chapter, client_id, title, text, created_at) VALUES (?, ?, ?, ?, ?)')->execute([$chapter, $client['id'], $title, $text, time()]);
    }
    exit(json_encode(['ok' => true, 'messages' => chat_messages($chapter, (int) $client['id'])], JSON_UNESCAPED_UNICODE));
}

/** Для админки: последние сообщения всех глав. */
function chat_recent(int $limit = 300): array
{
    $q = chat_db()->prepare("SELECT m.id, m.chapter, m.title, m.text, m.created_at, CASE WHEN m.author <> '' THEN m.author ELSE COALESCE(c.name, 'Участник') END AS name, COALESCE(c.login, '') AS login FROM chat_messages m LEFT JOIN clients c ON c.id = m.client_id ORDER BY m.id DESC LIMIT ?");
    $q->execute([$limit]);
    return $q->fetchAll();
}

/** «Опыт» из админки: запись от имени автора истории, не привязана к клиенту. Возвращает текст ошибки или null. */
function chat_add_story(int $chapter, string $date, string $author, string $title, string $text): ?string
{
    $total = count(content('program')['chapters']);
    $author = clean_text($author, 60, false);
    $title = clean_text($title, CHAT_TITLE_MAX, false);
    $text = clean_text($text, 3000, true);
    $at = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    if ($chapter < 1 || $chapter > $total) {
        return 'Выберите главу.';
    }
    if ($at === false) {
        return 'Укажите дату.';
    }
    if ($author === null || $author === '' || $title === null || $title === '' || $text === null || $text === '') {
        return 'Заполните имя, главную мысль и комментарий.';
    }
    chat_db()->prepare('INSERT INTO chat_messages (chapter, client_id, author, title, text, created_at) VALUES (?, 0, ?, ?, ?, ?)')
        ->execute([$chapter, $author, $title, $text, $at->setTime(12, 0)->getTimestamp()]);
    return null;
}

function chat_delete(int $id): void
{
    chat_db()->prepare('DELETE FROM chat_messages WHERE id = ?')->execute([$id]);
}
