<?php
/**
 * Живые данные страницы участника (динамический режим, TEMPLATE_VERSION 3.1.0).
 *
 *   GET /landing_data.php?f={код}
 *
 * HTML-страница (шелл с токеном) сама запрашивает этот эндпоинт: сразу при
 * открытии, раз в 60 секунд и при возврате во вкладку (visibilitychange).
 * Обновляет заголовки, требования, поддержку, кнопки (подписи + индивидуальные
 * POPup-комментарии) и динамические стили кнопок (#lpDynamicBtnStyle).
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/storage.php';
require_once __DIR__ . '/includes/landing_template.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0, must-revalidate');
header('Pragma: no-cache');
header('X-Robots-Tag: noindex, nofollow');

const LD_LANDINGS_FILE = __DIR__ . '/data/landing_pages.json';
const LD_WEBINARS_FILE = __DIR__ . '/data/webinars.json';

function ld_out(array $payload): void {
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** Единый ответ для любого «нет данных»: неверный код, удалённый вебинар, битые данные. */
function ld_removed(): void {
    ld_out([
        'ok'   => true,
        'data' => [
            'status'    => 'removed',
            'pageTitle' => 'Мероприятие удалено',
            'message'   => 'Мероприятие удалено',
        ],
    ]);
}

// --- Токен: код файла страницы (как в URL .../2026/{код}.html) ---
$token = strtolower(trim((string)($_GET['f'] ?? '')));
$token = preg_replace('/\.html$/u', '', $token) ?? '';
$token = preg_replace('/[^a-z0-9_\-]/', '', $token) ?? '';
if ($token === '') ld_removed();

// --- Код → запись оформления страницы (файл {код}.html) ---
$landings = (array)json_read(LD_LANDINGS_FILE, new stdClass());
$landing = null;
$webinarId = '';
foreach ($landings as $id => $record) {
    $record = (array)$record;
    $generated = (string)($record['generatedFileName'] ?? '');
    if ($generated !== '' && pathinfo($generated, PATHINFO_FILENAME) === $token) {
        $landing = $record;
        $webinarId = (string)$id;
        break;
    }
}
if ($landing === null) ld_removed();

// --- Запись оформления → вебинар ---
$webinars = json_read(LD_WEBINARS_FILE, []);
[$webinarIndex, $webinar] = lp_find_webinar($webinars, $webinarId);
if ($webinarIndex === null || !is_array($webinar)) ld_removed();

// --- Контент страницы (те же функции, что у рендеров — форматы совпадают) ---
$data = array_merge($landing, lp_static_text_fields());
$buttons = array_merge(
    lp_build_buttons($webinar, $data),
    array_map(static function ($button) { $button = (array)$button; $button['isCustom'] = true; return $button; }, is_array($landing['customButtons'] ?? null) ? $landing['customButtons'] : [])
);
$isPlainSupport = ((string)($landing['themePreset'] ?? '')) === 'kodex';

ld_out([
    'ok'   => true,
    'data' => [
        'status'          => 'ok',
        'pageTitle'       => lp_page_title($webinar),
        'kickerHtml'      => 'ВЕБИНАР ' . lp_datetime_html($webinar, $data),
        'titleHtml'       => lp_title_html($webinar),
        'requirementHtml' => lp_requirement_html($data),
        'supportHtml'     => lp_support_html($data, $isPlainSupport),
        'buttonsHtml'     => lp_buttons_html($buttons, $data),
        'buttonCss'       => lp_dynamic_button_css($data),
    ],
]);
