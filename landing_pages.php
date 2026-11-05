<?php
/**
 * Страницы для участников («Ссылка для участников»).
 *   GET  ?action=meta          — темы оформления + иконки кнопок + эталонные профили дизайна
 *   GET  ?webinarId=N          — черновик оформления страницы для этого вебинара
 *   POST                       — сохранить черновик оформления (тело: {webinarId, ...поля})
 *   POST ?action=publish       — собрать HTML, опубликовать на диск, подставить
 *                                 ссылку в data/webinars.json (поле link_participant)
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
auth_require_json();

require_once __DIR__ . '/includes/storage.php';
require_once __DIR__ . '/includes/landing_template.php';

header('Content-Type: application/json; charset=utf-8');

const LP_FILE           = __DIR__ . '/data/landing_pages.json';
const WEBINARS_FILE     = __DIR__ . '/data/webinars.json';
const SETTINGS_FILE_LP  = __DIR__ . '/data/settings.json';

function respond_lp(bool $ok, $data = null, string $error = ''): void {
    echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $error], JSON_UNESCAPED_UNICODE);
    exit;
}

function lp_clamp_int($value, int $min, int $max, int $fallback): int {
    $v = is_numeric($value) ? (int)$value : 0;
    if ($v <= 0 && $min > 0) $v = $fallback;
    return max($min, min($max, $v));
}

/** Кастомные кнопки, добавляемые пользователем вручную (идут после автоматических). */
function lp_sanitize_custom_buttons($in): array {
    $out = [];
    foreach ((is_array($in) ? $in : []) as $b) {
        $label = trim((string)($b['label'] ?? ''));
        if ($label === '') continue;
        $icon = trim((string)($b['icon'] ?? 'link'));
        if (!array_key_exists($icon, BUTTON_ICON_CLASSES)) $icon = 'link';
        $out[] = [
            'isCustom'        => true,
            'label'           => $label,
            'url'             => trim((string)($b['url'] ?? '')) ?: '#',
            'icon'            => $icon,
            'commentEnabled'  => !empty($b['commentEnabled']),
            'commentOnActive' => !empty($b['commentOnActive']),
            'commentText'     => trim((string)($b['commentText'] ?? '')),
        ];
    }
    return $out;
}

/** Только полностраничный дизайн (тема/шрифты/фон/интенсивность/ширина заголовка) + кастомные кнопки. */
function lp_sanitize_design(array $in): array {
    $theme = trim((string)($in['themePreset'] ?? 'wood'));
    if (!array_key_exists($theme, THEME_PRESETS)) $theme = 'wood';

    $time = trim((string)($in['eventTime'] ?? '10:00'));
    if (!preg_match('/^\d{2}:\d{2}$/', $time)) $time = '10:00';

    return [
        'eventTime'            => $time,
        'themePreset'          => $theme,
        'dateFontPx'           => lp_clamp_int($in['dateFontPx'] ?? null, 12, 72, 54),
        'titleFontPx'          => lp_clamp_int($in['titleFontPx'] ?? null, 14, 96, 46),
        'metaFontPx'           => lp_clamp_int($in['metaFontPx'] ?? null, 12, 48, 17),
        'buttonFontPx'         => lp_clamp_int($in['buttonFontPx'] ?? null, 12, 40, 16),
        'footerFontPx'         => lp_clamp_int($in['footerFontPx'] ?? null, 10, 30, 14),
        'backgroundImage'      => trim((string)($in['backgroundImage'] ?? '')),
        'backgroundIntensity'  => lp_clamp_int($in['backgroundIntensity'] ?? 100, 0, 100, 100),
        'titleWidthPct'        => lp_clamp_int($in['titleWidthPct'] ?? null, 100, 200, 100),
        'customButtons'        => lp_sanitize_custom_buttons($in['customButtons'] ?? []),
    ];
}

function lp_generate_file_id(string $targetDir, int $length = 6): string {
    $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
    for ($attempt = 0; $attempt < 20; $attempt++) {
        $id = '';
        for ($i = 0; $i < $length; $i++) $id .= $chars[random_int(0, strlen($chars) - 1)];
        if (!is_file(rtrim($targetDir, '/') . '/' . $id . '.html')) return $id;
    }
    return substr(bin2hex(random_bytes($length)), 0, $length);
}

function lp_backup_if_exists(string $fullPath, string $targetDir, string $fileName): void {
    if (!is_file($fullPath)) return;
    $backupsDir = $targetDir . '/.backups';
    if (!is_dir($backupsDir)) {
        @mkdir($backupsDir, 0775, true);
        @file_put_contents($backupsDir . '/.htaccess', "Require all denied\nDeny from all\n");
    }
    make_file_backup($fullPath, $backupsDir, pathinfo($fileName, PATHINFO_FILENAME), 5);
}

// ---------------------------------------------------------------------------

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    if ($method === 'GET' && $action === 'meta') {
        $themes = [];
        foreach (THEME_PRESETS as $key => $t) {
            $themes[] = [
                'key'          => $key,
                'label'        => $t['label'],
                'swatch'       => $t['buttonStart'],
                'buttonStart'  => $t['buttonStart'],
                'buttonEnd'    => $t['buttonEnd'],
                'hoverStart'   => $t['hoverStart'],
                'hoverEnd'     => $t['hoverEnd'],
                'overlayStart' => $t['overlayStart'],
                'overlayEnd'   => $t['overlayEnd'],
                'textColor'    => $t['textColor'],
                'baseImage'    => $t['baseImage'],
            ];
        }
        $icons = [];
        foreach (BUTTON_ICON_CLASSES as $key => $info) {
            $icons[] = ['key' => $key, 'label' => $info['label'], 'class' => $info['class']];
        }
        respond_lp(true, [
            'themes'                => $themes,
            'icons'                 => $icons,
            'canonicalButtons'      => CANONICAL_BUTTONS,
            'defaultDesignProfiles' => lp_default_design_profiles(),
        ]);
    }

    if ($method === 'GET') {
        $webinarId = (string)($_GET['webinarId'] ?? '');
        if ($webinarId === '') respond_lp(false, null, 'Не указан webinarId');
        $all = (array)json_read(LP_FILE, new stdClass());
        respond_lp(true, $all[$webinarId] ?? null);
    }

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $webinarId = (string)($input['webinarId'] ?? '');
        if ($webinarId === '') respond_lp(false, null, 'Не указан webinarId');

        $clean = lp_sanitize_design($input);

        $all = (array)json_read(LP_FILE, new stdClass());
        $existing = $all[$webinarId] ?? [];
        $clean['generatedFileName'] = $existing['generatedFileName'] ?? '';
        $clean['publicUrl']         = $existing['publicUrl'] ?? '';
        $clean['generatedAt']       = $existing['generatedAt'] ?? '';
        $clean['updatedAt']         = date('c');

        if ($action !== 'publish') {
            $all[$webinarId] = $clean;
            json_write(LP_FILE, $all);
            respond_lp(true, $clean);
        }

        // ---- Публикация ----
        $webinars = json_read(WEBINARS_FILE, []);
        [$webinarIndex, $webinar] = lp_find_webinar($webinars, $webinarId);
        if ($webinarIndex === null) respond_lp(false, null, 'Вебинар не найден в реестре');

        $documentRoot = realpath((string)($_SERVER['DOCUMENT_ROOT'] ?? ''));
        if ($documentRoot === false) respond_lp(false, null, 'Не удалось определить корень сайта (DOCUMENT_ROOT)');

        $settings = (array)json_read(SETTINGS_FILE_LP, []);
        [$targetDir, $relativeOutput] = lp_resolve_output_dir(
            $documentRoot,
            trim((string)($settings['landingOutputBase'] ?? ''), '/'),
            (string)($webinar['date'] ?? '')
        );
        if ($targetDir === null) respond_lp(false, null, 'Небезопасный путь публикации');

        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            respond_lp(false, null, 'Не удалось создать папку публикации: ' . $targetDir);
        }

        $forceNewName = !empty($input['regenerateFileName']);
        $oldFileName = $clean['generatedFileName'];
        $fileName = $oldFileName;
        if ($fileName === '' || $forceNewName || !is_file($targetDir . '/' . $fileName)) {
            $linkLength = (int)($settings['landingLinkLength'] ?? 4);
            if ($linkLength < 4 || $linkLength > 32) {
                $linkLength = 4;
            }
            $fileName = lp_generate_file_id($targetDir, $linkLength) . '.html';
        }
        $fullPath = rtrim($targetDir, '/') . '/' . $fileName;

        lp_backup_if_exists($fullPath, $targetDir, $fileName);

        $renderData = array_merge($clean, lp_static_text_fields());
        $renderData['buttons'] = array_merge(lp_build_buttons($webinar, $renderData), $clean['customButtons']);

        $html = render_landing_html($webinar, $renderData, $fileName);
        if (@file_put_contents($fullPath, $html, LOCK_EX) === false) {
            respond_lp(false, null, 'Не удалось записать файл: ' . $fullPath);
        }

        if ($forceNewName && $oldFileName !== '' && $oldFileName !== $fileName) {
            $oldFullPath = rtrim($targetDir, '/') . '/' . $oldFileName;
            if (is_file($oldFullPath)) {
                lp_backup_if_exists($oldFullPath, $targetDir, $oldFileName);
                @unlink($oldFullPath);
            }
        }

        $publicUrl = current_origin() . '/' . ($relativeOutput !== '' ? $relativeOutput . '/' : '') . $fileName;

        $clean['generatedFileName'] = $fileName;
        $clean['publicUrl'] = $publicUrl;
        $clean['generatedAt'] = date('c');
        $all[$webinarId] = $clean;
        json_write(LP_FILE, $all);

        $webinars[$webinarIndex]['link_participant'] = $publicUrl;
        json_write(WEBINARS_FILE, $webinars);

        respond_lp(true, ['publicUrl' => $publicUrl, 'fileName' => $fileName, 'landing' => $clean, 'buttons' => $renderData['buttons']]);
    }

    http_response_code(405);
    respond_lp(false, null, 'Метод не поддерживается');
} catch (Throwable $e) {
    http_response_code(500);
    respond_lp(false, null, 'Ошибка сервера: ' . $e->getMessage());
}
