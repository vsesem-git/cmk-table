<?php
/** Versioned, allow-listed registry data backup. Authentication belongs to the endpoint. */
declare(strict_types=1);

const REGISTRY_BACKUP_FILES = [
    'data/webinars.json' => 'list',
    'data/landing_pages.json' => 'map',
    'data/settings.json' => 'map',
    'data/columns.json' => 'list',
    'data/organizers.json' => 'list',
    'data/speakers.json' => 'list',
    'data/mailing_lists.json' => 'list',
    'data/directions.json' => 'list',
    'data/published_on_site.json' => 'list',
    'data/subscription.json' => 'list',
    'data/in_subscription.json' => 'list',
    'data/doc_types.json' => 'list',
    'data/smtp.json' => 'map',
    'template.json' => 'map',
    'sub.json' => 'map',
];

function registry_backup_validate($bundle): array {
    if (!is_object($bundle) || ($bundle->format ?? '') !== 'cmk-registry-backup' || ($bundle->version ?? null) !== 1 || !is_object($bundle->files ?? null)) {
        throw new RuntimeException('Неверный формат или версия полного архива');
    }
    $files = get_object_vars($bundle->files);
    foreach (array_keys($files) as $name) {
        if (!isset(REGISTRY_BACKUP_FILES[$name])) throw new RuntimeException('Неизвестный файл в архиве: ' . $name);
    }
    foreach (['data/webinars.json', 'data/landing_pages.json', 'data/settings.json'] as $required) {
        if (!array_key_exists($required, $files)) throw new RuntimeException('В архиве отсутствует ' . $required);
    }
    foreach ($files as $name => $value) {
        $kind = REGISTRY_BACKUP_FILES[$name];
        if ($kind === 'list' && !is_array($value)) throw new RuntimeException('Ожидается массив: ' . $name);
        if ($kind === 'list') {
            foreach ($value as $entry) if (!is_object($entry)) throw new RuntimeException('Некорректная запись: ' . $name);
        }
        // PHP historically writes empty maps as [] in this application.
        if ($kind === 'map' && !is_object($value) && $value !== []) throw new RuntimeException('Ожидается объект: ' . $name);
    }
    $ids = [];
    foreach ($files['data/webinars.json'] as $row) {
        if (!is_object($row) || !isset($row->id) || !is_scalar($row->id) || !preg_match('/^[1-9][0-9]*$/', (string)$row->id)) {
            throw new RuntimeException('В архиве есть мероприятие с некорректным ID');
        }
        $id = (string)$row->id;
        if (isset($ids[$id])) throw new RuntimeException('Повторяющийся ID мероприятия: ' . $id);
        $ids[$id] = true;
    }
    foreach ($files['data/landing_pages.json'] as $id => $landing) {
        if (!is_object($landing)) throw new RuntimeException('Некорректное оформление страницы: ' . $id);
    }
    return $files;
}

function registry_backup_export(string $root): array {
    $files = [];
    foreach (REGISTRY_BACKUP_FILES as $name => $kind) {
        $path = $root . '/' . $name;
        if (!is_file($path)) continue;
        $raw = file_get_contents($path);
        if ($raw === false) throw new RuntimeException('Не удалось прочитать ' . $name);
        $value = json_decode($raw);
        if (json_last_error() !== JSON_ERROR_NONE) throw new RuntimeException('Некорректный JSON: ' . $name);
        $files[$name] = $value;
    }
    $bundle = ['format' => 'cmk-registry-backup', 'version' => 1, 'exportedAt' => date('c'), 'files' => $files];
    registry_backup_validate(json_decode(json_encode($bundle, JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR));
    return $bundle;
}

/** Validate everything before writing; keep a complete server-side pre-import snapshot and roll back on failure. */
function registry_backup_restore(string $root, $bundle): string {
    $files = registry_backup_validate($bundle);
    $backupDir = $root . '/data/backups/import-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4));
    if (!@mkdir($backupDir, 0700, true)) throw new RuntimeException('Не удалось создать резервную копию перед импортом');
    @file_put_contents($backupDir . '/.htaccess', "Require all denied\nDeny from all\n");
    $originals = [];
    $staged = [];
    try {
        foreach ($files as $name => $value) {
            $path = $root . '/' . $name;
            $originals[$name] = is_file($path);
            if ($originals[$name]) {
                $backup = $backupDir . '/' . str_replace('/', '__', $name);
                if (!@copy($path, $backup)) throw new RuntimeException('Не удалось сохранить копию ' . $name);
                @chmod($backup, 0600);
            }
            $temp = tempnam(dirname($path), '.import-');
            if ($temp === false) throw new RuntimeException('Не удалось подготовить ' . $name);
            $staged[$name] = $temp;
            $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
            if (@file_put_contents($temp, $encoded, LOCK_EX) !== strlen($encoded)) throw new RuntimeException('Не удалось подготовить ' . $name);
            @chmod($temp, 0664);
        }
        $written = [];
        try {
            foreach ($staged as $name => $temp) {
                if (!@rename($temp, $root . '/' . $name)) throw new RuntimeException('Не удалось восстановить ' . $name);
                $written[] = $name;
            }
        } catch (Throwable $e) {
            $failed = [];
            foreach (array_reverse($written) as $name) {
                $path = $root . '/' . $name;
                $ok = $originals[$name] ? @copy($backupDir . '/' . str_replace('/', '__', $name), $path) : @unlink($path);
                if (!$ok) $failed[] = $name;
            }
            if ($failed) throw new RuntimeException('Ошибка импорта и отката файлов: ' . implode(', ', $failed) . '. Резервная копия: ' . $backupDir);
            throw $e;
        }
    } finally {
        foreach ($staged as $temp) if (is_file($temp)) @unlink($temp);
    }
    return $backupDir;
}
