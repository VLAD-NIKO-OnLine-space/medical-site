<?php
$c = content('privacy');
$structuredDocument = true;
if ($c['sections']) {
    $page['scripts'][] = '/assets/js/doc-toc.js';
}
require __DIR__ . '/../templates/doc.php';
