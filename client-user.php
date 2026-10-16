<?php
// Клиент (покупатель) из командной строки — пока нет оплаты, клиентов заводят так:
//   php client-user.php <логин> <пароль> [имя] [почта]
//   php client-user.php <логин> <пароль> [имя] [почта] --close   — закрыть доступ (например, после возврата денег)
// Доступ бессрочный. Повторный вызов с тем же логином меняет пароль, имя и почту.
require __DIR__ . '/lib.php';

$args = array_values(array_filter(array_slice($argv, 1), fn($a) => $a !== '--close'));
$close = in_array('--close', $argv, true);
if (count($args) < 2) {
    fwrite(STDERR, "php client-user.php <логин> <пароль> [имя] [почта] [--close]\n");
    exit(1);
}
[$login, $password] = $args;
client_upsert($login, $password, $args[2] ?? $login, $args[3] ?? '', $close);
@chown(__DIR__ . '/data/site.sqlite', 'www-data');
echo "Клиент «{$login}» сохранён, доступ " . ($close ? 'закрыт' : 'бессрочный') . ".\n";
