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

function icon(string $name, ?string $hover = null, int $size = 20): string
{
    static $icons;
    $icons ??= require __DIR__ . '/icons.php';
    $svg = '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' . $icons[$name] . '"/></svg>';
    if ($hover === null) {
        return $svg;
    }
    return '<morph-icon reduced-motion="user" data-hover="' . e($icons[$hover]) . '">' . $svg . '</morph-icon>';
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
    return "User-agent: *\nAllow: /\n\nSitemap: " . site()['domain'] . "/sitemap.xml\n";
}
