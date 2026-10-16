<?php
require __DIR__ . '/lib.php';

$dist = __DIR__ . '/dist';

function copy_dir(string $from, string $to): void
{
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($from, FilesystemIterator::SKIP_DOTS)) as $f) {
        $rel = substr($f->getPathname(), strlen($from) + 1);
        if ($rel === 'index.php' || $rel === '.htaccess' || str_starts_with($rel, 'assets/admin/')) {
            continue;
        }
        @mkdir(dirname("$to/$rel"), 0755, true);
        copy($f->getPathname(), "$to/$rel");
    }
}

function write(string $path, string $data): void
{
    @mkdir(dirname($path), 0755, true);
    file_put_contents($path, $data);
}

exec('rm -rf ' . escapeshellarg($dist));
copy_dir(__DIR__ . '/public', $dist);

foreach (pages() as $url => $file) {
    // кабинету нужен вошедший клиент и PHP — в статике его нет
    if ($url === '/kabinet/') {
        continue;
    }
    write($dist . $url . 'index.html', render($file, $url));
}
write("$dist/404.html", render_404());
write("$dist/sitemap.xml", sitemap());
write("$dist/robots.txt", robots());
write("$dist/site.webmanifest", manifest());
// сжатие, кэш и заголовки из public/.htaccess; маршрутизация через index.php статике не нужна
$htaccess = preg_replace('/^Rewrite.*\n/m', '', file_get_contents(__DIR__ . '/public/.htaccess'));
write("$dist/.htaccess", "ErrorDocument 404 /404.html\n" . $htaccess);

echo 'Собрано страниц: ' . count(pages()) . " → dist/\n";
