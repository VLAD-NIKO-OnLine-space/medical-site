<?php
// Схема контента: какие блоки и поля редактируются в админке, их типы и значения по умолчанию.
// Типы: text, textarea, url, icon, list (повторяющиеся элементы со своими полями). optional — поле можно оставить пустым.
return [
    'site' => [
        'title' => 'Сайт',
        'fields' => [
            'name' => ['label' => 'Название сайта', 'type' => 'text', 'hint' => 'Логотип в шапке и подпись в подвале', 'default' => 'Название сайта'],
            'home_title' => ['label' => 'Title главной', 'type' => 'text', 'hint' => 'Заголовок вкладки браузера и поисковой выдачи', 'default' => 'Главная — Название сайта'],
            'home_description' => ['label' => 'Description главной', 'type' => 'textarea', 'hint' => 'Описание для поисковиков', 'default' => 'Описание главной страницы.'],
        ],
    ],
    'header' => [
        'title' => 'Шапка',
        'fields' => [
            'nav' => [
                'label' => 'Меню',
                'type' => 'list',
                'item' => 'пункт меню',
                'fields' => [
                    'label' => ['label' => 'Текст', 'type' => 'text'],
                    'href' => ['label' => 'Ссылка', 'type' => 'url'],
                ],
                'default' => [
                    ['label' => 'Программа', 'href' => '/programma/'],
                    ['label' => 'О враче', 'href' => '/o-vrache/'],
                    ['label' => 'Как получить доступ', 'href' => '/dostup/'],
                    ['label' => 'Частые вопросы', 'href' => '/voprosy/'],
                    ['label' => 'Контакты', 'href' => '/kontakty/'],
                ],
            ],
            'button_text' => ['label' => 'Кнопка: текст', 'type' => 'text', 'default' => 'Войти'],
            'button_href' => ['label' => 'Кнопка: ссылка', 'type' => 'url', 'default' => '/vhod/'],
            'button_icon' => ['label' => 'Кнопка: иконка', 'type' => 'icon', 'default' => 'door-closed'],
            'button_icon_hover' => ['label' => 'Кнопка: иконка при наведении', 'type' => 'icon', 'default' => 'door-open'],
        ],
    ],
    'hero' => [
        'title' => 'Первый экран',
        'fields' => [
            'eyebrow' => ['label' => 'Подпись над заголовком', 'type' => 'text', 'optional' => true, 'default' => 'Видеолекции онколога-маммолога'],
            'title' => ['label' => 'Заголовок H1: первая строка', 'type' => 'text', 'default' => 'После лечения.'],
            'title_accent' => ['label' => 'Заголовок H1: вторая строка (розовая)', 'type' => 'text', 'optional' => true, 'default' => 'С пониманием и поддержкой'],
            'text' => ['label' => 'Текст под заголовком', 'type' => 'textarea', 'optional' => true, 'default' => 'Когда лечение позади, вопросы остаются. Разбираемся вместе с врачом, что происходит в периоде восстановления и о чём важно поговорить на приёме.'],
            'button_text' => ['label' => 'Кнопка: текст', 'type' => 'text', 'default' => 'Ознакомиться с программой'],
            'button_href' => ['label' => 'Кнопка: ссылка', 'type' => 'url', 'default' => '/programma/'],
            'button_icon' => ['label' => 'Кнопка: иконка', 'type' => 'icon', 'default' => 'book-open'],
            'button_icon_hover' => ['label' => 'Кнопка: иконка при наведении', 'type' => 'icon', 'default' => 'book-open-check'],
            'image_alt' => ['label' => 'Описание фона (alt)', 'type' => 'text', 'hint' => 'Для поисковиков и незрячих пользователей', 'default' => 'Уютный кабинет врача с цветами и зеленью'],
            'video_url' => ['label' => 'Видео: ссылка', 'type' => 'url', 'optional' => true, 'hint' => 'YouTube, Rutube, VK Видео или прямая ссылка на .mp4. Пусто — карточка видна, но клик ничего не открывает', 'default' => ''],
            'video_label' => ['label' => 'Видео: подпись', 'type' => 'text', 'optional' => true, 'default' => 'Видеознакомство с автором'],
            'video_author' => ['label' => 'Видео: имя автора', 'type' => 'text', 'default' => 'Дмитриенко Алексей Петрович'],
            'video_badge' => ['label' => 'Видео: метка', 'type' => 'text', 'optional' => true, 'default' => 'Бесплатное вступление'],
            'video_duration' => ['label' => 'Видео: длительность', 'type' => 'text', 'optional' => true, 'hint' => 'Например, «12 мин». Пусто — не показывается', 'default' => ''],
        ],
    ],
    'questions' => [
        'title' => 'Секция «Вопросы»',
        'fields' => [
            'eyebrow' => ['label' => 'Подпись над заголовком', 'type' => 'text', 'optional' => true, 'default' => 'Когда хочется разобраться'],
            'title' => ['label' => 'Заголовок H2: первая строка', 'type' => 'text', 'default' => 'Вопросы, с которыми'],
            'title_accent' => ['label' => 'Заголовок H2: вторая строка (розовая)', 'type' => 'text', 'optional' => true, 'default' => 'вы не одни'],
            'lead' => ['label' => 'Текст под заголовком', 'type' => 'textarea', 'optional' => true, 'default' => 'Материал для людей, проходящих восстановление после химиотерапии, и тех, кто рядом с ними.'],
            'cards' => [
                'label' => 'Карточки',
                'type' => 'list',
                'item' => 'карточку',
                'fields' => [
                    'title' => ['label' => 'Заголовок', 'type' => 'text'],
                    'text' => ['label' => 'Текст', 'type' => 'textarea', 'optional' => true],
                    'icon' => ['label' => 'Иконка', 'type' => 'icon'],
                    'icon_hover' => ['label' => 'Иконка при наведении', 'type' => 'icon'],
                ],
                'default' => [
                    ['title' => '«Почему я всё ещё так устаю?»', 'text' => 'Что врач рассказывает об усталости после лечения и какие вопросы можно подготовить к приёму.', 'icon' => 'battery-low', 'icon_hover' => 'battery-full'],
                    ['title' => '«Как возвращаться к привычной жизни?»', 'text' => 'Об активности, питании и повседневных изменениях в периоде восстановления.', 'icon' => 'sunrise', 'icon_hover' => 'sun'],
                    ['title' => '«Как близким быть рядом?»', 'text' => 'Как лучше понимать переживания пациента и обсуждать необходимую ему поддержку.', 'icon' => 'heart', 'icon_hover' => 'heart-handshake'],
                ],
            ],
        ],
    ],
    'footer' => [
        'title' => 'Подвал',
        'fields' => [
            'links' => [
                'label' => 'Ссылки',
                'type' => 'list',
                'item' => 'ссылку',
                'fields' => [
                    'label' => ['label' => 'Текст', 'type' => 'text'],
                    'href' => ['label' => 'Ссылка', 'type' => 'url'],
                ],
                'default' => [
                    ['label' => 'О нас', 'href' => '/o-nas/'],
                ],
            ],
            'copyright' => ['label' => 'Копирайт', 'type' => 'text', 'hint' => 'Выводится как «© текущий год ваш текст»', 'default' => 'Название сайта'],
        ],
    ],
];
