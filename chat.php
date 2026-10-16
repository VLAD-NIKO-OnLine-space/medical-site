<?php
// Чат «Обмен опытом» по главам: читать и писать могут клиенты с действующим доступом,
// сообщения появляются сразу; неподходящие удаляются в админке (вкладка «Чаты»).
// Глава определяется номером по порядку (1, 2, …): если переставить главы в админке, чаты останутся за номерами.

const CHAT_MAX = 1000;        // символов в сообщении
const CHAT_LIMIT = 10;        // сообщений от клиента
const CHAT_WINDOW = 60;       // за минуту

function chat_db(): PDO
{
    static $ready = false;
    $db = client_db();
    if (!$ready) {
        $db->exec('CREATE TABLE IF NOT EXISTS chat_messages (id INTEGER PRIMARY KEY AUTOINCREMENT, chapter INTEGER NOT NULL, client_id INTEGER NOT NULL, text TEXT NOT NULL, created_at INTEGER NOT NULL)');
        $db->exec('CREATE INDEX IF NOT EXISTS chat_by_chapter ON chat_messages (chapter, id)');
        $ready = true;
    }
    return $db;
}

/** Сообщения главы для показа: последние 200, старые сверху. */
function chat_messages(int $chapter, int $viewerId): array
{
    $q = chat_db()->prepare('SELECT m.id, m.text, m.created_at, m.client_id, c.name FROM chat_messages m JOIN clients c ON c.id = m.client_id WHERE m.chapter = ? ORDER BY m.id DESC LIMIT 200');
    $q->execute([$chapter]);
    return array_map(fn($m) => [
        'id' => (int) $m['id'],
        'name' => $m['name'],
        'initials' => initials($m['name']),
        'text' => $m['text'],
        'time' => date('d.m.y, H:i', (int) $m['created_at']),
        'own' => (int) $m['client_id'] === $viewerId,
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
        $db = chat_db();
        $q = $db->prepare('SELECT COUNT(*) FROM chat_messages WHERE client_id = ? AND created_at > ?');
        $q->execute([$client['id'], time() - CHAT_WINDOW]);
        if ($q->fetchColumn() >= CHAT_LIMIT) {
            http_response_code(429);
            exit(json_encode(['ok' => false, 'error' => 'Слишком много сообщений подряд. Подождите минуту.'], JSON_UNESCAPED_UNICODE));
        }
        $db->prepare('INSERT INTO chat_messages (chapter, client_id, text, created_at) VALUES (?, ?, ?, ?)')->execute([$chapter, $client['id'], $text, time()]);
    }
    exit(json_encode(['ok' => true, 'messages' => chat_messages($chapter, (int) $client['id'])], JSON_UNESCAPED_UNICODE));
}

/** Для админки: последние сообщения всех глав. */
function chat_recent(int $limit = 300): array
{
    $q = chat_db()->prepare('SELECT m.id, m.chapter, m.text, m.created_at, c.name, c.login FROM chat_messages m JOIN clients c ON c.id = m.client_id ORDER BY m.id DESC LIMIT ?');
    $q->execute([$limit]);
    return $q->fetchAll();
}

function chat_delete(int $id): void
{
    chat_db()->prepare('DELETE FROM chat_messages WHERE id = ?')->execute([$id]);
}
