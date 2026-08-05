<?php
/**
 * Настраиваемые типы документов карточки вебинара (сейчас: Официальное
 * письмо, Программа, Приглашение — но список расширяемый без правки кода).
 * Каждый тип соответствует полю вебинара doc_{key}.
 *
 *   GET             — список [{key,label,ext,icon}, ...]
 *   POST {label,ext,icon?} — добавить новый тип (ключ генерируется из label)
 *   DELETE ?key=…   — удалить тип (поле убирается из всех вебинаров)
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
auth_require_json();

require_once __DIR__ . '/includes/storage.php';

header('Content-Type: application/json; charset=utf-8');

const DOC_TYPES_FILE = __DIR__ . '/data/doc_types.json';
const DOC_TYPES_WEBINARS_FILE = __DIR__ . '/data/webinars.json';
const DOC_TYPE_ICONS = ['fa-solid fa-file-word', 'fa-solid fa-file-pdf', 'fa-solid fa-file-lines', 'fa-solid fa-file-image', 'fa-solid fa-file-excel', 'fa-solid fa-file-zipper'];

function respond_dt(bool $ok, $data = null, string $error = ''): void {
    echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $error], JSON_UNESCAPED_UNICODE);
    exit;
}

function dt_slugify(string $label): string {
    $map = [
        'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh','з'=>'z','и'=>'i','й'=>'y',
        'к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f',
        'х'=>'h','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'sch','ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya',
    ];
    $s = mb_strtolower($label, 'UTF-8');
    $s = strtr($s, $map);
    $s = preg_replace('/[^a-z0-9]+/', '_', $s) ?? $s;
    $s = trim($s, '_');
    return $s !== '' ? $s : ('doc_' . substr(bin2hex(random_bytes(3)), 0, 6));
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            respond_dt(true, json_read(DOC_TYPES_FILE, []));
            break;

        case 'POST': {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            $label = trim((string)($input['label'] ?? ''));
            if ($label === '') respond_dt(false, null, 'Укажите название типа документа');

            $ext = array_values(array_filter(array_map(
                fn($e) => strtolower(trim((string)$e, ". \t")),
                (array)($input['ext'] ?? [])
            )));
            if (empty($ext)) respond_dt(false, null, 'Укажите хотя бы одно допустимое расширение файла');

            $icon = trim((string)($input['icon'] ?? '')) ?: 'fa-solid fa-file-lines';

            $types = json_read(DOC_TYPES_FILE, []);
            if (count($types) >= 15) respond_dt(false, null, 'Слишком много типов документов');

            $baseKey = dt_slugify($label);
            $key = $baseKey;
            $i = 2;
            while (array_filter($types, fn($t) => $t['key'] === $key)) {
                $key = $baseKey . '_' . $i;
                $i++;
            }

            $entry = ['key' => $key, 'label' => $label, 'ext' => $ext, 'icon' => $icon];
            $types[] = $entry;
            if (!json_write(DOC_TYPES_FILE, $types)) respond_dt(false, null, 'Не удалось сохранить');
            respond_dt(true, $entry);
            break;
        }

        case 'DELETE': {
            parse_str(file_get_contents('php://input'), $input);
            $key = (string)($_GET['key'] ?? $input['key'] ?? '');
            if ($key === '') respond_dt(false, null, 'Не указан тип документа');

            $types = json_read(DOC_TYPES_FILE, []);
            $newTypes = array_values(array_filter($types, fn($t) => $t['key'] !== $key));
            if (count($newTypes) === count($types)) respond_dt(false, null, 'Тип не найден');
            json_write(DOC_TYPES_FILE, $newTypes);

            // Подчищаем поле doc_{key} из всех вебинаров (сами файлы на диске не трогаем).
            $field = 'doc_' . $key;
            $rows = json_read(DOC_TYPES_WEBINARS_FILE, []);
            foreach ($rows as &$r) unset($r[$field]);
            unset($r);
            json_write(DOC_TYPES_WEBINARS_FILE, $rows);

            respond_dt(true, ['key' => $key]);
            break;
        }

        default:
            http_response_code(405);
            respond_dt(false, null, 'Метод не поддерживается');
    }
} catch (Throwable $e) {
    http_response_code(500);
    respond_dt(false, null, 'Ошибка сервера: ' . $e->getMessage());
}
