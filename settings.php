<?php
/**
 * Общие настройки отображения (data/settings.json) — общие для всех, кто открывает
 * реестр, а не персональные для браузера.
 *   GET   — текущие настройки
 *   POST  — сохранить настройки целиком (тело: тот же объект, что вернул GET)
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
auth_require_json();

require_once __DIR__ . '/includes/storage.php';
require_once __DIR__ . '/includes/landing_template.php';

header('Content-Type: application/json; charset=utf-8');

const SETTINGS_FILE = __DIR__ . '/data/settings.json';
const REGISTRY_WIDTH_PRESETS = ['1200', '1400', '1600', '1800', '2000', 'full'];

const SEND_EXPORT_FIELD_KEYS = [
    'date', 'price', 'organizer', 'speaker', 'title',
    'link_participant', 'link_host', 'link_materials', 'link_recording', 'moderator_code',
    'doc_official_letter', 'doc_program', 'doc_invitation',
    'published_on_site', 'mailing_list', 'direction',
];

function default_settings(): array {
    return [
        'hidden' => [], 'linkAsText' => [], 'colWidths' => new stdClass(), 'tableWidthMode' => 'scroll',
        'landingOutputBase' => '',
        'landingPortsText' => 'Откройте порты TCP:443 UDP:16384 - 32768',
        'landingSupportText' => 'Техническая поддержка',
        'landingSupportPhone' => '+7 910 154 76 86',
        'landingSupportSchedule' => '(гарантируется только в день вебинара с 10:00 до 15:00)',
        'landingBrowserText' => 'Используйте браузеры Yandex Browser / Google Chrome / Firefox последней версии.',
        'landingFooterText' => '© 2026 Страница создана по материалам вебинара',
        'registryWidth' => '1400',
        'quickLinks' => [],
        'quickLinksOrder' => [],
        'mailRecipients' => [],
        'sendExportFields' => ['date', 'link_participant', 'link_host', 'moderator_code'],
        'columnOrder' => [],
        'columnLabels' => new stdClass(),
        'activeYearFilter' => '',
        'activeFilters' => new stdClass(),
        'landingDefaultTemplate' => [
            'themePreset' => 'wood',
            'dateFontPx' => 54, 'titleFontPx' => 46, 'metaFontPx' => 17, 'buttonFontPx' => 16, 'footerFontPx' => 14,
            'backgroundImage' => '', 'backgroundIntensity' => 100, 'titleWidthPct' => 100,
        ],
    ];
}

function respond_s(bool $ok, $data = null, string $error = ''): void {
    echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $error], JSON_UNESCAPED_UNICODE);
    exit;
}

function settings_clamp_int($value, int $min, int $max, int $fallback): int {
    $v = is_numeric($value) ? (int)$value : 0;
    if ($v <= 0) $v = $fallback;
    return max($min, min($max, $v));
}

function settings_clean_default_template($in): array {
    $in = is_array($in) ? $in : [];
    $theme = trim((string)($in['themePreset'] ?? 'wood'));
    if (!array_key_exists($theme, THEME_PRESETS)) $theme = 'wood';
    return [
        'themePreset'         => $theme,
        'dateFontPx'          => settings_clamp_int($in['dateFontPx'] ?? null, 12, 72, 54),
        'titleFontPx'         => settings_clamp_int($in['titleFontPx'] ?? null, 14, 96, 46),
        'metaFontPx'          => settings_clamp_int($in['metaFontPx'] ?? null, 12, 48, 17),
        'buttonFontPx'        => settings_clamp_int($in['buttonFontPx'] ?? null, 12, 40, 16),
        'footerFontPx'        => settings_clamp_int($in['footerFontPx'] ?? null, 10, 30, 14),
        'backgroundImage'     => trim((string)($in['backgroundImage'] ?? '')),
        'backgroundIntensity' => settings_clamp_int($in['backgroundIntensity'] ?? null, 0, 100, 100),
        'titleWidthPct'       => settings_clamp_int($in['titleWidthPct'] ?? null, 100, 200, 100),
    ];
}

function settings_clean_mail_recipients($in): array {
    $out = [];
    foreach ((is_array($in) ? $in : []) as $r) {
        $email = trim((string)($r['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) continue;
        $out[] = ['email' => $email, 'label' => trim((string)($r['label'] ?? ''))];
        if (count($out) >= 30) break;
    }
    return $out;
}

function settings_clean_quick_links($in): array {
    $out = [];
    foreach ((is_array($in) ? $in : []) as $l) {
        $label = trim((string)($l['label'] ?? ''));
        if ($label === '') continue;
        $out[] = [
            'id'    => trim((string)($l['id'] ?? '')) ?: ('ql_' . substr(bin2hex(random_bytes(4)), 0, 8)),
            'label' => $label,
            'url'   => trim((string)($l['url'] ?? '')) ?: '#',
            'icon'  => trim((string)($l['icon'] ?? 'fa-solid fa-link')),
        ];
        if (count($out) >= 6) break;
    }
    return $out;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            respond_s(true, json_read(SETTINGS_FILE, default_settings()));
            break;

        case 'POST': {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input)) respond_s(false, null, 'Некорректный формат настроек');

            $registryWidth = (string)($input['registryWidth'] ?? '1400');
            if (!in_array($registryWidth, REGISTRY_WIDTH_PRESETS, true)) $registryWidth = '1400';

            $columnLabelsIn = is_array($input['columnLabels'] ?? null) ? $input['columnLabels'] : [];
            $columnLabels = [];
            foreach ($columnLabelsIn as $key => $label) {
                $label = trim((string)$label);
                if ($label !== '') $columnLabels[(string)$key] = $label;
            }

            $activeFiltersIn = is_array($input['activeFilters'] ?? null) ? $input['activeFilters'] : [];
            $activeFilters = [];
            foreach ($activeFiltersIn as $col => $vals) {
                if (is_array($vals)) $activeFilters[(string)$col] = array_values(array_map('strval', $vals));
            }

            $clean = [
                'hidden'         => array_values(array_map('strval', $input['hidden'] ?? [])),
                'linkAsText'     => array_values(array_map('strval', $input['linkAsText'] ?? [])),
                'colWidths'      => is_array($input['colWidths'] ?? null) ? $input['colWidths'] : [],
                'tableWidthMode' => in_array($input['tableWidthMode'] ?? '', ['scroll', 'fit'], true) ? $input['tableWidthMode'] : 'scroll',
                'landingOutputBase' => trim((string)($input['landingOutputBase'] ?? ''), '/'),
                'landingPortsText' => trim((string)($input['landingPortsText'] ?? '')) ?: 'Откройте порты TCP:443 UDP:16384 - 32768',
                'landingSupportText' => trim((string)($input['landingSupportText'] ?? '')) ?: 'Техническая поддержка',
                'landingSupportPhone' => trim((string)($input['landingSupportPhone'] ?? '')) ?: '+7 910 154 76 86',
                'landingSupportSchedule' => trim((string)($input['landingSupportSchedule'] ?? '')) ?: '(гарантируется только в день вебинара с 10:00 до 15:00)',
                'landingBrowserText' => trim((string)($input['landingBrowserText'] ?? '')),
                'landingFooterText' => trim((string)($input['landingFooterText'] ?? '')),
                'registryWidth'  => $registryWidth,
                'quickLinks'     => settings_clean_quick_links($input['quickLinks'] ?? []),
                'quickLinksOrder' => array_values(array_map('strval', $input['quickLinksOrder'] ?? [])),
                'mailRecipients' => settings_clean_mail_recipients($input['mailRecipients'] ?? []),
                'sendExportFields' => array_values(array_intersect(
                    array_map('strval', $input['sendExportFields'] ?? []),
                    SEND_EXPORT_FIELD_KEYS
                )) ?: ['date', 'link_participant', 'link_host', 'moderator_code'],
                'columnOrder'    => array_values(array_map('strval', $input['columnOrder'] ?? [])),
                'columnLabels'   => $columnLabels,
                'activeYearFilter' => trim((string)($input['activeYearFilter'] ?? '')),
                'activeFilters'  => $activeFilters,
                'landingDefaultTemplate' => settings_clean_default_template($input['landingDefaultTemplate'] ?? []),
            ];
            if (!json_write(SETTINGS_FILE, $clean)) respond_s(false, null, 'Не удалось сохранить настройки');
            respond_s(true, $clean);
            break;
        }

        default:
            http_response_code(405);
            respond_s(false, null, 'Метод не поддерживается');
    }
} catch (Throwable $e) {
    http_response_code(500);
    respond_s(false, null, 'Ошибка сервера: ' . $e->getMessage());
}

