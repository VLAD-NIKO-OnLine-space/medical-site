<?php
// Админка: вход, редактирование контента по схеме content.php, сохранение в SQLite.
// Подключается из public/index.php для адресов /admin...

const ADMIN_URL = '/admin/';
const LOGIN_LIMIT = 5;
const LOGIN_WINDOW = 900;

require __DIR__ . '/images.php';

function admin_redirect(): never
{
    header('Location: ' . ADMIN_URL, true, 303);
    exit;
}

function admin_login(string $login, string $password): ?string
{
    $db = db();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $db->prepare('DELETE FROM login_attempts WHERE at < ?')->execute([time() - LOGIN_WINDOW]);
    $q = $db->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip = ?');
    $q->execute([$ip]);
    if ($q->fetchColumn() >= LOGIN_LIMIT) {
        return 'Слишком много попыток входа. Попробуйте через 15 минут.';
    }
    $q = $db->prepare('SELECT password_hash FROM admins WHERE login = ?');
    $q->execute([$login]);
    $hash = $q->fetchColumn();
    if ($hash === false || !password_verify($password, $hash)) {
        $db->prepare('INSERT INTO login_attempts (ip, at) VALUES (?, ?)')->execute([$ip, time()]);
        return 'Неверный логин или пароль.';
    }
    $db->prepare('DELETE FROM login_attempts WHERE ip = ?')->execute([$ip]);
    session_regenerate_id(true);
    $_SESSION['admin'] = $login;
    return null;
}

/** Строка без управляющих символов и не длиннее $max; null — если не строка, битый UTF-8 или слишком длинная. */
function clean_text(mixed $v, int $max, bool $multiline): ?string
{
    if (!is_string($v)) {
        return null;
    }
    $v = str_replace("\r\n", "\n", $v);
    if (!$multiline) {
        $v = str_replace("\n", ' ', $v);
    }
    $v = preg_replace('/[\x00-\x08\x0B-\x1F\x7F]/u', '', trim($v));
    if ($v === null || !preg_match('/^.{0,' . $max . '}$/su', $v)) {
        return null;
    }
    return $v;
}

function validate_field(array $f, mixed $v, string $name, array &$errors): mixed
{
    switch ($f['type']) {
        case 'list':
            $items = [];
            foreach (array_values(is_array($v) ? $v : []) as $i => $item) {
                $row = [];
                foreach ($f['fields'] as $key => $sub) {
                    $row[$key] = validate_field($sub, $item[$key] ?? '', "{$name}[$i][$key]", $errors);
                }
                $items[] = $row;
            }
            if (count($items) > 30) {
                $errors[$name] = 'Слишком много элементов, максимум 30.';
            }
            return $items;
        case 'rating':
            $v = is_string($v) ? $v : '';
            if (!in_array($v, ['1', '2', '3', '4', '5'], true)) {
                $errors[$name] = 'Выберите оценку от 1 до 5.';
            }
            return $v;
        case 'url':
            $v = clean_text($v, 500, false) ?? '';
            if ($v === '') {
                if (empty($f['optional'])) {
                    $errors[$name] = 'Укажите ссылку.';
                }
            } elseif (!preg_match('~^(/|#|https?://|mailto:|tel:)\S*$~i', $v)) {
                $errors[$name] = 'Ссылка должна начинаться с /, #, https://, mailto: или tel: и не содержать пробелов.';
            }
            return $v;
        default:
            $multiline = $f['type'] === 'textarea';
            $clean = clean_text($v, $multiline ? 3000 : 300, $multiline);
            if ($clean === null) {
                $errors[$name] = 'Слишком длинный текст или недопустимые символы.';
                return is_string($v) ? $v : '';
            }
            if ($clean === '' && empty($f['optional'])) {
                $errors[$name] = 'Поле не может быть пустым.';
            }
            return $clean;
    }
}

function validate_all(mixed $post): array
{
    $values = [];
    $errors = [];
    foreach (schema() as $block => $b) {
        foreach ($b['fields'] as $key => $f) {
            // картинку меняет только загрузка файла — из формы её значение не берём
            $values[$block][$key] = $f['type'] === 'image'
                ? content($block)[$key]
                : validate_field($f, $post[$block][$key] ?? '', "content[$block][$key]", $errors);
        }
    }
    return [$values, $errors];
}

function field_html(array $f, mixed $value, string $name, array $errors): string
{
    if ($f['type'] === 'list') {
        return list_html($f, is_array($value) ? $value : [], $name, $errors);
    }
    if ($f['type'] === 'image') {
        return image_html($f, $value, $name, $errors);
    }
    $id = 'f-' . preg_replace('/[^a-z0-9_]+/i', '-', $name);
    $value = is_string($value) ? $value : '';
    $error = $errors[$name] ?? null;
    $attrs = ' id="' . $id . '" name="' . e($name) . '"' . ($error ? ' aria-invalid="true"' : '');
    $required = empty($f['optional']) ? ' required' : '';
    switch ($f['type']) {
        case 'textarea':
            $input = '<textarea class="field__input" rows="3"' . $attrs . $required . '>' . e($value) . '</textarea>';
            break;
        case 'rating':
            $options = '';
            foreach (['5', '4', '3', '2', '1'] as $n) {
                $options .= '<option value="' . $n . '"' . ($n === $value ? ' selected' : '') . '>' . str_repeat('★', (int) $n) . str_repeat('☆', 5 - (int) $n) . ' ' . $n . '</option>';
            }
            $input = '<select class="field__input"' . $attrs . '>' . $options . '</select>';
            break;
        default:
            $extra = $f['type'] === 'url' ? ' inputmode="url" spellcheck="false" autocomplete="off"' : '';
            $input = '<input class="field__input" type="text" value="' . e($value) . '"' . $attrs . $required . $extra . '>';
    }
    return '<div class="field' . ($f['type'] === 'textarea' ? ' field--wide' : '') . '">'
        . '<label class="field__label" for="' . $id . '">' . e($f['label']) . '</label>'
        . (isset($f['hint']) ? '<p class="field__hint">' . e($f['hint']) . '</p>' : '')
        . $input
        . ($error ? '<p class="field__error">' . e($error) . '</p>' : '')
        . '</div>';
}

/** Картинка: превью текущей и кнопка замены; файл уходит в upload[блок][поле]. */
function image_html(array $f, array $img, string $name, array $errors): string
{
    $id = 'f-' . preg_replace('/[^a-z0-9_]+/i', '-', $name);
    $error = $errors[$name] ?? null;
    $size = end($img['w']) . '×' . $img['height'] . ' px';
    return '<div class="field field--wide">'
        . '<label class="field__label" for="' . $id . '">' . e($f['label']) . '</label>'
        . (isset($f['hint']) ? '<p class="field__hint">' . e($f['hint']) . '</p>' : '')
        . '<div class="image-field' . (!empty($f['alpha']) ? ' image-field--alpha' : '') . '">'
        . '<img class="image-field__preview" src="' . e("{$img['src']}-{$img['w'][0]}.webp") . '" alt="" data-image-preview>'
        . '<div class="image-field__body">'
        . '<p class="image-field__name" data-image-name>Сейчас: ' . $size . '</p>'
        . '<label class="list__add image-field__btn">' . icon('image-up', '', 18) . 'Заменить картинку'
        . '<input class="image-field__input" type="file" id="' . $id . '" name="' . e(preg_replace('/^content/', 'upload', $name)) . '"'
        . ' accept="image/jpeg,image/png,image/webp,image/avif" data-image-input></label>'
        . '<p class="field__hint">Новая картинка появится на сайте после «Сохранить».</p>'
        . '</div></div>'
        . ($error ? '<p class="field__error">' . e($error) . '</p>' : '')
        . '</div>';
}

function list_html(array $f, array $items, string $name, array $errors): string
{
    $row = function (array $item, string $rowName) use ($f, $errors): string {
        $fields = '';
        foreach ($f['fields'] as $key => $sub) {
            $fields .= field_html($sub, $item[$key] ?? '', "{$rowName}[$key]", $errors);
        }
        return '<div class="list__item" data-list-item>'
            . '<div class="list__fields">' . $fields . '</div>'
            . '<div class="list__tools">'
            . '<button type="button" class="tool" data-list-up aria-label="Выше" title="Выше">' . icon('chevron-up', '', 18) . '</button>'
            . '<button type="button" class="tool" data-list-down aria-label="Ниже" title="Ниже">' . icon('chevron-down', '', 18) . '</button>'
            . '<button type="button" class="tool tool--danger" data-list-remove aria-label="Удалить" title="Удалить">' . icon('trash', '', 18) . '</button>'
            . '</div></div>';
    };
    $html = '<div class="field field--wide list" data-list>'
        . '<p class="field__label">' . e($f['label']) . '</p>'
        . (isset($errors[$name]) ? '<p class="field__error">' . e($errors[$name]) . '</p>' : '')
        . '<div class="list__items" data-list-items>';
    foreach (array_values($items) as $i => $item) {
        $html .= $row(is_array($item) ? $item : [], "{$name}[$i]");
    }
    return $html . '</div>'
        . '<template data-list-template>' . $row([], "{$name}[__i__]") . '</template>'
        . '<button type="button" class="list__add" data-list-add>' . icon('plus', '', 18) . 'Добавить ' . e($f['item']) . '</button>'
        . '</div>';
}

$https = ($_SERVER['HTTPS'] ?? '') !== '' && $_SERVER['HTTPS'] !== 'off';
session_name('admin_sid');
session_set_cookie_params(['path' => ADMIN_URL, 'httponly' => true, 'samesite' => 'Strict', 'secure' => $https]);
session_start();
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
header('X-Frame-Options: DENY');

if ($url !== ADMIN_URL) {
    admin_redirect();
}

$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
$csrf = $_SESSION['csrf'];
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$loginError = null;
$values = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($csrf, (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(400);
        exit('Сессия устарела. Вернитесь назад и обновите страницу.');
    }
    $action = $_POST['action'] ?? '';
    if ($action === 'login') {
        $loginError = admin_login(trim((string) ($_POST['login'] ?? '')), (string) ($_POST['password'] ?? ''));
        if ($loginError === null) {
            admin_redirect();
        }
    } elseif ($action === 'logout') {
        $_SESSION = [];
        session_destroy();
        admin_redirect();
    } elseif ($action === 'save' && isset($_SESSION['admin'])) {
        [$values, $errors] = validate_all($_POST['content'] ?? []);
        // картинки обрабатываем, только когда остальные поля в порядке, — иначе файл пришлось бы выбирать заново впустую
        $replaced = [];
        $created = [];
        if (!$errors) {
            foreach (schema() as $block => $b) {
                foreach ($b['fields'] as $key => $f) {
                    if ($f['type'] === 'image' && ($img = upload_image($f, $block, $key, $errors))) {
                        $replaced[] = $values[$block][$key];
                        $created[] = $values[$block][$key] = $img;
                    }
                }
            }
        }
        if ($errors) {
            array_map('delete_image', $created);
        } else {
            db()->beginTransaction();
            foreach ($values as $block => $data) {
                save_content($block, $data);
            }
            db()->commit();
            array_map('delete_image', $replaced);
            $_SESSION['flash'] = 'Изменения сохранены в ' . date('H:i');
            admin_redirect();
        }
    }
}

if (!isset($_SESSION['admin'])) {
    require __DIR__ . '/views/login.php';
    exit;
}

if ($values === null) {
    foreach (schema() as $block => $b) {
        $values[$block] = content($block);
    }
}
require __DIR__ . '/views/editor.php';
