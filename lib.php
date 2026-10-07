<?php
function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function site(): array
{
    static $site;
    return $site ??= require __DIR__ . '/config.php';
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
    $page = ['title' => '', 'description' => '', 'robots' => 'index, follow'];
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

function sitemap(): string
{
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach (pages() as $url => $file) {
        $lastmod = date('c', filemtime($file));
        $xml .= '  <url><loc>' . e(site()['domain'] . $url) . "</loc><lastmod>$lastmod</lastmod></url>\n";
    }
    return $xml . "</urlset>\n";
}

function robots(): string
{
    return "User-agent: *\nAllow: /\nDisallow: /admin/\n\nSitemap: " . site()['domain'] . "/sitemap.xml\n";
}
