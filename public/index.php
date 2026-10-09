<?php
require __DIR__ . '/../lib.php';

$url = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if (PHP_SAPI === 'cli-server' && $url !== '/' && is_file(__DIR__ . $url)) {
    return false;
}

if ($url === '/sitemap.xml') {
    header('Content-Type: application/xml; charset=utf-8');
    exit(sitemap());
}
if ($url === '/site.webmanifest') {
    header('Content-Type: application/manifest+json; charset=utf-8');
    exit(manifest());
}
if ($url === '/robots.txt') {
    header('Content-Type: text/plain; charset=utf-8');
    exit(robots());
}

if ($url === '/admin' || str_starts_with($url, '/admin/')) {
    require __DIR__ . '/../admin/admin.php';
    exit;
}

$pages = pages();
if (isset($pages[$url])) {
    exit(render($pages[$url], $url));
}
if (isset($pages["$url/"])) {
    header("Location: $url/", true, 301);
    exit;
}
http_response_code(404);
echo render_404();
