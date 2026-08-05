<?php
/**
 * Документы карточки вебинара: Официальное письмо (.doc/.docx),
 * Программа мероприятия (.docx), Приглашение на вебинар (.pdf).
 *
 * Файлы хранятся в отдельной папке на каждый вебинар:
 *   uploads/documents/{Дата - Спикер - Тема (до двоеточия)}/{тип документа}/{оригинальное имя файла}
 * Простое хранение готовых файлов — без генерации по шаблону. При повторной
 * загрузке того же типа старый файл уходит в бэкап и физически удаляется.
 *
 *   POST (multipart: webinarId, docType, file) — загрузить/заменить файл
 *   DELETE ?webinarId=N&docType=…             — убрать файл (без замены)
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
auth_require_json();

require_once __DIR__ . '/includes/storage.php';
require_once __DIR__ . '/includes/naming.php';

header('Content-Type: application/json; charset=utf-8');

const WD_WEBINARS_FILE = __DIR__ . '/data/webinars.json';
const WD_UPLOAD_ROOT    = __DIR__ . '/uploads/documents';
const WD_MAX_BYTES      = 20 * 1024 * 1024; // 20 МБ
const WD_DOC_TYPES_FILE = __DIR__ . '/data/doc_types.json';

/** Настроенные типы документов (data/doc_types.json), в виде key => [field,ext,label]. */
function wd_types(): array {
    $types = json_read(WD_DOC_TYPES_FILE, []);
    $out = [];
    foreach ($types as $t) {
        $out[$t['key']] = ['field' => 'doc_' . $t['key'], 'ext' => $t['ext'], 'label' => $t['label']];
    }
    return $out;
}

function respond_wd(bool $ok, $data = null, string $error = ''): void {
    echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $error], JSON_UNESCAPED_UNICODE);
    exit;
}

/** Безопасное оригинальное имя файла — только basename, без выхода за пределы папки. */
function wd_safe_original_name(string $name, string $fallbackExt): string {
    return safe_file_name($name, 'document.' . $fallbackExt);
}

/** По публичному URL, ранее выданному этим же скриптом, находит физический путь файла. */
function wd_path_from_url(string $url): ?string {
    $marker = '/uploads/documents/';
    $pos = strpos($url, $marker);
    if ($pos === false) return null;
    $relative = rawurldecode(substr($url, $pos + strlen($marker)));
    $parts = array_filter(explode('/', $relative), fn($p) => $p !== '' && $p !== '.' && $p !== '..');
    $safeParts = array_map('basename', $parts); // каждый сегмент отдельно — защита от "../"
    if (empty($safeParts)) return null;
    $full = WD_UPLOAD_ROOT . '/' . implode('/', $safeParts);
    return is_file($full) ? $full : null;
}

function wd_backup_and_delete(string $fullPath): void {
    $backupsDir = dirname($fullPath) . '/.backups';
    if (!is_dir($backupsDir)) {
        @mkdir($backupsDir, 0775, true);
        @file_put_contents($backupsDir . '/.htaccess', "Require all denied\nDeny from all\n");
    }
    make_file_backup($fullPath, $backupsDir, pathinfo($fullPath, PATHINFO_FILENAME), 5);
    @unlink($fullPath);
}

function wd_find_webinar_index(array $rows, string $id): ?int {
    foreach ($rows as $i => $r) {
        if ((string)($r['id'] ?? '') === $id) return $i;
    }
    return null;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    if ($method === 'GET' && $action === 'zip') {
        $webinarId = (string)($_GET['webinarId'] ?? '');
        if ($webinarId === '') { http_response_code(400); echo 'Не указан вебинар'; exit; }

        $rows = json_read(WD_WEBINARS_FILE, []);
        $idx = wd_find_webinar_index($rows, $webinarId);
        if ($idx === null) { http_response_code(404); echo 'Вебинар не найден'; exit; }
        $webinar = $rows[$idx];

        if (!class_exists('ZipArchive')) {
            http_response_code(500);
            echo 'На сервере не включено расширение PHP zip (ZipArchive) — включите php-zip и повторите.';
            exit;
        }

        $filesToZip = []; // [archiveNameInsideZip => fullDiskPath]
        $types = wd_types();
        foreach ($types as $docType => $def) {
            $url = (string)($webinar[$def['field']] ?? '');
            if ($url === '') continue;
            $path = wd_path_from_url($url);
            if ($path !== null) $filesToZip[basename($path)] = $path;
        }

        if (empty($filesToZip)) {
            http_response_code(404);
            echo 'У этого вебинара пока нет загруженных документов';
            exit;
        }

        $zipName = $webinarId . '. ' . webinar_slug_name($webinar) . '.zip';
        $tmpZipPath = sys_get_temp_dir() . '/webinar_docs_' . uniqid('', true) . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($tmpZipPath, ZipArchive::CREATE) !== true) {
            http_response_code(500);
            echo 'Не удалось создать архив';
            exit;
        }
        foreach ($filesToZip as $archiveName => $fullPath) {
            $zip->addFile($fullPath, $archiveName);
        }
        $zip->close();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . rawurlencode($zipName) . '"');
        header('Content-Length: ' . filesize($tmpZipPath));
        readfile($tmpZipPath);
        @unlink($tmpZipPath);
        exit;
    }

    if ($method === 'POST') {
        $types = wd_types();
        $webinarId = (string)($_POST['webinarId'] ?? '');
        $docType = (string)($_POST['docType'] ?? '');
        if ($webinarId === '' || !isset($types[$docType])) {
            respond_wd(false, null, 'Некорректный запрос (не указан вебинар или тип документа)');
        }
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            respond_wd(false, null, 'Не удалось загрузить файл');
        }

        $size = (int)$_FILES['file']['size'];
        if ($size <= 0 || $size > WD_MAX_BYTES) respond_wd(false, null, 'Файл слишком большой (максимум 20 МБ)');

        $origExt = strtolower(pathinfo((string)$_FILES['file']['name'], PATHINFO_EXTENSION));
        $allowed = $types[$docType]['ext'];
        if (!in_array($origExt, $allowed, true)) {
            respond_wd(false, null, 'Для «' . $types[$docType]['label'] . '» разрешены файлы: ' . implode(', ', $allowed));
        }

        $rows = json_read(WD_WEBINARS_FILE, []);
        $idx = wd_find_webinar_index($rows, $webinarId);
        if ($idx === null) respond_wd(false, null, 'Вебинар не найден в реестре');

        $field = $types[$docType]['field'];

        // Старый файл этого типа для этого вебинара — в бэкап и удалить
        // (путь берём из уже сохранённого URL — так надёжнее, даже если
        // дата/спикер/тема с тех пор поменялись и папка была бы другой).
        $existingUrl = (string)($rows[$idx][$field] ?? '');
        if ($existingUrl !== '') {
            $oldPath = wd_path_from_url($existingUrl);
            if ($oldPath !== null) wd_backup_and_delete($oldPath);
        }

        $folderName = webinar_slug_name($rows[$idx]);
        $targetDir = WD_UPLOAD_ROOT . '/' . $folderName . '/' . $docType;
        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            respond_wd(false, null, 'Не удалось создать папку для загрузки');
        }

        $safeOrigName = wd_safe_original_name((string)$_FILES['file']['name'], $origExt);
        $destPath = $targetDir . '/' . $safeOrigName;
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $destPath)) {
            respond_wd(false, null, 'Не удалось сохранить файл на сервере');
        }

        $publicUrl = current_origin() . current_script_web_dir() . '/uploads/documents/'
            . rawurlencode($folderName) . '/' . rawurlencode($docType) . '/' . rawurlencode($safeOrigName);
        $rows[$idx][$field] = $publicUrl;
        if (!json_write(WD_WEBINARS_FILE, $rows)) respond_wd(false, null, 'Не удалось обновить данные вебинара');

        respond_wd(true, ['url' => $publicUrl, 'field' => $field]);
    }

    if ($method === 'DELETE') {
        $types = wd_types();
        parse_str(file_get_contents('php://input'), $bodyParams);
        $webinarId = (string)($_GET['webinarId'] ?? $bodyParams['webinarId'] ?? '');
        $docType = (string)($_GET['docType'] ?? $bodyParams['docType'] ?? '');
        if ($webinarId === '' || !isset($types[$docType])) {
            respond_wd(false, null, 'Некорректный запрос');
        }

        $rows = json_read(WD_WEBINARS_FILE, []);
        $idx = wd_find_webinar_index($rows, $webinarId);
        if ($idx === null) respond_wd(false, null, 'Вебинар не найден в реестре');

        $field = $types[$docType]['field'];
        $existingUrl = (string)($rows[$idx][$field] ?? '');
        if ($existingUrl !== '') {
            $oldPath = wd_path_from_url($existingUrl);
            if ($oldPath !== null) wd_backup_and_delete($oldPath);
        }
        $rows[$idx][$field] = '';
        if (!json_write(WD_WEBINARS_FILE, $rows)) respond_wd(false, null, 'Не удалось обновить данные вебинара');

        respond_wd(true, ['field' => $field]);
    }

    http_response_code(405);
    respond_wd(false, null, 'Метод не поддерживается');
} catch (Throwable $e) {
    http_response_code(500);
    respond_wd(false, null, 'Ошибка сервера: ' . $e->getMessage());
}
