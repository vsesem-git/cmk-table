<?php
/**
 * Общие функции чтения/записи JSON-файлов с блокировкой.
 * Используются api.php и columns.php, чтобы не дублировать логику.
 */

declare(strict_types=1);

function json_read(string $path, $default = []) {
    if (!file_exists($path)) return $default;
    $fh = fopen($path, 'r');
    if (!$fh) return $default;
    flock($fh, LOCK_SH);
    $raw = stream_get_contents($fh);
    flock($fh, LOCK_UN);
    fclose($fh);
    $data = json_decode($raw ?: 'null', true);
    return $data === null ? $default : $data;
}

function json_write(string $path, $data): bool {
    $fh = fopen($path, 'c+');
    if (!$fh) return false;
    flock($fh, LOCK_EX);
    ftruncate($fh, 0);
    rewind($fh);
    $ok = fwrite($fh, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) !== false;
    fflush($fh);
    flock($fh, LOCK_UN);
    fclose($fh);
    return $ok;
}

/**
 * Копирует произвольный файл в $backupDir с меткой времени и префиксом,
 * оставляя не более $keep последних копий с этим префиксом.
 * Используется и для data/webinars.json, и для опубликованных HTML-страниц.
 */
function make_file_backup(string $filePath, string $backupDir, string $prefix, int $keep = 5): void {
    if (!file_exists($filePath)) return;
    if (!is_dir($backupDir)) @mkdir($backupDir, 0775, true);
    $ext = pathinfo($filePath, PATHINFO_EXTENSION) ?: 'bak';
    $stamp = date('Y-m-d_His');
    $dest = rtrim($backupDir, '/') . "/{$prefix}-{$stamp}.{$ext}";
    @copy($filePath, $dest);

    $files = glob(rtrim($backupDir, '/') . "/{$prefix}-*.{$ext}") ?: [];
    usort($files, fn($a, $b) => filemtime($b) <=> filemtime($a));
    foreach (array_slice($files, $keep) as $old) {
        @unlink($old);
    }
}

/**
 * Схема + хост текущего запроса — без единого захардкоженного домена,
 * чтобы всё приложение переносилось на любой домен/поддомен без правок кода.
 */
function current_origin(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') === '443' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
    return $scheme . '://' . $host;
}

/** Веб-путь директории, в которой физически лежит текущий PHP-скрипт (без домена). */
function current_script_web_dir(): string {
    return rtrim(str_replace('\\', '/', dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/'))), '/');
}

/**
 * Копирует текущий файл данных в data/backups/ с меткой времени
 * и оставляет не более $keep последних бэкапов.
 */
function make_backup(string $dataFile, string $backupDir, int $keep = 5): void {
    make_file_backup($dataFile, $backupDir, 'webinars', $keep);
}
