<?php
// даты отзывов и время сохранения в админке — по Москве
date_default_timezone_set('Europe/Moscow');

const SITE_ROBOTS = 'noindex, nofollow, noarchive, nosnippet, noimageindex';

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
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

/** Адрес файла из public/ с меткой версии: после правки файла браузер скачает его заново, а не возьмёт старый из кэша. */
function asset(string $path): string
{
    $file = __DIR__ . '/public' . $path;
    return is_file($file) ? $path . '?v=' . filemtime($file) : $path;
}

function site(): array
{
    static $site;
    return $site ??= require __DIR__ . '/config.php';
}

function legal_consents(): array
{
    static $consents;
    return $consents ??= require __DIR__ . '/content/legal-consents.php';
}

/** Типографика: неразрывный пробел перед тире и после коротких слов — они не повиснут на краю строки. */
function typo(string $s): string
{
    $s = preg_replace('/ ([—–])/u', "\u{00A0}$1", $s);
    // число не отрывается от следующего слова («10 дней»), «№» — от номера
    $s = preg_replace('/(\d|№) (?=[\p{L}\d])/u', "$1\u{00A0}", $s);
    // диапазон «4–5» не разрывается по тире (U+2060 — невидимый запрет переноса);
    // только длинные тире: дефисы в телефонах «000-00-00» не трогаем, иначе номер не скопировать
    $s = preg_replace('/(\d)([–—])(?=\d)/u', "$1\u{2060}$2\u{2060}", $s);
    return preg_replace('/(?<![\p{L}\d])(\p{L}{1,2}) (?=[\p{L}\d«])/u', "$1\u{00A0}", $s);
}

/** Адреса почты в уже экранированном тексте — ссылками mailto. */
function link_emails(string $html): string
{
    return preg_replace('/[\w.+-]+@[\w-]+(?:\.[\w-]+)+/u', '<a href="mailto:$0">$0</a>', $html);
}

/** Текст из админки в абзацы: пустая строка — новый <p>, перенос строки — <br>. */
function paragraphs(string $text, string $class = ''): string
{
    $out = '';
    foreach (preg_split('/\n\s*\n/', trim($text)) as $p) {
        if (trim($p) !== '') {
            $out .= '<p' . ($class !== '' ? ' class="' . $class . '"' : '') . '>' . link_emails(nl2br(e(typo(trim($p))), false)) . "</p>\n";
        }
    }
    return $out;
}

/** Текст документа из админки: пустая строка — новый абзац, строки с «— » или «- » в начале — пункты списка. */
function rich_text(string $text, ?callable $format = null): string
{
    $out = '';
    $format ??= static fn(string $line): string => link_emails(e(typo($line)));
    foreach (preg_split('/\n\s*\n/', trim($text)) as $block) {
        $lines = preg_split('/\n/', trim($block));
        $para = [];
        $list = [];
        $flush = function () use (&$para, &$list, &$out, $format) {
            if ($para) {
                $out .= '<p>' . implode('<br>', array_map($format, $para)) . "</p>\n";
            }
            if ($list) {
                $out .= "<ul>\n" . implode('', array_map(fn($l) => '<li>' . $format($l) . "</li>\n", $list)) . "</ul>\n";
            }
            $para = $list = [];
        };
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (preg_match('/^[—–\-•]\s+(.+)$/u', $line, $m)) {
                if ($para) {
                    $flush();
                }
                $list[] = $m[1];
            } else {
                if ($list) {
                    $flush();
                }
                $para[] = $line;
            }
        }
        $flush();
    }
    return $out;
}

function document_inline(string $text): string
{
    $parts = preg_split('~(https?://[^\s<>"\'()]*[^\s<>"\'().,;:!?])~iu', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($parts as $index => &$part) {
        $part = $index % 2 === 1
            ? '<a href="' . e($part) . '">' . e($part) . '</a>'
            : link_emails(e(typo($part)));
    }
    unset($part);
    return implode('', $parts);
}

function document_text(string $text): string
{
    $out = '';
    foreach (preg_split('/\n\s*\n/', trim($text)) as $block) {
        if (str_starts_with($block, '### ')) {
            $out .= '<h3>' . document_inline(substr($block, 4)) . "</h3>\n";
        } else {
            $lines = explode("\n", $block);
            if (str_starts_with($block, '| ') && preg_match('/^\|(?:\s*---\s*\|)+$/', $lines[1] ?? '')) {
                $out .= document_table($lines);
            } else {
                $out .= rich_text($block, document_inline(...));
            }
        }
    }
    return $out;
}

function document_table(array $lines): string
{
    $cells = static fn(string $line): array => array_map('trim', explode('|', trim(trim($line), '|')));
    $headings = $cells(array_shift($lines));
    array_shift($lines); // Разделитель столбцов после заголовка.
    $out = '<table class="doc__table"><thead><tr>';
    foreach ($headings as $heading) {
        $out .= '<th scope="col">' . document_inline($heading) . '</th>';
    }
    $out .= "</tr></thead>\n<tbody>\n";
    foreach ($lines as $line) {
        $out .= '<tr>';
        foreach ($cells($line) as $cell) {
            $out .= '<td>' . document_inline($cell) . '</td>';
        }
        $out .= "</tr>\n";
    }
    return $out . "</tbody></table>\n";
}

/** Реквизиты продавца из «Подвала» — для подстановки в документы вместо {seller}, {inn} и т. д.; пустое — «—». */
function seller_tokens(): array
{
    $f = content('footer');
    $out = [];
    foreach (['seller', 'inn', 'ogrnip', 'city', 'email', 'phone'] as $k) {
        $out['{' . $k . '}'] = $f[$k] !== '' ? $f[$k] : '—';
    }
    return $out;
}

/** Ссылка tel: из телефона в любом виде: «+7 (900) 123-45-67» → «+79001234567». */
function tel_href(string $phone): string
{
    return 'tel:' . preg_replace('/[^\d+]/', '', $phone);
}

/** Пункты меню шапки (они же «Разделы» в подвале): у клиента с доступом без «Как получить доступ» — секции у него нет. */
function nav_items(): array
{
    $nav = content('header')['nav'];
    if (client_has_access(current_client())) {
        $nav = array_values(array_filter($nav, fn($item) => $item['href'] !== '/#dostup'));
    }
    return $nav;
}

/** Склонение по числу: plural(5, ['отзыв', 'отзыва', 'отзывов']) → «отзывов». */
function plural(int $n, array $forms): string
{
    $n = abs($n) % 100;
    $i = $n % 10;
    return $forms[$n > 10 && $n < 20 ? 2 : ($i === 1 ? 0 : ($i >= 2 && $i <= 4 ? 1 : 2))];
}

/** Инициалы из ФИО для заглушки вместо фото: «Дмитренко Алексей» → «ДА». */
function initials(string $name): string
{
    preg_match_all('/(?<!\p{L})\p{L}/u', $name, $m);
    return implode('', array_slice($m[0], 0, 2));
}

/** Знак логотипа (крест с линией пульса) — один и тот же в шапке и подвале. */
function logo_mark(string $class, int $size = 32): string
{
    return '<svg class="' . $class . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 32 32" aria-hidden="true"><path d="M12 4h8v8h8v8h-8v8h-8v-8H4v-8h8z" fill="currentColor"/><path d="M6 16h5l2-4 4 8 2-4h7" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

/** Название в логотипе: выделенная часть (из админки) — бирюзовым шрифтом, как выделения в заголовках. */
function logo_text(): string
{
    $s = content('site');
    // жадное (.*) — выделяется последнее вхождение, регистр не важен
    if ($s['logo_accent'] === '' || !preg_match('/^(.*)(' . preg_quote($s['logo_accent'], '/') . ')(.*)$/isu', $s['name'], $m)) {
        return '<span class="logo-text">' . e($s['name']) . '</span>';
    }
    return '<span class="logo-text">' . e($m[1]) . '<span class="logo-text__accent">' . e($m[2]) . '</span>' . e($m[3]) . '</span>';
}

function icons(): array
{
    static $icons;
    return $icons ??= require __DIR__ . '/icons.php';
}

function icon(string $name, string $hover = '', int $size = 20): string
{
    $icons = icons();
    if (!isset($icons[$name])) {
        return '';
    }
    $svg = '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' . $icons[$name] . '"/></svg>';
    if (!isset($icons[$hover])) {
        return $svg;
    }
    return '<morph-icon reduced-motion="user" data-hover="' . e($icons[$hover]) . '">' . $svg . '</morph-icon>';
}

/**
 * <picture> для картинки из админки (поле типа image): AVIF и WebP с запасным JPEG/PNG, все ширины из нарезки.
 * $img: src — путь без «-ширина.формат», w — ширины, mw — ширины обрезки для телефона (до 640px), ext — запасной формат.
 */
function picture(array $img, string $sizes, string $alt, string $attrs = '', string $class = ''): string
{
    $set = fn(string $base, array $widths, string $ext): string => e(implode(', ', array_map(fn($w) => "$base-$w.$ext {$w}w", $widths)));
    $formats = ['avif' => 'image/avif', 'webp' => 'image/webp', $img['ext'] => ''];
    $html = '<picture' . ($class !== '' ? ' class="' . $class . '"' : '') . '>';
    if (!empty($img['mw'])) {
        foreach ($formats as $ext => $type) {
            $html .= '<source media="(max-width: 640px)"' . ($type ? ' type="' . $type . '"' : '') . ' srcset="' . $set($img['src'] . '-mobile', $img['mw'], $ext) . '" sizes="100vw">';
        }
    }
    foreach (['avif' => 'image/avif', 'webp' => 'image/webp'] as $ext => $type) {
        $html .= '<source type="' . $type . '" srcset="' . $set($img['src'], $img['w'], $ext) . '" sizes="' . $sizes . '">';
    }
    $mid = $img['w'][intdiv(count($img['w']), 2)];
    return $html . '<img src="' . e("{$img['src']}-$mid.{$img['ext']}") . '" srcset="' . $set($img['src'], $img['w'], $img['ext']) . '" sizes="' . $sizes . '"'
        . ' width="' . (int) $img['width'] . '" height="' . (int) $img['height'] . '" alt="' . e($alt) . '"' . ($attrs !== '' ? ' ' . $attrs : '') . '></picture>';
}

/** Возвращает режим плеера, URL и null при отсутствии ссылки. */
function video_embed(string $url): ?array
{
    if ($url === '') {
        return null;
    }
    if (preg_match('~\.m3u8(\?.*)?$~i', $url)) {
        return ['hls', $url];
    }
    if (preg_match('~\.(mp4|webm|ogv)(\?.*)?$~i', $url)) {
        return ['file', $url];
    }
    if (preg_match('~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/)|youtu\.be/)([\w-]{11})~', $url, $m)) {
        return ['iframe', "https://www.youtube.com/embed/{$m[1]}?autoplay=1&rel=0"];
    }
    if (preg_match('~rutube\.ru/(?:video|play/embed)/([0-9a-f]{32})~i', $url, $m)) {
        return ['iframe', "https://rutube.ru/play/embed/{$m[1]}?autoplay=1"];
    }
    if (preg_match('~(?:vk\.com|vkvideo\.ru)/video(-?\d+)_(\d+)~', $url, $m)) {
        return ['iframe', "https://vkvideo.ru/video_ext.php?oid={$m[1]}&id={$m[2]}&autoplay=1"];
    }
    return ['iframe', $url];
}

function chapter_full_video(array $chapter): ?array
{
    $url = $chapter['full_url'] ?? '';
    if ($url === '' && $chapter['title'] === 'Вступление') {
        $url = $chapter['video_url'] ?? '';
    }
    return video_embed($url);
}

function video_play_icon(string $gradientId): string
{
    $id = e($gradientId);
    $path = icons()['play'];
    return <<<HTML
<span class="video-card__play" aria-hidden="true">
<svg viewBox="3 1 20 22" focusable="false">
<defs>
<linearGradient id="$id" x2="76%" y2="100%">
<stop stop-color="#e7f3f1" stop-opacity=".62"/>
<stop offset=".52" stop-color="#9fb7b5" stop-opacity=".44"/>
<stop offset="1" stop-color="#395955" stop-opacity=".76"/>
</linearGradient>
</defs>
<path d="$path" fill="url(#$id)" stroke="#e3f6f2" stroke-opacity=".56" stroke-width=".24"/>
</svg>
</span>
HTML;
}

function db(): PDO
{
    static $pdo;
    if ($pdo) {
        return $pdo;
    }
    $dir = __DIR__ . '/data';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    $pdo = new PDO('sqlite:' . $dir . '/site.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('CREATE TABLE IF NOT EXISTS content (block TEXT PRIMARY KEY, data TEXT NOT NULL, updated_at TEXT NOT NULL)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS admins (login TEXT PRIMARY KEY, password_hash TEXT NOT NULL)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS login_attempts (ip TEXT NOT NULL, at INTEGER NOT NULL)');
    // отзывы с сайта ждут проверки здесь; после публикации или удаления строка стирается
    $pdo->exec('CREATE TABLE IF NOT EXISTS review_queue (id INTEGER PRIMARY KEY AUTOINCREMENT, author TEXT NOT NULL, rating INTEGER NOT NULL, text TEXT NOT NULL, created_at INTEGER NOT NULL, ip TEXT NOT NULL)');
    return $pdo;
}

function schema(): array
{
    static $schema;
    return $schema ??= require __DIR__ . '/content.php';
}

/** Контент блока: сохранённое в базе поверх значений по умолчанию из content.php. */
function content(string $block): array
{
    static $saved;
    $saved ??= array_column(db()->query('SELECT block, data FROM content')->fetchAll(), 'data', 'block');
    $data = json_decode($saved[$block] ?? '[]', true) ?: [];
    $out = [];
    foreach (schema()[$block]['fields'] as $key => $field) {
        $out[$key] = $data[$key] ?? $field['default'];
    }
    return $out;
}

function save_content(string $block, array $data): void
{
    db()->prepare('INSERT INTO content (block, data, updated_at) VALUES (?, ?, ?)
        ON CONFLICT(block) DO UPDATE SET data = excluded.data, updated_at = excluded.updated_at')
        ->execute([$block, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), date('c')]);
}

function pages(): array
{
    $dir = __DIR__ . '/pages';
    $out = [];
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($files as $f) {
        if ($f->getExtension() !== 'php') {
            continue;
        }
        $rel = substr($f->getPathname(), strlen($dir) + 1, -4);
        if ($rel === '404') {
            continue;
        }
        $path = preg_replace('#(^|/)index$#', '', $rel);
        $out[$path === '' ? '/' : "/$path/"] = $f->getPathname();
    }
    ksort($out);
    return $out;
}

function render(string $file, string $url): string
{
    $site = site();
    // секции могут дописать в styles/scripts свои файлы: страница рендерится раньше, чем head
    $page = ['title' => '', 'description' => '', 'styles' => [], 'scripts' => []];
    // страницы зависят от того, вошёл ли клиент: браузер каждый раз сверяется с сервером, прокси не хранят
    if (!headers_sent() && PHP_SAPI !== 'cli') {
        header('Cache-Control: private, no-cache');
        header('Vary: Cookie');
    }
    ob_start();
    require $file;
    $content = ob_get_clean();
    ob_start();
    require __DIR__ . '/templates/layout.php';
    return ob_get_clean();
}

function render_404(): string
{
    return render(__DIR__ . '/pages/404.php', '/404/');
}

/** Манифест для установки сайта на телефон: название — из админки. */
function manifest(): string
{
    $name = content('site')['name'];
    return json_encode([
        'name' => $name,
        'short_name' => $name,
        'lang' => site()['lang'],
        'start_url' => '/',
        'display' => 'standalone',
        'background_color' => '#f1f9f9',
        'theme_color' => '#f1f9f9',
        'icons' => [
            ['src' => '/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png'],
            ['src' => '/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png'],
            ['src' => '/icon-maskable-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n";
}

function robots(): string
{
    // Роботы должны увидеть noindex, чтобы убрать найденные URL из поиска.
    return "User-agent: *\nAllow: /\n";
}

require __DIR__ . '/client.php';
require __DIR__ . '/chat.php';
