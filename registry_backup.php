<?php
/** Authenticated full registry JSON export/import. Files and passwords must be kept private. */
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
auth_require_json();
require_once __DIR__ . '/includes/registry_backup.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');
header('X-Content-Type-Options: nosniff');
try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        echo json_encode(['ok' => true, 'data' => registry_backup_export(__DIR__)], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 50 * 1024 * 1024) throw new RuntimeException('Архив больше 50 МБ');
        $body = file_get_contents('php://input', false, null, 0, 50 * 1024 * 1024 + 1);
        if ($body === false || strlen($body) > 50 * 1024 * 1024) throw new RuntimeException('Не удалось прочитать архив или его размер больше 50 МБ');
        $bundle = json_decode($body, false, 512, JSON_THROW_ON_ERROR);
        registry_backup_restore(__DIR__, $bundle);
        echo json_encode(['ok' => true, 'data' => ['files' => count(get_object_vars($bundle->files))]], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Метод не поддерживается'], JSON_UNESCAPED_UNICODE);
    }
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
