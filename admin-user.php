<?php
// Создать администратора или сменить ему пароль: php admin-user.php <логин> [пароль]
// Без пароля сгенерирует случайный и выведет его.
require __DIR__ . '/lib.php';

if (PHP_SAPI !== 'cli') {
    exit;
}
$login = $argv[1] ?? '';
if ($login === '') {
    fwrite(STDERR, "Использование: php admin-user.php <логин> [пароль]\n");
    exit(1);
}
$password = $argv[2] ?? rtrim(strtr(base64_encode(random_bytes(12)), '+/', 'Kq'), '=');
if (strlen($password) < 8) {
    fwrite(STDERR, "Пароль должен быть не короче 8 символов.\n");
    exit(1);
}
db()->prepare('INSERT INTO admins (login, password_hash) VALUES (?, ?)
    ON CONFLICT(login) DO UPDATE SET password_hash = excluded.password_hash')
    ->execute([$login, password_hash($password, PASSWORD_DEFAULT)]);
echo "Готово. Логин: $login" . (isset($argv[2]) ? '' : ", пароль: $password") . "\n";
