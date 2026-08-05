<?php
/**
 * Загрузка фонового изображения / логотипа для страницы участника.
 * Сохраняет в uploads/images/ рядом с приложением. URL строится из текущего
 * запроса (без хардкода домена), поэтому переносим приложение — ссылки не ломаются.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
auth_require_json();

require_once __DIR__ . '/includes/storage.php';

header('Content-Type: application/json; charset=utf-8');

const UPLOAD_DIR = __DIR__ . '/uploads/images';
const MAX_UPLOAD_BYTES = 6 * 1024 * 1024; // 6 МБ
const ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

function respond_up(bool $ok, $data = null, string $error = ''): void {
    echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $error], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    respond_up(false, null, 'Метод не поддерживается');
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $err = $_FILES['file']['error'] ?? 'нет файла';
    respond_up(false, null, 'Ошибка загрузки файла (код: ' . $err . ')');
}

$tmpPath = $_FILES['file']['tmp_name'];
$origName = (string)$_FILES['file']['name'];
$size = (int)$_FILES['file']['size'];

if ($size <= 0 || $size > MAX_UPLOAD_BYTES) {
    respond_up(false, null, 'Файл слишком большой (максимум 6 МБ)');
}

$ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
if (!in_array($ext, ALLOWED_EXT, true)) {
    respond_up(false, null, 'Разрешены только изображения: jpg, png, webp, gif');
}

$imageInfo = @getimagesize($tmpPath);
if ($imageInfo === false) {
    respond_up(false, null, 'Файл не распознан как изображение');
}

if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0775, true) && !is_dir(UPLOAD_DIR)) {
    respond_up(false, null, 'Не удалось создать папку для загрузок');
}

$safeName = date('Ymd-His') . '-' . substr(bin2hex(random_bytes(4)), 0, 8) . '.' . $ext;
$destPath = UPLOAD_DIR . '/' . $safeName;

if (!move_uploaded_file($tmpPath, $destPath)) {
    respond_up(false, null, 'Не удалось сохранить файл на сервере');
}

$url = current_origin() . current_script_web_dir() . '/uploads/images/' . $safeName;

respond_up(true, ['url' => $url, 'fileName' => $safeName]);
