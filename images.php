<?php
/**
 * Библиотека уже загруженных изображений (uploads/images/) — чтобы не грузить
 * повторно один и тот же фон/лого. Только чтение: сама загрузка — через
 * landing_upload.php.
 *   GET — [{fileName, url, uploadedAt}, ...] новые сверху
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
auth_require_json();

require_once __DIR__ . '/includes/storage.php';

header('Content-Type: application/json; charset=utf-8');

const IMAGES_DIR = __DIR__ . '/uploads/images';
const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'data' => null, 'error' => 'Метод не поддерживается'], JSON_UNESCAPED_UNICODE);
    exit;
}

$items = [];
if (is_dir(IMAGES_DIR)) {
    foreach (scandir(IMAGES_DIR) as $file) {
        if ($file === '.' || $file === '..') continue;
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (!in_array($ext, IMAGE_EXTENSIONS, true)) continue;
        $fullPath = IMAGES_DIR . '/' . $file;
        $items[] = [
            'fileName'   => $file,
            'url'        => current_origin() . current_script_web_dir() . '/uploads/images/' . rawurlencode($file),
            'uploadedAt' => filemtime($fullPath),
        ];
    }
}
usort($items, fn($a, $b) => $b['uploadedAt'] <=> $a['uploadedAt']);

echo json_encode(['ok' => true, 'data' => $items, 'error' => ''], JSON_UNESCAPED_UNICODE);
