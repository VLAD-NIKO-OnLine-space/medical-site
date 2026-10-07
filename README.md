# scr-system

Сайт на PHP: шапка, подвал и `<head>` лежат в одном месте, страницы содержат только свой контент. Сайт работает как живой PHP-сайт или собирается в статический HTML.

## Структура

| Что | Где |
|---|---|
| Домен, язык, название, меню, ссылки подвала | `config.php` |
| `<head>`: мета, canonical, og-теги, CSS | `templates/head.php` |
| Шапка | `templates/header.php` |
| Секции главной | `templates/hero.php`, `templates/questions.php` |
| Подвал | `templates/footer.php` |
| Каркас страницы | `templates/layout.php` |
| Страницы | `pages/*.php` |
| Страница 404 | `pages/404.php` |
| CSS, JS, картинки | `public/assets/` |
| Картинка первого экрана | `public/assets/img/hero-*` (AVIF/WebP/JPEG, десктоп и мобильная обрезка) |
| Иконки (Lucide) | `icons.php`, вывод — `<?= icon('calendar') ?>` |
| Анимация иконок при наведении (Morphicons) | `public/assets/js/icons.js`, `public/assets/js/vendor/morphicons/` |
| Маршрутизация (живой режим) | `public/index.php`, `public/.htaccess` |
| Сборка в статику | `build.php` |

## Добавить страницу

Создать файл в `pages/`. Адрес берётся из имени файла:

| Файл | Адрес |
|---|---|
| `pages/index.php` | `/` |
| `pages/kontakty.php` | `/kontakty/` |
| `pages/blog/index.php` | `/blog/` |
| `pages/blog/post.php` | `/blog/post/` |

Содержимое файла:

```php
<?php
$page['title'] = 'Контакты — Название сайта';
$page['description'] = 'Как с нами связаться.';
?>
<div class="container">
<h1>Контакты</h1>
<p>Текст страницы.</p>
</div>
```

Необязательно: `$page['robots'] = 'noindex, follow';` — закрыть страницу от индексации.

Страница сразу открывается по своему адресу и попадает в `sitemap.xml`. В меню она попадает, только если добавить её в `nav` в `config.php`.

## Запуск для просмотра

Все команды выполнять из папки `/root/scr-system`.

### На сервере

```bash
cd /root/scr-system
php -S 127.0.0.1:8000 -t public public/index.php
```

Остановить: `Ctrl+C`.

Сайт слушает только `127.0.0.1`, из интернета он не виден. Правки в `pages/`, `templates/`, `config.php` и `public/assets/` видны сразу после обновления страницы в браузере.

### С другого устройства (SSH-туннель)

1. На сервере запустить сайт командой выше.
2. На своём устройстве выполнить:

   ```bash
   ssh -L 8000:127.0.0.1:8000 root@164.90.165.153
   ```

3. Открыть в браузере **http://localhost:8000**.

Сайт доступен, пока открыто окно с SSH. Если порт 8000 на устройстве занят, заменить первое число: `ssh -L 8080:127.0.0.1:8000 ...` и открыть `http://localhost:8080`.

Можно сделать всё одной командой с устройства — туннель и сервер вместе, сервер остановится при закрытии SSH:

```bash
ssh -t -L 8000:127.0.0.1:8000 root@164.90.165.153 'cd /root/scr-system && php -S 127.0.0.1:8000 -t public public/index.php'
```

## Сборка в статику

```bash
cd /root/scr-system
php build.php
```

Результат — папка `dist/`:

- каждая страница — `путь/index.html`;
- `404.html` и `.htaccess` с `ErrorDocument 404 /404.html`;
- `sitemap.xml` и `robots.txt` — генерируются из списка страниц;
- содержимое `public/assets/`.

Папка `dist/` пересоздаётся с нуля при каждой сборке — руками в ней ничего не править.

## Деплой

**Статика (PHP на хостинге не нужен):** залить содержимое `dist/` в корень сайта на Apache.

**Живой PHP:** залить папку целиком, корнем сайта (DocumentRoot) указать `public/`. Нужен PHP 8.0+ и `mod_rewrite` для `.htaccess`. Остальные папки (`pages/`, `templates/`, `config.php`) остаются вне корня и снаружи недоступны.

## Перед запуском сайта

- В `config.php` заменить `https://example.com` на реальный домен — от него строятся canonical, `og:url`, `sitemap.xml` и `robots.txt`.
- Заменить язык (`lang`), название и демо-тексты страниц.
