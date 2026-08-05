<?php
/**
 * «Генерация подписчикам» — собирает в sub.json (корень приложения, стабильный
 * публичный URL — тот же принцип, что и у template.json) все вебинары, у
 * которых «Направление» входит в выбранные категории И «Входит в подписку»
 * = «Да». Записываются все поля вебинара целиком. Выбор автоматический —
 * без ручного отмечания чекбоксом.
 *
 *   GET  ?action=status   — сколько сейчас вебинаров в файле, когда собирали
 *   POST ?action=generate — {categories:[...]} — полностью пересобрать sub.json
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
auth_require_json();

require_once __DIR__ . '/includes/storage.php';

header('Content-Type: application/json; charset=utf-8');

const SUB_FILE = __DIR__ . '/sub.json';
const SUB_WEBINARS_FILE = __DIR__ . '/data/webinars.json';
const SUB_BACKUPS_DIR = __DIR__ . '/data/backups';
const SUB_QUALIFYING_VALUE = 'Да';

function respond_sub(bool $ok, $data = null, string $error = ''): void {
    echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $error], JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    if ($method === 'GET' && $action === 'status') {
        $exists = is_file(SUB_FILE);
        $count = 0;
        $generatedAt = null;
        if ($exists) {
            $data = json_decode(file_get_contents(SUB_FILE), true);
            $count = is_array($data['webinars'] ?? null) ? count($data['webinars']) : 0;
            $generatedAt = $data['generatedAt'] ?? null;
        }
        respond_sub(true, ['exists' => $exists, 'count' => $count, 'generatedAt' => $generatedAt]);
    }

    if ($method === 'POST' && $action === 'generate') {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $categories = array_values(array_filter(array_map('trim', (array)($input['categories'] ?? []))));
        if (empty($categories)) respond_sub(false, null, 'Выберите хотя бы одну категорию (Направление)');

        $rows = json_read(SUB_WEBINARS_FILE, []);
        $matching = array_values(array_filter($rows, function ($r) use ($categories) {
            $direction = (string)($r['direction'] ?? '');
            $subscription = (string)($r['subscription'] ?? '');
            return $direction !== '' && in_array($direction, $categories, true) && $subscription === SUB_QUALIFYING_VALUE;
        }));

        $payload = [
            'generatedAt' => date('c'),
            'categories'  => $categories,
            'count'       => count($matching),
            'webinars'    => $matching,
        ];

        if (is_file(SUB_FILE)) {
            make_file_backup(SUB_FILE, SUB_BACKUPS_DIR, 'sub', 5);
        }
        if (!json_write(SUB_FILE, $payload)) respond_sub(false, null, 'Не удалось сохранить sub.json');

        respond_sub(true, $payload);
    }

    http_response_code(405);
    respond_sub(false, null, 'Метод не поддерживается');
} catch (Throwable $e) {
    http_response_code(500);
    respond_sub(false, null, 'Ошибка сервера: ' . $e->getMessage());
}
