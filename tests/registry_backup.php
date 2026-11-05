<?php
// Run with: php tests/registry_backup.php
declare(strict_types=1);
require_once __DIR__ . '/../includes/registry_backup.php';
function backup_check(bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); }
$root = sys_get_temp_dir() . '/cmk-backup-test-' . bin2hex(random_bytes(5));
mkdir($root . '/data', 0775, true);
try {
    file_put_contents($root . '/data/webinars.json', '[{"id":7,"title":"старый"}]');
    file_put_contents($root . '/data/landing_pages.json', '{"7":{"generatedFileName":"abc.html"}}');
    file_put_contents($root . '/data/settings.json', '{"activeYearFilter":"all"}');
    file_put_contents($root . '/data/users.json', '[{"username":"local"}]');
    file_put_contents($root . '/data/smtp.json', '{"password":"secret"}');
    $bundle = json_decode(json_encode(registry_backup_export($root), JSON_THROW_ON_ERROR));
    backup_check(!isset($bundle->files->{'data/users.json'}), 'Учётные записи исключены');
    backup_check($bundle->files->{'data/smtp.json'}->password === 'secret', 'SMTP включён');
    file_put_contents($root . '/data/webinars.json', '[{"id":42}]');
    $backupDir = registry_backup_restore($root, $bundle);
    $restored = json_decode(file_get_contents($root . '/data/webinars.json'));
    backup_check($restored[0]->id === 7, 'ID мероприятия сохранён');
    backup_check(json_decode(file_get_contents($root . '/data/landing_pages.json'))->{'7'}->generatedFileName === 'abc.html', 'Код страницы сохранён');
    backup_check(strpos(file_get_contents($backupDir . '/data__webinars.json'), '42') !== false, 'Старая версия сохранена');
    backup_check(strpos(file_get_contents($root . '/data/users.json'), 'local') !== false, 'Учётная запись не заменена');
    $bundle->files->{'../../outside.json'} = [];
    try { registry_backup_restore($root, $bundle); throw new RuntimeException('Некорректный архив принят'); }
    catch (RuntimeException $e) { backup_check(strpos($e->getMessage(), 'Неизвестный файл') !== false, 'Запрещены посторонние пути'); }
    unset($bundle->files->{'../../outside.json'});
    $bundle->files->{'data/webinars.json'}[] = (object)['id' => 7];
    try { registry_backup_restore($root, $bundle); throw new RuntimeException('Дубликат принят'); }
    catch (RuntimeException $e) { backup_check(strpos($e->getMessage(), 'Повторяющийся ID') !== false, 'Дубликаты ID отклоняются'); }
    backup_check(json_decode(file_get_contents($root . '/data/webinars.json'))[0]->id === 7, 'Ошибка проверки не меняет данные');
    echo "Registry backup tests passed\n";
} finally {
    $remove = static function ($dir) use (&$remove): void {
        foreach (scandir($dir) as $name) { if ($name === '.' || $name === '..') continue; $path = $dir . '/' . $name; if (is_dir($path)) $remove($path); else unlink($path); }
        rmdir($dir);
    };
    $remove($root);
}
