<?php
require __DIR__ . '/../lib.php';

$url = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$courseVideo = str_starts_with($url, '/course-video/');

if (PHP_SAPI === 'cli-server' && !$courseVideo && $url !== '/' && is_file(__DIR__ . $url)) {
    return false;
}

header('X-Robots-Tag: ' . SITE_ROBOTS);

if ($courseVideo) {
    require __DIR__ . '/../course-video.php';
    serve_course_video($url);
}

if ($url === '/sitemap.xml') {
    http_response_code(410);
    exit;
}
if ($url === '/site.webmanifest') {
    header('Content-Type: application/manifest+json; charset=utf-8');
    exit(manifest());
}
if ($url === '/robots.txt') {
    header('Content-Type: text/plain; charset=utf-8');
    exit(robots());
}

if ($url === '/chat/') {
    chat_endpoint();
}
if ($url === '/vyhod/') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && same_origin()) {
        client_logout();
    }
    header('Location: /', true, 303);
    exit;
}

if ($url === '/otzyv/') {
    require __DIR__ . '/../review-queue.php';
    review_endpoint();
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
