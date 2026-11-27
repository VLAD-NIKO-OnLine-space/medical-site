<?php
$c = content('terms');
$privacySections = array_column(content('privacy')['sections'], 'text', 'title');
$operatorDetails = $privacySections['ИНФОРМАЦИЯ ОБ ОПЕРАТОРЕ'] ?? '';
if ($operatorDetails !== '') {
    foreach ($c['sections'] as $index => $section) {
        if ($section['title'] === 'Реквизиты продавца') {
            $c['sections'][$index]['text'] = $operatorDetails;
            break;
        }
    }
}
require __DIR__ . '/../templates/doc.php';
