<?php
/**
 * Управление пользовательскими столбцами (data/columns.json).
 *   GET            — список столбцов [{key,label,type}, ...]
 *   POST {label,type?} — добавить столбец, key генерируется автоматически.
 *                        type: 'text' (по умолчанию) | 'boolean' (Да/Нет) | 'tags' (несколько значений)
 *   DELETE ?key=…  — удалить столбец (и подчистить это поле во всех строках)
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
auth_require_json();

require_once __DIR__ . '/includes/storage.php';

header('Content-Type: application/json; charset=utf-8');

const COLUMNS_FILE_C = __DIR__ . '/data/columns.json';
const DATA_FILE_C    = __DIR__ . '/data/webinars.json';
const CUSTOM_COLUMN_TYPES = ['text', 'boolean', 'tags'];

function respond_c(bool $ok, $data = null, string $error = ''): void {
    echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $error], JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            respond_c(true, json_read(COLUMNS_FILE_C, []));
            break;

        case 'POST': {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $label = trim((string)($input['label'] ?? ''));
            if ($label === '') respond_c(false, null, 'Укажите название столбца');
            $type = (string)($input['type'] ?? 'text');
            if (!in_array($type, CUSTOM_COLUMN_TYPES, true)) $type = 'text';

            $cols = json_read(COLUMNS_FILE_C, []);
            if (count($cols) >= 20) respond_c(false, null, 'Слишком много дополнительных столбцов');

            $key = 'col_' . substr(md5(uniqid((string)mt_rand(), true)), 0, 8);
            $cols[] = ['key' => $key, 'label' => $label, 'type' => $type];
            if (!json_write(COLUMNS_FILE_C, $cols)) respond_c(false, null, 'Не удалось сохранить столбцы');
            respond_c(true, ['key' => $key, 'label' => $label, 'type' => $type]);
            break;
        }

        case 'DELETE': {
            parse_str(file_get_contents('php://input'), $input);
            $key = (string)($_GET['key'] ?? $input['key'] ?? '');
            if ($key === '') respond_c(false, null, 'Не указан ключ столбца');

            $cols = json_read(COLUMNS_FILE_C, []);
            $newCols = array_values(array_filter($cols, fn($c) => $c['key'] !== $key));
            if (count($newCols) === count($cols)) respond_c(false, null, 'Столбец не найден');
            json_write(COLUMNS_FILE_C, $newCols);

            // Подчищаем это поле из всех строк, чтобы не оставался мусор.
            $rows = json_read(DATA_FILE_C, []);
            foreach ($rows as &$r) unset($r[$key]);
            unset($r);
            json_write(DATA_FILE_C, $rows);

            respond_c(true, ['key' => $key]);
            break;
        }

        default:
            http_response_code(405);
            respond_c(false, null, 'Метод не поддерживается');
    }
} catch (Throwable $e) {
    http_response_code(500);
    respond_c(false, null, 'Ошибка сервера: ' . $e->getMessage());
}
