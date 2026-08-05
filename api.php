<?php
/**
 * ЦМК — Реестр вебинаров
 * JSON-CRUD API + импорт/экспорт. Ответ: {"ok": true/false, "data": ..., "error": "..."}
 *
 * Методы:
 *   GET                       — список всех вебинаров
 *   POST                      — создать вебинар (делает бэкап data/webinars.json перед записью)
 *   PUT                       — обновить вебинар по id
 *   DELETE ?id=N              — удалить вебинар
 *   POST ?action=import       — заменить весь набор данных (тело: JSON-массив вебинаров).
 *                                Тоже делает бэкап перед заменой.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
auth_require_json();

require_once __DIR__ . '/includes/storage.php';

header('Content-Type: application/json; charset=utf-8');

const DATA_FILE    = __DIR__ . '/data/webinars.json';
const COLUMNS_FILE = __DIR__ . '/data/columns.json';
const BACKUP_DIR   = __DIR__ . '/data/backups';
const BACKUP_KEEP  = 5;

const BASE_FIELDS = [
    'price', 'organizer', 'date', 'speaker', 'title',
    'link_participant', 'link_host', 'link_materials', 'link_recording', 'moderator_code',
    'published_on_site', 'mailing_list', 'direction', 'subscription',
];

const DOC_TYPES_FILE_API = __DIR__ . '/data/doc_types.json';

function custom_field_keys(): array {
    $cols = json_read(COLUMNS_FILE, []);
    return array_map(fn($c) => $c['key'], $cols);
}

/** Поля doc_{key} — по одному на каждый настроенный тип документа (data/doc_types.json). */
function doc_field_keys(): array {
    $types = json_read(DOC_TYPES_FILE_API, []);
    return array_map(fn($t) => 'doc_' . $t['key'], $types);
}

function all_fields(): array {
    return array_merge(BASE_FIELDS, custom_field_keys(), doc_field_keys());
}

function next_id(array $rows): int {
    $max = 0;
    foreach ($rows as $r) $max = max($max, (int)($r['id'] ?? 0));
    return $max + 1;
}

function respond(bool $ok, $data = null, string $error = ''): void {
    echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $error], JSON_UNESCAPED_UNICODE);
    exit;
}

/** Тема всегда хранится в кавычках «…» — снимаем прежние кавычки и оборачиваем заново. */
/** Убирает лишние пробелы/табы и схлопывает пустые строки, не трогая настоящие переносы строк. */
function clean_title_whitespace(string $s): string {
    $s = str_replace(["\r\n", "\r"], "\n", $s);
    $s = str_replace("\t", ' ', $s);
    $lines = array_map(function ($line) {
        $line = preg_replace('/[ \x{00A0}]+/u', ' ', $line);
        return trim($line);
    }, explode("\n", $s));
    $s = implode("\n", $lines);
    $s = preg_replace('/\n{3,}/', "\n\n", $s); // не более одной пустой строки подряд
    return trim($s, " \n");
}

/** Снимает кавычки любых видов по краям строки (может быть несколько слоёв: «"текст"» → текст). */
function strip_wrapping_quotes(string $s): string {
    $quoteClass = '«»‹›“”‘’„"\'`';
    do {
        $before = $s;
        $s = preg_replace('/^[' . preg_quote($quoteClass, '/') . '\s]+/u', '', $s);
        $s = preg_replace('/[' . preg_quote($quoteClass, '/') . '\s]+$/u', '', $s);
    } while ($s !== $before && $s !== '');
    return $s;
}

function wrap_title(string $s): string {
    $s = clean_title_whitespace($s);
    if ($s === '') return '';
    $s = strip_wrapping_quotes($s);
    return $s === '' ? '' : '«' . $s . '»';
}

/** Обязательные поля: дата, цена, кто проводит, спикер, тема. */
function validate_required(array $in): string {
    if (trim((string)($in['date'] ?? '')) === '') return 'Поле «Дата» обязательно';
    if (!isset($in['price']) || !is_numeric($in['price']) || (float)$in['price'] <= 0) return 'Поле «Цена» обязательно и должно быть больше нуля';
    if (trim((string)($in['organizer'] ?? '')) === '') return 'Поле «Организатор» обязательно';
    if (trim((string)($in['speaker'] ?? '')) === '') return 'Поле «Спикер» обязательно';
    if (trim((string)($in['title'] ?? '')) === '') return 'Поле «Вебинар» обязательно';
    return '';
}

function sanitize_input(array $in): array {
    $out = [];
    foreach (all_fields() as $f) {
        $v = $in[$f] ?? '';
        if ($f === 'price') {
            $out[$f] = is_numeric($v) ? (int)$v : 0;
        } elseif ($f === 'title') {
            $out[$f] = wrap_title((string)$v);
        } else {
            $out[$f] = trim((string)$v);
        }
    }
    return $out;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    if ($method === 'POST' && $action === 'import') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) respond(false, null, 'Файл должен содержать JSON-массив вебинаров');

        make_backup(DATA_FILE, BACKUP_DIR, BACKUP_KEEP);

        $rows = [];
        $usedIds = [];
        foreach ($input as $row) {
            if (!is_array($row)) continue;
            $clean = sanitize_input($row);
            $id = (int)($row['id'] ?? 0);
            if ($id <= 0 || in_array($id, $usedIds, true)) {
                $id = ($usedIds ? max($usedIds) : 0) + 1;
            }
            $usedIds[] = $id;
            $clean['id'] = $id;
            $rows[] = $clean;
        }
        if (!json_write(DATA_FILE, $rows)) respond(false, null, 'Не удалось сохранить файл данных');
        respond(true, $rows);
    }

    switch ($method) {
        case 'GET':
            respond(true, json_read(DATA_FILE, []));
            break;

        case 'POST': {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            if ($err = validate_required($input)) respond(false, null, $err);
            make_backup(DATA_FILE, BACKUP_DIR, BACKUP_KEEP);

            $rows = json_read(DATA_FILE, []);
            $row = sanitize_input($input);
            $row['id'] = next_id($rows);
            $rows[] = $row;
            if (!json_write(DATA_FILE, $rows)) respond(false, null, 'Не удалось сохранить файл данных');
            respond(true, $row);
            break;
        }

        case 'PUT': {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $id = (int)($input['id'] ?? 0);
            if ($id <= 0) respond(false, null, 'Некорректный id');
            if ($err = validate_required($input)) respond(false, null, $err);
            $rows = json_read(DATA_FILE, []);
            $found = false;
            foreach ($rows as &$r) {
                if ((int)$r['id'] === $id) {
                    $updated = sanitize_input($input);
                    $updated['id'] = $id;
                    $r = $updated;
                    $found = true;
                    break;
                }
            }
            unset($r);
            if (!$found) respond(false, null, 'Запись не найдена');
            if (!json_write(DATA_FILE, $rows)) respond(false, null, 'Не удалось сохранить файл данных');
            respond(true, ['id' => $id]);
            break;
        }

        case 'DELETE': {
            parse_str(file_get_contents('php://input'), $input);
            $id = (int)($_GET['id'] ?? $input['id'] ?? 0);
            if ($id <= 0) respond(false, null, 'Некорректный id');
            $rows = json_read(DATA_FILE, []);
            $newRows = array_values(array_filter($rows, fn($r) => (int)$r['id'] !== $id));
            if (count($newRows) === count($rows)) respond(false, null, 'Запись не найдена');
            if (!json_write(DATA_FILE, $newRows)) respond(false, null, 'Не удалось сохранить файл данных');
            respond(true, ['id' => $id]);
            break;
        }

        default:
            http_response_code(405);
            respond(false, null, 'Метод не поддерживается');
    }
} catch (Throwable $e) {
    http_response_code(500);
    respond(false, null, 'Ошибка сервера: ' . $e->getMessage());
}
