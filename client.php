<?php
// Клиенты (покупатели): аккаунты, вход по логину или почте и паролю, запоминание входа на 30 дней.
// Вход хранится в cookie bo_auth: в ней случайный токен, в базе — только его хэш (украденная база не даёт войти).

const CLIENT_COOKIE = 'bo_auth';
const CLIENT_REMEMBER = 30 * 86400;
const CLIENT_LOGIN_LIMIT = 5;
const CLIENT_LOGIN_WINDOW = 900;

function client_db(): PDO
{
    static $ready = false;
    $db = db();
    if (!$ready) {
        $db->exec('CREATE TABLE IF NOT EXISTS clients (id INTEGER PRIMARY KEY AUTOINCREMENT, login TEXT NOT NULL UNIQUE COLLATE NOCASE, email TEXT NOT NULL DEFAULT \'\', name TEXT NOT NULL, password_hash TEXT NOT NULL, access_until INTEGER NOT NULL, created_at INTEGER NOT NULL)');
        $db->exec('CREATE TABLE IF NOT EXISTS client_tokens (token_hash TEXT PRIMARY KEY, client_id INTEGER NOT NULL, expires INTEGER NOT NULL)');
        $db->exec('CREATE TABLE IF NOT EXISTS client_attempts (ip TEXT NOT NULL, at INTEGER NOT NULL)');
        $ready = true;
    }
    return $db;
}

/** Вошедший клиент или null. Результат запоминается на время запроса. */
function current_client(): ?array
{
    static $client = false;
    if ($client !== false) {
        return $client;
    }
    $client = null;
    $token = $_COOKIE[CLIENT_COOKIE] ?? '';
    if (!is_string($token) || !preg_match('/^[0-9a-f]{64}$/', $token)) {
        return null;
    }
    $q = client_db()->prepare('SELECT c.* FROM client_tokens t JOIN clients c ON c.id = t.client_id WHERE t.token_hash = ? AND t.expires > ?');
    $q->execute([hash('sha256', $token), time()]);
    $client = $q->fetch() ?: null;
    return $client;
}

function client_has_access(?array $client): bool
{
    // access_until = 0 — доступ бессрочный (так у всех покупателей); дата — только если доступ закрыли вручную
    return $client !== null && ((int) $client['access_until'] === 0 || (int) $client['access_until'] > time());
}

function client_cookie(string $value, int $expires): void
{
    $https = ($_SERVER['HTTPS'] ?? '') !== '' && $_SERVER['HTTPS'] !== 'off';
    setcookie(CLIENT_COOKIE, $value, ['expires' => $expires, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax', 'secure' => $https]);
}

/** Вход: null — успех (cookie поставлена), иначе текст ошибки. */
function client_login(string $login, string $password): ?string
{
    $db = client_db();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $db->prepare('DELETE FROM client_attempts WHERE at < ?')->execute([time() - CLIENT_LOGIN_WINDOW]);
    $q = $db->prepare('SELECT COUNT(*) FROM client_attempts WHERE ip = ?');
    $q->execute([$ip]);
    if ($q->fetchColumn() >= CLIENT_LOGIN_LIMIT) {
        return 'Слишком много попыток входа. Попробуйте через 15 минут.';
    }
    $q = $db->prepare('SELECT * FROM clients WHERE login = ? OR (email <> \'\' AND email = ? COLLATE NOCASE)');
    $q->execute([$login, $login]);
    $client = $q->fetch();
    if (!$client || !password_verify($password, $client['password_hash'])) {
        $db->prepare('INSERT INTO client_attempts (ip, at) VALUES (?, ?)')->execute([$ip, time()]);
        return 'Неверный логин или пароль.';
    }
    $db->prepare('DELETE FROM client_attempts WHERE ip = ?')->execute([$ip]);
    $db->prepare('DELETE FROM client_tokens WHERE expires < ?')->execute([time()]);
    $token = bin2hex(random_bytes(32));
    $expires = time() + CLIENT_REMEMBER;
    $db->prepare('INSERT INTO client_tokens (token_hash, client_id, expires) VALUES (?, ?, ?)')->execute([hash('sha256', $token), $client['id'], $expires]);
    client_cookie($token, $expires);
    return null;
}

function client_logout(): void
{
    $token = $_COOKIE[CLIENT_COOKIE] ?? '';
    if (is_string($token) && $token !== '') {
        client_db()->prepare('DELETE FROM client_tokens WHERE token_hash = ?')->execute([hash('sha256', $token)]);
    }
    client_cookie('', time() - 3600);
}

/** Запрос со своего сайта (защита форм входа/выхода/чата от отправки с чужих страниц). */
function same_origin(): bool
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    return $origin === '' || parse_url($origin, PHP_URL_HOST) === parse_url('//' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST);
}

/** Создать клиента или сменить ему пароль (для client-user.php и будущей оплаты). Доступ бессрочный; $closed — закрыть доступ. */
function client_upsert(string $login, string $password, string $name, string $email = '', bool $closed = false): void
{
    $db = client_db();
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $until = $closed ? time() - 1 : 0;
    $q = $db->prepare('SELECT id FROM clients WHERE login = ?');
    $q->execute([$login]);
    if ($id = $q->fetchColumn()) {
        $db->prepare('UPDATE clients SET password_hash = ?, name = ?, email = ?, access_until = ? WHERE id = ?')->execute([$hash, $name, $email, $until, $id]);
    } else {
        $db->prepare('INSERT INTO clients (login, email, name, password_hash, access_until, created_at) VALUES (?, ?, ?, ?, ?, ?)')->execute([$login, $email, $name, $hash, $until, time()]);
    }
}
