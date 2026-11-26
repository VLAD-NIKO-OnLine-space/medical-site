<?php

function serve_course_video(string $url): never
{
    $method = $_SERVER['REQUEST_METHOD'];
    header('Cache-Control: private, no-store');
    header('Cross-Origin-Resource-Policy: same-origin');
    if ($method !== 'GET' && $method !== 'HEAD') {
        header('Allow: GET, HEAD');
        http_response_code(405);
        exit;
    }
    if (!client_has_access(current_client())) {
        http_response_code(403);
        exit;
    }

    $pathPattern = '~\A/course-video/(chapter-[0-9]{2}/(?:index\.m3u8|(?:480p|720p)/(?:index\.m3u8|video\.key|seg-[0-9]{5}\.ts)))\z~';
    if (!preg_match($pathPattern, $url, $matches)) {
        http_response_code(404);
        exit;
    }
    $relative = $matches[1];
    header('Content-Type: ' . match (pathinfo($relative, PATHINFO_EXTENSION)) {
        'm3u8' => 'application/vnd.apple.mpegurl',
        'ts' => 'video/mp2t',
        'key' => 'application/octet-stream',
    });

    if (PHP_SAPI !== 'cli-server') {
        // nginx sends the private file after PHP checks the current purchase entitlement.
        header_remove('Cache-Control');
        header_remove('Cross-Origin-Resource-Policy');
        header('X-Accel-Redirect: /__course_video/' . $relative);
        exit;
    }

    $file = __DIR__ . '/data/course-video/' . $relative;
    if (!is_file($file)) {
        http_response_code(404);
        exit;
    }
    header('Content-Length: ' . filesize($file));
    if ($method === 'GET') {
        readfile($file);
    }
    exit;
}
