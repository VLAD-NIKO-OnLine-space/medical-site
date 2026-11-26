<?php

function course_active_chapter(int $clientId, string $loginHash, string $playback): string|false
{
    $query = client_db()->prepare('SELECT chapter FROM client_playbacks WHERE client_id = ? AND login_hash = ? AND playback_hash = ?');
    $query->execute([$clientId, $loginHash, hash('sha256', $playback)]);
    return $query->fetchColumn();
}

function serve_course_playback(array $client, string $loginHash, string $method): never
{
    if (!in_array($method, ['GET', 'POST', 'DELETE'], true)) {
        header('Allow: GET, POST, DELETE');
        http_response_code(405);
        exit;
    }
    if ($method !== 'GET' && (($_SERVER['HTTP_X_COURSE_PLAYBACK'] ?? '') !== '1' || !same_origin())) {
        http_response_code(403);
        exit;
    }

    $id = (int) $client['id'];
    if ($method === 'POST') {
        $chapter = $_POST['chapter'] ?? '';
        if (!is_string($chapter) || !preg_match('/^chapter-[0-9]{2}$/D', $chapter)
            || !in_array('/course-video/' . $chapter . '/index.m3u8', array_column(content('program')['chapters'], 'full_url'), true)) {
            http_response_code(404);
            exit;
        }

        $playback = bin2hex(random_bytes(16));
        $update = client_db()->prepare(
            'INSERT INTO client_playbacks (client_id, login_hash, playback_hash, chapter)
             SELECT c.id, t.token_hash, ?, ? FROM clients c JOIN client_tokens t ON t.client_id = c.id
             WHERE c.id = ? AND t.token_hash = ? AND t.expires > ? AND (c.access_until = 0 OR c.access_until > ?)
             ON CONFLICT(client_id) DO UPDATE SET login_hash = excluded.login_hash, playback_hash = excluded.playback_hash, chapter = excluded.chapter'
        );
        $now = time();
        $update->execute([hash('sha256', $playback), $chapter, $id, $loginHash, $now, $now]);
        if ($update->rowCount() !== 1) {
            http_response_code(403);
            exit;
        }

        header('Content-Type: application/json');
        exit(json_encode([
            'playback' => $playback,
            'src' => '/course-video/play/' . $playback . '/' . $chapter . '/index.m3u8',
            'watermark' => client_watermark_code($id),
        ]));
    }

    $playback = $_SERVER['HTTP_X_PLAYBACK_ID'] ?? '';
    if (!preg_match('/^[0-9a-f]{32}$/D', $playback)) {
        http_response_code(400);
        exit;
    }
    if ($method === 'DELETE') {
        client_db()->prepare('DELETE FROM client_playbacks WHERE client_id = ? AND login_hash = ? AND playback_hash = ?')
            ->execute([$id, $loginHash, hash('sha256', $playback)]);
        http_response_code(204);
        exit;
    }
    http_response_code(course_active_chapter($id, $loginHash, $playback) !== false ? 204 : 409);
    exit;
}

function serve_course_video(string $url): never
{
    $method = $_SERVER['REQUEST_METHOD'];
    header('Cache-Control: private, no-store');
    header('Cross-Origin-Resource-Policy: same-origin');
    $client = current_client();
    $loginHash = client_token_hash();
    if (!client_has_access($client) || $loginHash === null) {
        http_response_code(403);
        exit;
    }
    if ($url === '/course-video/playback/') {
        serve_course_playback($client, $loginHash, $method);
    }
    if ($method !== 'GET' && $method !== 'HEAD') {
        header('Allow: GET, HEAD');
        http_response_code(405);
        exit;
    }
    $pathPattern = '~\A/course-video/play/([0-9a-f]{32})/((chapter-[0-9]{2})/(?:index\.m3u8|(?:480p|720p)/(?:index\.m3u8|video\.key|seg-[0-9]{5}\.ts)))\z~';
    if (!preg_match($pathPattern, $url, $matches)) {
        http_response_code(404);
        exit;
    }
    [, $playback, $relative, $chapter] = $matches;
    if (course_active_chapter((int) $client['id'], $loginHash, $playback) !== $chapter) {
        http_response_code(409);
        exit;
    }
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
