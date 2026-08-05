<?php
/**
 * Единый backend справочников «имя + цвет» — Организатор, Рассылка,
 * Направление, Размещён на сайте, Входит в подписку, Спикер и любые
 * будущие похожие поля. Новый справочник — это одна запись в TAXONOMY_DEFS,
 * без нового кода.
 *
 *   GET  ?type=X                      — список [{name,color}, ...]
 *   POST ?type=X {name,color?}        — добавить (если существует — вернуть его)
 *   POST ?type=X&action=rename {oldName,newName,color?} — переименовать,
 *        каскадно обновив это значение у всех вебинаров, где оно уже используется
 *   DELETE ?type=X&name=…             — удалить из справочника
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
auth_require_json();

require_once __DIR__ . '/includes/storage.php';

header('Content-Type: application/json; charset=utf-8');

const TAXONOMY_WEBINARS_FILE = __DIR__ . '/data/webinars.json';

const TAXONOMY_DEFS = [
    'organizer' => [
        'file' => __DIR__ . '/data/organizers.json',
        'palette' => ['#7A9E9F', '#B5638A', '#4E7CC2', '#8A6BBE', '#C2703D', '#5FA777'],
        'field' => 'organizer', 'multi' => false,
    ],
    'mailing_list' => [
        'file' => __DIR__ . '/data/mailing_lists.json',
        'palette' => ['#7A9E9F', '#B5638A', '#4E7CC2', '#8A6BBE', '#C2703D', '#5FA777'],
        'field' => 'mailing_list', 'multi' => true,
    ],
    'direction' => [
        'file' => __DIR__ . '/data/directions.json',
        'palette' => ['#1E6FA8', '#A8285A', '#AA6A0F', '#2E7D32', '#5D4037', '#3A26B5', '#0A5E5E', '#B5638A'],
        'field' => 'direction', 'multi' => false,
    ],
    'published_on_site' => [
        'file' => __DIR__ . '/data/published_on_site.json',
        'palette' => ['#1F7A3D', '#A6392B', '#7A9E9F', '#8A6BBE'],
        'field' => 'published_on_site', 'multi' => false,
    ],
    'subscription' => [
        'file' => __DIR__ . '/data/subscription.json',
        'palette' => ['#1F7A3D', '#A6392B', '#7A9E9F', '#8A6BBE'],
        'field' => 'subscription', 'multi' => false,
    ],
    'speaker' => [
        'file' => __DIR__ . '/data/speakers.json',
        'palette' => ['#8A97A6'],
        'field' => 'speaker', 'multi' => false,
    ],
    // Чтобы добавить новый справочник такого же вида — достаточно новой
    // записи здесь (и .json-файла в data/) — новый PHP-код не нужен.
];

function respond_tx(bool $ok, $data = null, string $error = ''): void {
    echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $error], JSON_UNESCAPED_UNICODE);
    exit;
}

$type = (string)($_GET['type'] ?? '');
if (!isset(TAXONOMY_DEFS[$type])) {
    respond_tx(false, null, 'Неизвестный справочник: ' . $type);
}
$def = TAXONOMY_DEFS[$type];
$file = $def['file'];
$palette = $def['palette'];

/** Каскадно переименовывает значение $old в $new во всех вебинарах, использующих это поле. */
function taxonomy_cascade_rename(array $def, string $old, string $new): void {
    if ($old === $new) return;
    $rows = json_read(TAXONOMY_WEBINARS_FILE, []);
    $field = $def['field'];
    $changed = false;
    foreach ($rows as &$r) {
        if (!array_key_exists($field, $r)) continue;
        if ($def['multi']) {
            $parts = array_map('trim', explode(',', (string)$r[$field]));
            $newParts = array_map(fn($p) => $p === $old ? $new : $p, $parts);
            $newVal = implode(',', array_filter($newParts, fn($p) => $p !== ''));
            if ($newVal !== $r[$field]) { $r[$field] = $newVal; $changed = true; }
        } else {
            if ($r[$field] === $old) { $r[$field] = $new; $changed = true; }
        }
    }
    unset($r);
    if ($changed) json_write(TAXONOMY_WEBINARS_FILE, $rows);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            respond_tx(true, json_read($file, []));
            break;

        case 'POST': {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];

            if ($action === 'rename') {
                $oldName = trim((string)($input['oldName'] ?? ''));
                $newName = trim((string)($input['newName'] ?? ''));
                if ($oldName === '' || $newName === '') respond_tx(false, null, 'Укажите старое и новое название');

                $list = json_read($file, []);
                $found = false;
                foreach ($list as &$item) {
                    if ($item['name'] === $oldName) {
                        $item['name'] = $newName;
                        if (!empty($input['color']) && preg_match('/^#[0-9a-fA-F]{6}$/', $input['color'])) {
                            $item['color'] = $input['color'];
                        }
                        $found = true;
                        break;
                    }
                }
                unset($item);
                if (!$found) respond_tx(false, null, 'Значение не найдено');
                if (!json_write($file, $list)) respond_tx(false, null, 'Не удалось сохранить');

                taxonomy_cascade_rename($def, $oldName, $newName);
                respond_tx(true, ['name' => $newName]);
            }

            $name = trim((string)($input['name'] ?? ''));
            if ($name === '') respond_tx(false, null, 'Укажите название');

            $lower = function_exists('mb_strtolower') ? fn($s) => mb_strtolower($s, 'UTF-8') : fn($s) => strtolower($s);
            $list = json_read($file, []);
            foreach ($list as $item) {
                if ($lower($item['name']) === $lower($name)) respond_tx(true, $item);
            }

            $color = trim((string)($input['color'] ?? ''));
            if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
                $color = $palette[count($list) % count($palette)];
            }
            $entry = ['name' => $name, 'color' => $color];
            $list[] = $entry;
            if (!json_write($file, $list)) respond_tx(false, null, 'Не удалось сохранить');
            respond_tx(true, $entry);
            break;
        }

        case 'DELETE': {
            parse_str(file_get_contents('php://input'), $input);
            $name = (string)($_GET['name'] ?? $input['name'] ?? '');
            if ($name === '') respond_tx(false, null, 'Не указано название');
            $list = json_read($file, []);
            $newList = array_values(array_filter($list, fn($i) => $i['name'] !== $name));
            if (count($newList) === count($list)) respond_tx(false, null, 'Не найдено');
            json_write($file, $newList);
            respond_tx(true, ['name' => $name]);
            break;
        }

        default:
            http_response_code(405);
            respond_tx(false, null, 'Метод не поддерживается');
    }
} catch (Throwable $e) {
    http_response_code(500);
    respond_tx(false, null, 'Ошибка сервера: ' . $e->getMessage());
}
