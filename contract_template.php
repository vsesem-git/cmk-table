<?php
/**
 * «Генерация для договоров» — работает с зоной Check.Мероприятия в
 * template.json (шаблон для генератора договоров / docx).
 *
 * Файл хранится прямо в корне приложения (template.json, рядом с index.php,
 * api.php и т.д.), поэтому у него всегда стабильный публичный адрес —
 * например https://ваш-домен/webinar-app/template.json — который можно
 * один раз прописать в вашей системе договоров.
 *
 * Ключи внутри Check.Мероприятия (например "8oL") — это реальные поля,
 * зашитые в вашем .docx-документе, мы их не изобретаем.
 *
 * При каждой генерации зона Check.Мероприятия полностью пересобирается
 * заново: все слоты сбрасываются в "Пусто", затем по порядку заполняются
 * выбранными вебинарами (отсортированными по дате). Так что каждая
 * генерация — это не добавление, а полная замена содержимого на текущий
 * выбор.
 *
 *   GET  ?action=status     — есть ли сохранённый файл, сколько слотов всего/свободно/занято
 *   GET  ?action=download   — скачать текущий template.json (для кнопки в интерфейсе;
 *                             сам файл и так публично доступен по прямой ссылке)
 *   POST (multipart, поле file) — загрузить/заменить template.json целиком
 *   POST ?action=generate   — {webinarIds:[...]} — пересобрать Check.Мероприятия
 *                             с нуля из выбранных вебинаров
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
auth_require_json();

require_once __DIR__ . '/includes/storage.php';
require_once __DIR__ . '/includes/landing_template.php'; // landing_format_date_long()

header('Content-Type: application/json; charset=utf-8');

const CONTRACT_TEMPLATE_FILE = __DIR__ . '/template.json';
const CONTRACT_WEBINARS_FILE = __DIR__ . '/data/webinars.json';
const CONTRACT_BACKUPS_DIR   = __DIR__ . '/data/backups';

function respond_ct(bool $ok, $data = null, string $error = ''): void {
    echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $error], JSON_UNESCAPED_UNICODE);
    exit;
}

function ct_read_template(): ?array {
    if (!is_file(CONTRACT_TEMPLATE_FILE)) return null;
    $raw = json_decode(file_get_contents(CONTRACT_TEMPLATE_FILE), true);
    return is_array($raw) && isset($raw['Check']['Мероприятия']) ? $raw : null;
}

/** Совпадает с логикой вашего editor.php: слот считается пустым, если
 *  name==="Пусто" ИЛИ value==="Тема" (там же именно так чистят слоты). */
function ct_is_empty_slot(array $entry): bool {
    return (($entry['name'] ?? '') === 'Пусто') || (($entry['value'] ?? '') === 'Тема');
}

/** Тема всегда в «кавычках» + «;» на конце. */
function ct_format_title(string $title): string {
    $t = trim($title);
    if (!(str_starts_with($t, '«') && str_ends_with($t, '»'))) {
        $t = '«' . trim($t, '«»" ') . '»';
    }
    return $t . ';';
}

function ct_status(array $template): array {
    $events = $template['Check']['Мероприятия'] ?? [];
    $empty = 0; $filled = 0;
    foreach ($events as $entry) {
        if (ct_is_empty_slot($entry)) $empty++; else $filled++;
    }
    return ['totalSlots' => count($events), 'emptySlots' => $empty, 'filledSlots' => $filled];
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    if ($method === 'GET' && $action === 'status') {
        $template = ct_read_template();
        if ($template === null) respond_ct(true, ['uploaded' => false]);
        respond_ct(true, array_merge(['uploaded' => true], ct_status($template)));
    }

    if ($method === 'GET' && $action === 'download') {
        $template = ct_read_template();
        if ($template === null) { http_response_code(404); echo 'Файл ещё не загружен'; exit; }
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="template.json"');
        echo json_encode($template, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    if ($method === 'POST' && $action === '') {
        // Загрузка/замена файла целиком
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            respond_ct(false, null, 'Не удалось загрузить файл');
        }
        $raw = file_get_contents($_FILES['file']['tmp_name']);
        $parsed = json_decode($raw, true);
        if (!is_array($parsed)) respond_ct(false, null, 'Файл не является корректным JSON');
        if (!isset($parsed['Check']['Мероприятия']) || !is_array($parsed['Check']['Мероприятия'])) {
            respond_ct(false, null, 'В файле не найдена зона Check → Мероприятия — проверьте, тот ли это template.json');
        }
        if (is_file(CONTRACT_TEMPLATE_FILE)) {
            make_file_backup(CONTRACT_TEMPLATE_FILE, CONTRACT_BACKUPS_DIR, 'contract-template', 5);
        }
        if (!json_write(CONTRACT_TEMPLATE_FILE, $parsed)) respond_ct(false, null, 'Не удалось сохранить файл на сервере');
        respond_ct(true, array_merge(['uploaded' => true], ct_status($parsed)));
    }

    if ($method === 'POST' && $action === 'generate') {
        $template = ct_read_template();
        if ($template === null) respond_ct(false, null, 'Сначала загрузите template.json в настройках');

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $webinarIds = array_map('strval', $input['webinarIds'] ?? []);
        if (empty($webinarIds)) respond_ct(false, null, 'Не выбрано ни одного вебинара');

        $allWebinars = json_read(CONTRACT_WEBINARS_FILE, []);
        $selected = array_values(array_filter($allWebinars, fn($w) => in_array((string)($w['id'] ?? ''), $webinarIds, true)));
        if (empty($selected)) respond_ct(false, null, 'Выбранные вебинары не найдены в реестре');
        usort($selected, fn($a, $b) => strcmp((string)($a['date'] ?? ''), (string)($b['date'] ?? '')));

        // Полная пересборка: сначала сбрасываем ВСЕ существующие слоты (сохраняя реальные ключи из docx),
        // затем по порядку заполняем выбранными вебинарами.
        $events = $template['Check']['Мероприятия'];
        $keys = array_keys($events);
        foreach ($keys as $key) {
            $events[$key] = ['type' => 'check', 'name' => 'Пусто', 'value' => 'Тема'];
        }

        $filledCount = 0;
        foreach ($selected as $w) {
            if ($filledCount >= count($keys)) break;
            $key = $keys[$filledCount];
            $name = landing_format_date_long((string)($w['date'] ?? '')) . ' ' . trim((string)($w['speaker'] ?? ''));
            $events[$key] = [
                'type'  => 'check',
                'name'  => $name,
                'value' => $name . ' ' . ct_format_title((string)($w['title'] ?? '')),
            ];
            $filledCount++;
        }

        $template['Check']['Мероприятия'] = $events;

        make_file_backup(CONTRACT_TEMPLATE_FILE, CONTRACT_BACKUPS_DIR, 'contract-template', 5);
        if (!json_write(CONTRACT_TEMPLATE_FILE, $template)) respond_ct(false, null, 'Не удалось сохранить обновлённый файл');

        respond_ct(true, [
            'filledCount'    => $filledCount,
            'skippedCount'   => count($selected) - $filledCount,
            'totalSlots'     => count($keys),
            'template'       => $template,
        ]);
    }

    http_response_code(405);
    respond_ct(false, null, 'Метод не поддерживается');
} catch (Throwable $e) {
    http_response_code(500);
    respond_ct(false, null, 'Ошибка сервера: ' . $e->getMessage());
}
