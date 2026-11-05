<?php
/**
 * Рендер статической HTML-страницы для участников вебинара.
 * Дизайн — точный перенос присланного шаблона (тема/фон/оверлей/кнопки/шрифты),
 * адаптированный на сервер (PHP heredoc вместо JS-строки).
 * Домен нигде не хардкодится — все абсолютные ссылки уже посчитаны вызывающим кодом.
 */

declare(strict_types=1);

const TEMPLATE_VERSION = '3.1.0';

const THEME_PRESETS = [
    // Дизайн «КОДЕКС» — стили эталонной страницы (Jost, display-2, кнопки #40b0bf).
    // Рендерится отдельной функцией render_landing_html_kodex() с полным редактированием
    // (шрифты/ширина/фон/интенсивность — из конструктора, как у классических тем).
    'kodex' => [
        'label' => 'КОДЕКС',
        'overlayStart' => 'rgba(0, 0, 0, 0)', 'overlayEnd' => 'rgba(0, 0, 0, 0)',
        'buttonStart' => '#40b0bf', 'buttonEnd' => '#40b0bf',
        'hoverStart' => '#2a747e', 'hoverEnd' => '#2a747e',
        'textColor' => '#ffffff',
        'baseImage' => 'https://edu.vsesem.ru/2026/assets/images/mbr-1-1920x1276.jpg',
    ],
    'wood' => [
        'label' => 'Дерево (нейтральный)',
        'overlayStart' => 'rgba(20, 21, 23, 0.6)', 'overlayEnd' => 'rgba(20, 21, 23, 0.68)',
        'buttonStart' => '#33acc2', 'buttonEnd' => '#2f92b6',
        'hoverStart' => '#39bbd2', 'hoverEnd' => '#319ec3',
        'textColor' => '#f6f8fb',
        'baseImage' => 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?auto=format&fit=crop&w=1900&q=80',
    ],
    'agro' => [
        'label' => 'Агро / сельское хозяйство',
        'overlayStart' => 'rgba(19, 34, 24, 0.62)', 'overlayEnd' => 'rgba(19, 34, 24, 0.7)',
        'buttonStart' => '#3da868', 'buttonEnd' => '#327d52',
        'hoverStart' => '#48b874', 'hoverEnd' => '#38895b',
        'textColor' => '#f7fff8',
        'baseImage' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1900&q=80',
    ],
    'zhkh' => [
        'label' => 'ЖКХ',
        'overlayStart' => 'rgba(17, 55, 83, 0.66)', 'overlayEnd' => 'rgba(17, 55, 83, 0.74)',
        'buttonStart' => '#1E6FA8', 'buttonEnd' => '#175b89',
        'hoverStart' => '#2a7db8', 'hoverEnd' => '#1c689e',
        'textColor' => '#f3f9ff',
        'baseImage' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1900&q=80',
    ],
    'zdrav' => [
        'label' => 'Здравоохранение',
        'overlayStart' => 'rgba(88, 17, 44, 0.68)', 'overlayEnd' => 'rgba(88, 17, 44, 0.76)',
        'buttonStart' => '#A8285A', 'buttonEnd' => '#8e214d',
        'hoverStart' => '#b63567', 'hoverEnd' => '#9b2653',
        'textColor' => '#fff3f8',
        'baseImage' => 'https://images.unsplash.com/photo-1586773860418-d37222d8fce3?auto=format&fit=crop&w=1900&q=80',
    ],
    'energy' => [
        'label' => 'Энергетика',
        'overlayStart' => 'rgba(74, 45, 9, 0.68)', 'overlayEnd' => 'rgba(74, 45, 9, 0.76)',
        'buttonStart' => '#AA6A0F', 'buttonEnd' => '#8a550d',
        'hoverStart' => '#bc7815', 'hoverEnd' => '#9a610f',
        'textColor' => '#fff8ee',
        'baseImage' => 'https://images.unsplash.com/photo-1473341304170-971dccb5ac1e?auto=format&fit=crop&w=1900&q=80',
    ],
    'eco' => [
        'label' => 'Экология',
        'overlayStart' => 'rgba(26, 64, 28, 0.66)', 'overlayEnd' => 'rgba(26, 64, 28, 0.74)',
        'buttonStart' => '#2E7D32', 'buttonEnd' => '#256729',
        'hoverStart' => '#3a8f3f', 'hoverEnd' => '#2d7430',
        'textColor' => '#f1fff2',
        'baseImage' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1900&q=80',
    ],
    'build' => [
        'label' => 'Строительство',
        'overlayStart' => 'rgba(54, 35, 30, 0.68)', 'overlayEnd' => 'rgba(54, 35, 30, 0.76)',
        'buttonStart' => '#5D4037', 'buttonEnd' => '#4c342d',
        'hoverStart' => '#6f4b40', 'hoverEnd' => '#5a3d34',
        'textColor' => '#fff6f2',
        'baseImage' => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=1900&q=80',
    ],
    'goz' => [
        'label' => 'Гособоронзаказ',
        'overlayStart' => 'rgba(35, 24, 98, 0.68)', 'overlayEnd' => 'rgba(35, 24, 98, 0.76)',
        'buttonStart' => '#3A26B5', 'buttonEnd' => '#2f1f96',
        'hoverStart' => '#4a34c6', 'hoverEnd' => '#3824ad',
        'textColor' => '#f2efff',
        'baseImage' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=1900&q=80',
    ],
];

const BUTTON_ICON_CLASSES = [
    'download' => ['class' => 'fa-solid fa-download',     'label' => '⬇️ Скачать файл'],
    'play'     => ['class' => 'fa-solid fa-circle-play',  'label' => '▶️ Смотреть онлайн'],
    'video'    => ['class' => 'fa-solid fa-video',        'label' => '🎥 Скачать запись трансляции'],
    'file'     => ['class' => 'fa-solid fa-file-lines',   'label' => '📄 Скачать материалы вебинара'],
    'folder'   => ['class' => 'fa-solid fa-folder-open',  'label' => '🗂️ Открыть материалы'],
    'link'     => ['class' => 'fa-solid fa-link',         'label' => '🔗 Перейти по ссылке'],
];

function landing_escape(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function landing_nl2br(string $s): string {
    return nl2br(landing_escape($s));
}

function landing_clamp(int $value, int $min, int $max, int $fallback): int {
    if ($value <= 0 && $min > 0) $value = $fallback;
    return max($min, min($max, $value));
}

function landing_phone_href(string $phone): string {
    $clean = preg_replace('/[^\d+]/', '', $phone);
    return $clean !== '' ? 'tel:' . $clean : 'tel:+79101547686';
}

/** Уменьшает альфа-канал rgba(...)-строки темы пропорционально интенсивности (0-100%). */
function landing_scale_rgba_alpha(string $rgba, int $intensityPct): string {
    if (!preg_match('/^rgba\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*,\s*([\d.]+)\s*\)$/', trim($rgba), $m)) {
        return $rgba;
    }
    $alpha = max(0.0, min(1.0, (float)$m[4] * ($intensityPct / 100)));
    return sprintf('rgba(%d, %d, %d, %s)', (int)$m[1], (int)$m[2], (int)$m[3], rtrim(rtrim(number_format($alpha, 3, '.', ''), '0'), '.') ?: '0');
}

/** "dd.mm.yyyy hh:mm" из даты вебинара (Y-m-d) + времени начала (H:i) черновика страницы. */
function landing_format_webinar_datetime(string $date, string $time): string {
    $time = $time !== '' ? $time : '10:00';
    $ts = strtotime($date . ' ' . $time);
    if (!$ts) return $date;
    return date('d.m.Y H:i', $ts);
}

/** "01 января 2026 г." — для <title> вкладки браузера. */
function landing_format_date_long(string $date): string {
    static $months = ['января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'];
    $ts = strtotime($date);
    if (!$ts) return $date;
    return sprintf('%02d %s %d г.', (int)date('d', $ts), $months[(int)date('n', $ts) - 1], (int)date('Y', $ts));
}

const TECH_REQUIREMENTS_FAQ_URL = 'https://vsesem.ru/bbb/vsesem_faq.html';

// ---------------------------------------------------------------------------
// Общий доменный слой страниц участников и профилей дизайна (ЦМК / КОДЕКС).
// Используется рендером (render_landing_html*), публикацией (landing_pages.php),
// настройками (settings.php) и живым API данных (landing_data.php).
// ---------------------------------------------------------------------------

/** Базовые слоты автоматических кнопок страницы участника. */
const CANONICAL_BUTTONS = [
    ['field' => 'link_host',       'label' => 'Смотреть онлайн',              'icon' => 'play'],
    ['field' => 'link_recording',  'label' => 'Скачать запись трансляции',    'icon' => 'video'],
    ['field' => 'link_materials',  'label' => 'Скачать материалы вебинара',   'icon' => 'file'],
];

const DEFAULT_COMMENT_HOST      = 'Ссылка на подключение появится перед началом вебинара.';
const DEFAULT_COMMENT_RECORDING = 'Организатор еще не разместил запись трансляции. Обычно это занимает от 2 до 5 дней. Обновите страницу Ctrl+F5.';
const DEFAULT_COMMENT_MATERIALS = 'Организатор еще не разместил материалы вебинара. Обновите страницу Ctrl+F5.';

function lp_sanitize_hex_color($val, string $fallback): string {
    $s = strtolower(trim((string)$val));
    if (preg_match('/^#([0-9a-f]{6})$/', $s)) {
        return $s;
    }
    if (preg_match('/^#([0-9a-f])([0-9a-f])([0-9a-f])$/', $s, $m)) {
        return '#' . $m[1] . $m[1] . $m[2] . $m[2] . $m[3] . $m[3];
    }
    return strtolower($fallback);
}

/** Сдвигает яркость HEX-цвета на $percent (-100..100) для расчёта hover-оттенка. */
function lp_shade_hex(string $hex, int $percent): string {
    $hex = lp_sanitize_hex_color($hex, '#33acc2');
    $r = hexdec(substr($hex, 1, 2));
    $g = hexdec(substr($hex, 3, 2));
    $b = hexdec(substr($hex, 5, 2));
    $factor = (100 + $percent) / 100.0;
    $r = max(0, min(255, (int)round($r * $factor)));
    $g = max(0, min(255, (int)round($g * $factor)));
    $b = max(0, min(255, (int)round($b * $factor)));
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}

/** Вычисляет CSS-строку box-shadow по ползунку интенсивности тени (0..100). */
function lp_shadow_css(int $intensity, bool $isHover = false): string {
    $intensity = max(0, min(100, $intensity));
    if ($intensity <= 0) {
        return 'none';
    }
    $k = $intensity / 60.0;
    if ($isHover) {
        $y = max(2, (int)round(16 * $k));
        $blur = max(4, (int)round(30 * $k));
        $alpha = min(0.62, max(0.12, round(0.16 + 0.30 * ($intensity / 100.0), 2)));
    } else {
        $y = max(1, (int)round(12 * $k));
        $blur = max(2, (int)round(24 * $k));
        $alpha = min(0.55, max(0.08, round(0.10 + 0.30 * ($intensity / 100.0), 2)));
    }
    return sprintf('0 %dpx %dpx rgba(0, 0, 0, %s)', $y, $blur, number_format($alpha, 2, '.', ''));
}

/** Эталонные профили дизайна кнопок и комментариев для шаблонов ЦМК и КОДЕКС. */
function lp_default_design_profiles(?array $legacySettings = null): array {
    $legacyRec = trim((string)($legacySettings['landingTooltipRecording'] ?? ''));
    $legacyMat = trim((string)($legacySettings['landingTooltipMaterials'] ?? ''));
    if ($legacyRec === '') $legacyRec = DEFAULT_COMMENT_RECORDING;
    if ($legacyMat === '') $legacyMat = DEFAULT_COMMENT_MATERIALS;

    return [
        'cmk' => [
            'useThemeActiveBg'  => true,
            'activeBgColor'     => '#33acc2',
            'activeTextColor'   => '#ffffff',
            'inactiveBgColor'   => '#7a7a7a',
            'inactiveTextColor' => '#ffffff',
            'inactiveCursor'    => 'default',
            'borderRadiusPx'    => 8,
            'shadowIntensity'   => 60,
            'shadowOnInactive'  => false,
            'showIcons'         => true,
            'buttons'           => [
                'link_host' => [
                    'label'           => 'Смотреть онлайн',
                    'showWhenEmpty'   => false,
                    'commentEnabled'  => false,
                    'commentOnActive' => false,
                    'commentText'     => DEFAULT_COMMENT_HOST,
                ],
                'link_recording' => [
                    'label'           => 'Скачать запись трансляции',
                    'showWhenEmpty'   => true,
                    'commentEnabled'  => true,
                    'commentOnActive' => false,
                    'commentText'     => $legacyRec,
                ],
                'link_materials' => [
                    'label'           => 'Скачать материалы вебинара',
                    'showWhenEmpty'   => true,
                    'commentEnabled'  => true,
                    'commentOnActive' => false,
                    'commentText'     => $legacyMat,
                ],
            ],
        ],
        'kodex' => [
            'useThemeActiveBg'  => false,
            'activeBgColor'     => '#40b0bf',
            'activeTextColor'   => '#ffffff',
            'inactiveBgColor'   => '#fafafa',
            'inactiveTextColor' => '#7a7a7a',
            'inactiveCursor'    => 'default',
            'borderRadiusPx'    => 4,
            'shadowIntensity'   => 20,
            'shadowOnInactive'  => true,
            'showIcons'         => false,
            'buttons'           => [
                'link_host' => [
                    'label'           => 'Смотреть вебинар онлайн',
                    'showWhenEmpty'   => false,
                    'commentEnabled'  => false,
                    'commentOnActive' => false,
                    'commentText'     => DEFAULT_COMMENT_HOST,
                ],
                'link_recording' => [
                    'label'           => 'Скачать запись трансляции',
                    'showWhenEmpty'   => true,
                    'commentEnabled'  => false,
                    'commentOnActive' => false,
                    'commentText'     => DEFAULT_COMMENT_RECORDING,
                ],
                'link_materials' => [
                    'label'           => 'Скачать материалы вебинара',
                    'showWhenEmpty'   => true,
                    'commentEnabled'  => false,
                    'commentOnActive' => false,
                    'commentText'     => DEFAULT_COMMENT_MATERIALS,
                ],
            ],
        ],
    ];
}

function lp_clamp_range_int($value, int $min, int $max, int $fallback): int {
    if (!is_numeric($value)) return $fallback;
    return max($min, min($max, (int)$value));
}

function lp_sanitize_single_profile($in, array $def, bool $allowThemeActiveBg): array {
    $in = is_array($in) ? $in : [];
    $cursor = trim((string)($in['inactiveCursor'] ?? $def['inactiveCursor']));
    if (!in_array($cursor, ['default', 'pointer', 'not-allowed'], true)) {
        $cursor = $def['inactiveCursor'];
    }

    $inButtons = is_array($in['buttons'] ?? null) ? $in['buttons'] : [];
    $buttonsOut = [];
    foreach ($def['buttons'] as $slot => $bDef) {
        $bIn = is_array($inButtons[$slot] ?? null) ? $inButtons[$slot] : [];
        $label = trim((string)($bIn['label'] ?? ''));
        if ($label === '') $label = $bDef['label'];
        $buttonsOut[$slot] = [
            'label'           => $label,
            'showWhenEmpty'   => array_key_exists('showWhenEmpty', $bIn) ? (bool)$bIn['showWhenEmpty'] : (bool)$bDef['showWhenEmpty'],
            'commentEnabled'  => array_key_exists('commentEnabled', $bIn) ? (bool)$bIn['commentEnabled'] : (bool)$bDef['commentEnabled'],
            'commentOnActive' => array_key_exists('commentOnActive', $bIn) ? (bool)$bIn['commentOnActive'] : (bool)$bDef['commentOnActive'],
            'commentText'     => array_key_exists('commentText', $bIn) ? trim((string)$bIn['commentText']) : (string)$bDef['commentText'],
        ];
    }

    return [
        'useThemeActiveBg'  => $allowThemeActiveBg
            ? (array_key_exists('useThemeActiveBg', $in) ? (bool)$in['useThemeActiveBg'] : (bool)$def['useThemeActiveBg'])
            : false,
        'activeBgColor'     => lp_sanitize_hex_color($in['activeBgColor'] ?? null, $def['activeBgColor']),
        'activeTextColor'   => lp_sanitize_hex_color($in['activeTextColor'] ?? null, $def['activeTextColor']),
        'inactiveBgColor'   => lp_sanitize_hex_color($in['inactiveBgColor'] ?? null, $def['inactiveBgColor']),
        'inactiveTextColor' => lp_sanitize_hex_color($in['inactiveTextColor'] ?? null, $def['inactiveTextColor']),
        'inactiveCursor'    => $cursor,
        'borderRadiusPx'    => lp_clamp_range_int($in['borderRadiusPx'] ?? null, 0, 32, (int)$def['borderRadiusPx']),
        'shadowIntensity'   => lp_clamp_range_int($in['shadowIntensity'] ?? null, 0, 100, (int)$def['shadowIntensity']),
        'shadowOnInactive'  => array_key_exists('shadowOnInactive', $in) ? (bool)$in['shadowOnInactive'] : (bool)$def['shadowOnInactive'],
        'showIcons'         => array_key_exists('showIcons', $in) ? (bool)$in['showIcons'] : (bool)$def['showIcons'],
        'buttons'           => $buttonsOut,
    ];
}

function lp_sanitize_design_profiles($in, ?array $legacySettings = null): array {
    $defaults = lp_default_design_profiles($legacySettings);
    $in = is_array($in) ? $in : [];
    return [
        'cmk'   => lp_sanitize_single_profile($in['cmk'] ?? [], $defaults['cmk'], true),
        'kodex' => lp_sanitize_single_profile($in['kodex'] ?? [], $defaults['kodex'], false),
    ];
}

function lp_profile_key_for_landing(array $landing): string {
    return ((string)($landing['themePreset'] ?? 'wood')) === 'kodex' ? 'kodex' : 'cmk';
}

function lp_resolve_design_profile(array $landing): array {
    $profileKey = lp_profile_key_for_landing($landing);
    if (is_array($landing['designProfiles'] ?? null) && isset($landing['designProfiles'][$profileKey])) {
        $defaults = lp_default_design_profiles();
        return lp_sanitize_single_profile(
            (array)$landing['designProfiles'][$profileKey],
            $defaults[$profileKey],
            $profileKey === 'cmk'
        );
    }
    $static = lp_static_text_fields();
    return $static['designProfiles'][$profileKey];
}

/** Динамический CSS кнопок (цвета, скругление, тень, курсор) — для <style id="lpDynamicBtnStyle"> и живого API. */
function lp_dynamic_button_css(array $landing): string {
    $profileKey = lp_profile_key_for_landing($landing);
    $profile = lp_resolve_design_profile($landing);
    $themeKey = (string)($landing['themePreset'] ?? ($profileKey === 'kodex' ? 'kodex' : 'wood'));
    $theme = THEME_PRESETS[$themeKey] ?? THEME_PRESETS['wood'];

    if ($profileKey === 'cmk' && !empty($profile['useThemeActiveBg'])) {
        $activeBgCss    = "linear-gradient(135deg, {$theme['buttonStart']}, {$theme['buttonEnd']})";
        $activeHoverCss = "linear-gradient(135deg, {$theme['hoverStart']}, {$theme['hoverEnd']})";
    } else {
        $activeHex      = lp_sanitize_hex_color($profile['activeBgColor'] ?? '', $theme['buttonStart']);
        $hoverHex       = lp_shade_hex($activeHex, -15);
        $activeBgCss    = $activeHex;
        $activeHoverCss = $hoverHex;
    }

    $activeText   = lp_sanitize_hex_color($profile['activeTextColor'] ?? '', '#ffffff');
    $inactiveBg   = lp_sanitize_hex_color($profile['inactiveBgColor'] ?? '', '#7a7a7a');
    $inactiveText = lp_sanitize_hex_color($profile['inactiveTextColor'] ?? '', '#ffffff');
    $cursor       = in_array($profile['inactiveCursor'] ?? '', ['default', 'pointer', 'not-allowed'], true)
        ? $profile['inactiveCursor']
        : 'default';

    $radiusPx        = lp_clamp_range_int($profile['borderRadiusPx'] ?? null, 0, 32, 8);
    $shadowInt       = lp_clamp_range_int($profile['shadowIntensity'] ?? null, 0, 100, 60);
    $activeShadow    = lp_shadow_css($shadowInt, false);
    $hoverShadow     = lp_shadow_css($shadowInt, true);
    $inactiveShadow  = (!empty($profile['shadowOnInactive']) && $shadowInt > 0) ? $activeShadow : 'none';

    return <<<CSS
.btn {
  border-radius: {$radiusPx}px !important;
  color: {$activeText} !important;
  background: {$activeBgCss} !important;
  box-shadow: {$activeShadow} !important;
  cursor: pointer;
  position: relative;
}
.btn:not(.btn-disabled):hover {
  transform: translateY(-2px);
  color: {$activeText} !important;
  background: {$activeHoverCss} !important;
  box-shadow: {$hoverShadow} !important;
}
.btn.btn-disabled,
.btn.btn-disabled:hover,
.btn.btn-disabled:focus,
.btn.btn-disabled:active {
  background: {$inactiveBg} !important;
  color: {$inactiveText} !important;
  box-shadow: {$inactiveShadow} !important;
  cursor: {$cursor} !important;
  transform: none !important;
  opacity: 1 !important;
}
CSS;
}

/** Строит массив автоматических кнопок из ссылок вебинара с учётом профиля дизайна (ЦМК / КОДЕКС). */
function lp_build_buttons(array $webinar, array $landing = []): array {
    $profile = lp_resolve_design_profile($landing);
    $buttons = [];
    foreach (CANONICAL_BUTTONS as $def) {
        $field = $def['field'];
        $cfg = $profile['buttons'][$field] ?? [];
        $label = trim((string)($cfg['label'] ?? '')) ?: $def['label'];
        $url = trim((string)($webinar[$field] ?? ''));
        if ($url === '') {
            $showWhenEmpty = array_key_exists('showWhenEmpty', $cfg)
                ? (bool)$cfg['showWhenEmpty']
                : ($field === 'link_recording' || $field === 'link_materials');
            if ($showWhenEmpty) {
                $url = '#';
            } else {
                continue;
            }
        }
        $buttons[] = [
            'slot'  => $field,
            'label' => $label,
            'url'   => $url,
            'icon'  => $def['icon'],
        ];
    }
    return $buttons;
}

/** Статичные тексты и профили дизайна кнопок — общие для всех страниц (data/settings.json). */
function lp_static_text_fields(): array {
    $settings = (array)json_read(__DIR__ . '/../data/settings.json', []);
    $profiles = lp_sanitize_design_profiles($settings['landingDesignProfiles'] ?? null, $settings);
    return [
        'portsText'        => $settings['landingPortsText'] ?? 'Откройте порты TCP:443 UDP:16384 - 32768',
        'supportText'      => $settings['landingSupportText'] ?? 'Техническая поддержка',
        'supportPhone'     => $settings['landingSupportPhone'] ?? '+7 910 154 76 86',
        'supportSchedule'  => $settings['landingSupportSchedule'] ?? '(гарантируется только в день вебинара с 10:00 до 15:00)',
        'browserText'      => $settings['landingBrowserText'] ?? 'Используйте браузеры Yandex-Браузер / Google Chrome / Chromium / Firefox.',
        'tooltipRecording' => $profiles['cmk']['buttons']['link_recording']['commentText'],
        'tooltipMaterials' => $profiles['cmk']['buttons']['link_materials']['commentText'],
        'footerText'       => $settings['landingFooterText'] ?? '© 2026 Страница создана по материалам вебинара',
        'designProfiles'   => $profiles,
    ];
}

/** Находит вебинар по id, возвращает [индекс, строка] или [null, null]. */
function lp_find_webinar(array $webinars, string $webinarId): array {
    foreach ($webinars as $i => $w) {
        if ((string)($w['id'] ?? '') === $webinarId) return [$i, $w];
    }
    return [null, null];
}

/** Сопоставление старой кнопки без поля slot с каноническим слотом. */
function lp_guess_slot(array $btn): string {
    $slot = (string)($btn['slot'] ?? '');
    if ($slot !== '') return $slot;
    if (!empty($btn['isCustom'])) return '';
    return '';
}

/** HTML кнопок (иконки, disabled, индивидуальный POPup-комментарий) — единый для рендеров и живого API. */
function lp_buttons_html($buttons, array $landing): string {
    $profile = lp_resolve_design_profile($landing);
    $showIcons = !empty($profile['showIcons']);
    $html = '';

    foreach ((is_array($buttons) ? $buttons : []) as $btn) {
        $label = trim((string)($btn['label'] ?? ''));
        if ($label === '') continue;
        $url = trim((string)($btn['url'] ?? '')) ?: '#';
        $isDisabled = ($url === '#');
        $iconKey = (string)($btn['icon'] ?? 'download');
        $iconClass = BUTTON_ICON_CLASSES[$iconKey]['class'] ?? BUTTON_ICON_CLASSES['download']['class'];

        $tooltipText = '';
        $slot = lp_guess_slot((array)$btn);
        if ($slot !== '' && isset($profile['buttons'][$slot])) {
            $cfg = $profile['buttons'][$slot];
            if (!empty($cfg['commentEnabled']) && ($isDisabled || !empty($cfg['commentOnActive']))) {
                $tooltipText = trim((string)($cfg['commentText'] ?? ''));
            }
        } elseif (!empty($btn['commentEnabled']) && ($isDisabled || !empty($btn['commentOnActive']))) {
            $tooltipText = trim((string)($btn['commentText'] ?? ''));
        }

        $titleAttr = $tooltipText !== '' ? ' data-tooltip="' . landing_escape($tooltipText) . '"' : '';
        $extraClass = $isDisabled ? ' btn-disabled' : '';
        $rel = (!$isDisabled && str_starts_with($url, 'http')) ? ' rel="noopener noreferrer" target="_blank"' : '';
        $iconHtml = $showIcons ? '<i class="' . landing_escape($iconClass) . '" aria-hidden="true"></i>' : '';

        $html .= '<a class="btn js-download' . $extraClass . '" href="' . landing_escape($url) . '"' . $rel . $titleAttr . '>'
            . $iconHtml . landing_escape($label) . '</a>';
    }
    return $html;
}

/** Тема вебинара: экранирование + автоперенос строки после каждого двоеточия. */
function lp_title_html(array $webinar): string {
    $title = landing_nl2br((string)($webinar['title'] ?? 'Вебинар'));
    return str_replace(':', ':<br>', $title);
}

/** <title> вкладки: «01 января 2026 г. — Тема». */
function lp_page_title(array $webinar): string {
    return landing_escape(
        landing_format_date_long((string)($webinar['date'] ?? '')) . ' — '
        . trim(preg_replace('/\s*\n\s*/', ' ', (string)($webinar['title'] ?? 'Вебинар')))
    );
}

/** «02.10.2026 10:00» (экранировано) — для строки «ВЕБИНАР …». */
function lp_datetime_html(array $webinar, array $landing): string {
    return landing_escape(landing_format_webinar_datetime(
        (string)($webinar['date'] ?? ''),
        (string)($landing['eventTime'] ?? '10:00')
    ));
}

/** Строка требований: браузеры + порты. */
function lp_requirement_html(array $landing): string {
    $browser = landing_nl2br((string)($landing['browserText'] ?? ''));
    $ports = landing_escape((string)($landing['portsText'] ?? '') ?: 'Откройте порты TCP:443 UDP:16384 - 32768');
    return $browser . ' ' . $ports;
}

/** Строка поддержки: $plain=true — как в КОДЕКС (текст); false — как в классике (терминал + tel:). */
function lp_support_html(array $landing, bool $plain): string {
    $text = landing_escape((string)($landing['supportText'] ?? '') ?: 'Техническая поддержка');
    $phoneRaw = (string)($landing['supportPhone'] ?? '') ?: '+7 910 154 76 86';
    $phone = landing_escape($phoneRaw);
    if ($plain) {
        return $text . ' ' . $phone . '&nbsp;';
    }
    $href = landing_escape(landing_phone_href($phoneRaw));
    return '<span class="terminal">' . $text . ' <a class="support-link" href="' . $href . '">' . $phone . '</a></span>';
}

/** Безопасно нормализует относительный путь, схлопывая ".." без выхода за пределы. */
function lp_normalize_segments(string $path): array {
    $path = str_replace('\\', '/', $path);
    $path = preg_replace('/[\x00-\x1F\x7F]/u', '', $path) ?? '';
    $segments = [];
    foreach (explode('/', $path) as $chunk) {
        $chunk = trim($chunk);
        if ($chunk === '' || $chunk === '.') continue;
        if ($chunk === '..') { if ($segments) array_pop($segments); continue; }
        $segments[] = $chunk;
    }
    return $segments;
}

/** Вычисляет целевую директорию публикации внутри DOCUMENT_ROOT и проверяет, что не вышли за его пределы. */
function lp_resolve_output_dir(string $documentRoot, string $outputBase, string $webinarDate): array {
    $year = date('Y', strtotime($webinarDate) ?: time());
    $segments = lp_normalize_segments(($outputBase !== '' ? $outputBase . '/' : '') . $year);
    $relativeOutput = implode('/', $segments);
    $targetDir = rtrim($documentRoot, '/') . ($relativeOutput !== '' ? '/' . $relativeOutput : '');

    $normalizedTarget = rtrim(str_replace('\\', '/', $targetDir), '/');
    $normalizedRoot = rtrim(str_replace('\\', '/', $documentRoot), '/');
    if (strpos($normalizedTarget . '/', $normalizedRoot . '/') !== 0) {
        return [null, null];
    }
    return [$targetDir, $relativeOutput];
}

/**
 * Перезаписывает уже опубликованные на диске HTML-файлы актуальным рендером,
 * чтобы правки дизайна кнопок/комментариев применялись и к старым страницам.
 */
function lp_sync_published_html_files(string $dataDir, ?string $documentRoot = null): int {
    $docRoot = $documentRoot ?? realpath((string)($_SERVER['DOCUMENT_ROOT'] ?? ''));
    if (!is_string($docRoot) || $docRoot === '') return 0;

    $settings = (array)json_read($dataDir . '/settings.json', []);
    $landings = (array)json_read($dataDir . '/landing_pages.json', new stdClass());
    $webinars = json_read($dataDir . '/webinars.json', []);
    if (!is_array($webinars) || empty($landings)) return 0;

    $outputBase = trim((string)($settings['landingOutputBase'] ?? ''), '/');
    $staticFields = lp_static_text_fields();
    $updated = 0;

    foreach ($landings as $webinarId => $record) {
        $record = (array)$record;
        $fileName = basename(trim((string)($record['generatedFileName'] ?? '')));
        if ($fileName === '') continue;

        [$idx, $webinar] = lp_find_webinar($webinars, (string)$webinarId);
        if ($idx === null || !is_array($webinar)) continue;

        [$targetDir] = lp_resolve_output_dir($docRoot, $outputBase, (string)($webinar['date'] ?? ''));
        if ($targetDir === null) continue;

        $fullPath = rtrim($targetDir, '/') . '/' . $fileName;
        if (!is_file($fullPath) || !is_writable($fullPath)) continue;

        $renderData = array_merge($record, $staticFields);
        $customButtons = array_map(static function ($button) { $button = (array)$button; $button['isCustom'] = true; return $button; }, is_array($record['customButtons'] ?? null) ? $record['customButtons'] : []);
        $renderData['buttons'] = array_merge(lp_build_buttons($webinar, $renderData), $customButtons);

        $html = render_landing_html($webinar, $renderData, $fileName);
        if (@file_put_contents($fullPath, $html, LOCK_EX) !== false) {
            $updated++;
        }
    }
    return $updated;
}

/**
 * URL живого API данных страницы: {origin}{webdir}/landing_data.php?f={код файла}.
 */
function landing_live_api_url(string $fileName): string {
    $fileId = pathinfo($fileName, PATHINFO_FILENAME);
    if ($fileId === '') return '';
    if (function_exists('current_origin') && function_exists('current_script_web_dir')) {
        return current_origin() . rtrim(current_script_web_dir(), '/') . '/landing_data.php?f=' . rawurlencode($fileId);
    }
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . '/landing_data.php?f=' . rawurlencode($fileId);
}

/**
 * Инлайн-скрипт живого обновления страницы (без внешних зависимостей):
 *   - сразу и раз в 60 секунд подтягивает данные своего токена;
 *   - мгновенный рефреш при возврате во вкладку (visibilitychange);
 *   - обновляет в том числе динамические стили кнопок (#lpDynamicBtnStyle);
 *   - при удалении вебинара показывает «Мероприятие удалено».
 */
function landing_live_script(): string {
    return <<<'JS'
(function () {
  var body = document.body;
  var api = body.getAttribute('data-lp-api');
  if (!api) return;
  var stopped = false, busy = false, timer = null;

  function put(id, html) {
    var el = document.getElementById(id);
    if (el && typeof html === 'string') el.innerHTML = html;
  }

  function putStyle(css) {
    if (typeof css !== 'string') return;
    var st = document.getElementById('lpDynamicBtnStyle');
    if (!st) {
      st = document.createElement('style');
      st.id = 'lpDynamicBtnStyle';
      document.head.appendChild(st);
    }
    st.textContent = css;
  }

  function showRemoved() {
    if (stopped) return;
    stopped = true;
    if (timer) clearInterval(timer);
    document.title = 'Мероприятие удалено';
    var content = document.getElementById('lpContent');
    if (content) {
      content.innerHTML = '<div style="font-weight:800; font-size:clamp(22px, 4vw, 34px); letter-spacing:.02em; text-align:center; padding:48px 0;">Мероприятие удалено</div>';
    }
  }

  function apply(d) {
    if (d.pageTitle) document.title = d.pageTitle;
    if (typeof d.buttonCss === 'string') putStyle(d.buttonCss);
    put('lpKicker', d.kickerHtml);
    put('lpTitle', d.titleHtml);
    put('lpRequirement', d.requirementHtml);
    put('lpSupport', d.supportHtml);
    put('lpButtons', d.buttonsHtml);
  }

  function refresh() {
    if (stopped || busy) return;
    busy = true;
    fetch(api + (api.indexOf('?') >= 0 ? '&' : '?') + '_=' + Date.now(), {
      cache: 'no-store',
      headers: { 'Accept': 'application/json' }
    }).then(function (res) {
      return res.json();
    }).then(function (json) {
      busy = false;
      if (!json || !json.ok || !json.data) return;
      if (json.data.status === 'removed') showRemoved();
      else apply(json.data);
    }).catch(function () {
      busy = false;
    });
  }

  refresh();
  timer = setInterval(refresh, 60000);
  document.addEventListener('visibilitychange', function () {
    if (document.visibilityState === 'visible') refresh();
  });
})();
JS;
}

function render_landing_html(array $webinar, array $landing, string $fileName): string {
    // Дизайн «КОДЕКС» рендерится своей функцией — отлаженный прототип kodex/index.html.
    if ((string)($landing['themePreset'] ?? 'wood') === 'kodex') {
        return render_landing_html_kodex($webinar, $landing, $fileName);
    }

    $themeKey = (string)($landing['themePreset'] ?? 'wood');
    $theme = THEME_PRESETS[$themeKey] ?? THEME_PRESETS['wood'];

    $title = lp_title_html($webinar);
    $pageTitle = lp_page_title($webinar);
    $webinarDateTime = lp_datetime_html($webinar, $landing);
    $requirementHtml = lp_requirement_html($landing);
    $supportHtml = lp_support_html($landing, false);

    $bgUrl = trim((string)($landing['backgroundImage'] ?? ''));
    $bg = str_replace(['"', "'"], '', $bgUrl !== '' ? $bgUrl : $theme['baseImage']);

    $dateFontPx   = landing_clamp((int)($landing['dateFontPx'] ?? 0), 12, 72, 54);
    $titleFontPx  = landing_clamp((int)($landing['titleFontPx'] ?? 0), 14, 96, 46);
    $metaFontPx   = landing_clamp((int)($landing['metaFontPx'] ?? 0), 12, 48, 17);
    $buttonFontPx = landing_clamp((int)($landing['buttonFontPx'] ?? 0), 12, 40, 16);
    $footerFontPx = landing_clamp((int)($landing['footerFontPx'] ?? 0), 10, 30, 14);

    $intensity = landing_clamp((int)($landing['backgroundIntensity'] ?? 100), 0, 100, 100);
    $overlayStart = landing_scale_rgba_alpha($theme['overlayStart'], $intensity);
    $overlayEnd = landing_scale_rgba_alpha($theme['overlayEnd'], $intensity);

    $titleWidthPct = landing_clamp((int)($landing['titleWidthPct'] ?? 100), 100, 200, 100);
    $centerPx = (int)round(1400 * $titleWidthPct / 100);
    $contentPx = max(1600, $centerPx + 200);

    $buttonsHtml = lp_buttons_html(is_array($landing['buttons'] ?? null) ? $landing['buttons'] : [], $landing);
    $dynamicButtonCss = lp_dynamic_button_css($landing);

    $liveApi = landing_live_api_url($fileName);
    $liveJs = landing_live_script();

    return <<<HTML
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{$pageTitle}</title>
  <meta name="description" content="Информационная страница вебинара">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
  <style>
    * { box-sizing: border-box; }
    html, body { margin: 0; min-height: 100%; overflow-x: hidden; }
    body { font-family: "Montserrat", "Poppins", "Century Gothic", "Trebuchet MS", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; color: {$theme['textColor']}; background: #1f1f1f; }
    .hero { min-height: 100vh; display: grid; grid-template-rows: 1fr auto; background-image: linear-gradient(to bottom, {$overlayStart}, {$overlayEnd}), url("{$bg}"); background-size: cover; background-position: center; }
    .content { width: min({$contentPx}px, 94vw); margin: 0 auto; display: flex; align-items: center; justify-content: center; text-align: center; padding: 48px 0 22px; }
    .center { width: min({$centerPx}px, 100%); animation: up .8s ease forwards; }
    .kicker { margin: 0; font-size: min({$dateFontPx}px, 11vw); font-weight: 800; letter-spacing: .02em; line-height: 1.14; text-shadow: 0 12px 28px rgba(0,0,0,.35); }
    .main { margin: clamp(24px, 4.4vw, 44px) auto 0; font-size: min({$titleFontPx}px, 9vw); line-height: 1.18; font-weight: 800; text-wrap: balance; text-shadow: 0 12px 30px rgba(0,0,0,.42); }
    .meta { margin: clamp(24px, 5vw, 46px) auto 0; width: min(980px, 100%); font-size: min({$metaFontPx}px, 5.4vw); line-height: 1.48; color: rgba(255,255,255,.97); text-shadow: 0 8px 22px rgba(0,0,0,.45); }
    .meta p { margin: 0; }
    .terminal { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, Liberation Mono, monospace; background: rgba(11,14,14,.46); border: 1px solid rgba(255,255,255,.18); border-radius: 10px; display: inline-block; padding: 4px 12px; white-space: nowrap; }
    .support-link { color: #fff; text-decoration: none; border-bottom: 1px dashed rgba(255,255,255,.62); font-weight: 700; }
    .support-link:hover { color: #96ecff; border-color: rgba(150,236,255,.85); }
    .actions { margin-top: clamp(24px, 5vw, 42px); display: flex; justify-content: center; gap: 14px; flex-wrap: wrap; }
    .btn { min-height: 56px; display: inline-flex; align-items: center; justify-content: center; padding: 0 26px; border-radius: 8px; font-weight: 700; font-size: min({$buttonFontPx}px, 4.8vw); text-decoration: none; border: 1px solid transparent; color: #fff; background: linear-gradient(135deg, {$theme['buttonStart']}, {$theme['buttonEnd']}); box-shadow: 0 12px 24px rgba(0,0,0,.28); position: relative; transition: transform .2s ease, box-shadow .25s ease, background .25s ease, color .2s ease; }

    /* Всплывающий комментарий (POPup) при наведении на кнопку */
    .btn[data-tooltip]::after {
      content: attr(data-tooltip);
      position: absolute;
      bottom: 125%;
      left: 50%;
      transform: translateX(-50%) translateY(8px);
      background: rgba(18, 22, 23, 0.96);
      color: #fff;
      padding: 10px 14px;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 500;
      line-height: 1.45;
      width: 280px;
      text-align: center;
      box-shadow: 0 10px 26px rgba(0,0,0,0.5);
      border: 1px solid rgba(255,255,255,0.14);
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.2s ease, transform 0.2s ease;
      z-index: 999;
      font-family: "Montserrat", sans-serif;
    }
    .btn[data-tooltip]::before {
      content: "";
      position: absolute;
      bottom: 115%;
      left: 50%;
      transform: translateX(-50%) translateY(8px);
      border-width: 6px;
      border-style: solid;
      border-color: rgba(18, 22, 23, 0.96) transparent transparent transparent;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.2s ease, transform 0.2s ease;
      z-index: 999;
    }
    .btn[data-tooltip]:hover::after,
    .btn[data-tooltip]:hover::before {
      opacity: 1;
      transform: translateX(-50%) translateY(0);
    }

    @keyframes pulse-play {
      0% { transform: scale(1); }
      50% { transform: scale(1.15); }
      100% { transform: scale(1); }
    }
    @keyframes wiggle-video {
      0% { transform: rotate(0deg); }
      25% { transform: rotate(-8deg); }
      75% { transform: rotate(8deg); }
      100% { transform: rotate(0deg); }
    }
    @keyframes bounce-file {
      0% { transform: translateY(0); }
      50% { transform: translateY(-4px); }
      100% { transform: translateY(0); }
    }

    .btn:not(.btn-disabled) .fa-circle-play { display: inline-block; animation: pulse-play 2s infinite ease-in-out; }
    .btn:not(.btn-disabled) .fa-video { display: inline-block; animation: wiggle-video 2.5s infinite ease-in-out; }
    .btn:not(.btn-disabled) .fa-file-lines { display: inline-block; animation: bounce-file 2s infinite ease-in-out; }

    .btn i { margin-right: 10px; }
    .bottom { width: min({$contentPx}px, 94vw); margin: 0 auto; padding: 12px 0 18px; text-align: center; font-size: {$footerFontPx}px; color: rgba(255,255,255,.76); }
    @keyframes up { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
    @media (max-width: 740px) {
      .content { align-items: flex-start; padding-top: 84px; }
      .meta { line-height: 1.55; }
      .terminal { display: block; margin: 8px auto 0; max-width: max-content; }
      .btn { width: 100%; }
    }
  </style>
  <style id="lpDynamicBtnStyle">{$dynamicButtonCss}</style>
</head>
<body data-lp-api="{$liveApi}">
  <header class="hero">
    <main class="content">
      <section class="center" id="lpContent" aria-label="Информация о вебинаре">
        <p class="kicker" id="lpKicker">ВЕБИНАР {$webinarDateTime}</p>
        <h1 class="main" id="lpTitle">{$title}</h1>
        <section class="meta" aria-label="Технические требования и поддержка">
          <p id="lpRequirement">{$requirementHtml}</p>
          <p style="margin-top: 12px;" id="lpSupport">{$supportHtml}</p>
        </section>
        <section class="actions" id="lpButtons" aria-label="Скачивание материалов">{$buttonsHtml}</section>
      </section>
    </main>
    <footer class="bottom"></footer>
  </header>

  <script>
    document.addEventListener('click', function (event) {
      var link = event.target && event.target.closest ? event.target.closest('a.js-download') : null;
      if (link && link.getAttribute('href') === '#') {
        event.preventDefault();
      }
    });
  </script>
  <script>{$liveJs}</script>
</body>
</html>
HTML;
}

/**
 * Дизайн «КОДЕКС» — стили эталонной страницы edu.vsesem.ru/2026/1lzl.html
 * с полным редактированием шрифтов, фона, кнопок и всплывающих комментариев
 * через профиль КОДЕКС в панели управления.
 */
function render_landing_html_kodex(array $webinar, array $landing, string $fileName): string {
    $theme = THEME_PRESETS['kodex'];

    $title = lp_title_html($webinar);
    $pageTitle = lp_page_title($webinar);
    $webinarDateTime = lp_datetime_html($webinar, $landing);
    $requirementHtml = lp_requirement_html($landing);
    $supportHtml = lp_support_html($landing, true);

    $dateFontPx   = landing_clamp((int)($landing['dateFontPx'] ?? 0), 12, 72, 48);
    $titleFontPx  = landing_clamp((int)($landing['titleFontPx'] ?? 0), 14, 96, 48);
    $metaFontPx   = landing_clamp((int)($landing['metaFontPx'] ?? 0), 12, 48, 19);
    $buttonFontPx = landing_clamp((int)($landing['buttonFontPx'] ?? 0), 12, 40, 18);

    $titleWidthPct = landing_clamp((int)($landing['titleWidthPct'] ?? 100), 100, 200, 100);
    $centerPx = (int)round(1400 * $titleWidthPct / 100);

    $intensity = landing_clamp((int)($landing['backgroundIntensity'] ?? 0), 0, 100, 0);
    $overlayOpacity = number_format(0.65 * $intensity / 100, 3, '.', '');

    $defaultBg = (string)$theme['baseImage'];
    $customBg  = str_replace(['"', "'", '\\', "\n", "\r"], '', trim((string)($landing['backgroundImage'] ?? '')));
    $bgImages  = $customBg !== ''
        ? 'url("' . $customBg . '"), url("' . $defaultBg . '")'
        : 'url("' . $defaultBg . '")';

    $buttonsHtml = lp_buttons_html(is_array($landing['buttons'] ?? null) ? $landing['buttons'] : [], $landing);
    $dynamicButtonCss = lp_dynamic_button_css($landing);

    $liveApi = landing_live_api_url($fileName);
    $liveJs = landing_live_script();

    return <<<HTML
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
  <title>{$pageTitle}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
  <style>
    *, *::before, *::after { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; height: auto; min-height: 100vh; overflow-x: hidden; }
    body {
      font-family: 'Jost', sans-serif;
      line-height: 1.5;
      font-weight: 400;
      color: #232323;
      position: relative;
    }
    b, strong { font-weight: 700; }
    a {
      color: #6592e6;
      font-weight: 400;
      cursor: pointer;
      text-decoration: none;
      transition: color 0.6s;
    }
    h1, h2, h3, h4, h5, h6,
    .display-1, .display-2, .display-4, .display-5, .display-7,
    span, p, a {
      line-height: 1;
      word-break: break-word;
      word-wrap: break-word;
      font-weight: 400;
    }
    p { margin-top: 0; margin-bottom: 1rem; }
    h1, h2 { margin-top: 0; margin-bottom: 0.5rem; }
    .mb-3 { margin-bottom: 1rem !important; }
    .mt-3 { margin-top: 1rem !important; }

    section {
      background-color: #ffffff;
      background-position: 50% 50%;
      background-repeat: no-repeat;
      background-size: cover;
      position: relative;
      word-wrap: break-word;
    }
    .mbr-section-title,
    .mbr-section-subtitle { line-height: 1.3; }
    .mbr-text { font-style: normal; line-height: 1.7; }
    .mbr-white { color: #ffffff; }
    .align-center { text-align: center; }

    .mbr-fullscreen {
      display: flex;
      align-items: center;
      min-height: 100vh;
      padding-top: 3rem;
      padding-bottom: 3rem;
    }

    .container-fluid {
      width: 100%;
      margin-left: auto;
      margin-right: auto;
      padding-left: 12px;
      padding-right: 12px;
      position: relative;
      word-wrap: break-word;
    }
    .row {
      display: flex;
      flex-wrap: wrap;
      margin-left: -12px;
      margin-right: -12px;
      position: relative;
      word-wrap: break-word;
    }
    .justify-content-center { justify-content: center; }
    .col-12, .col-lg-12 {
      flex: 0 0 auto;
      width: 100%;
      padding-left: 12px;
      padding-right: 12px;
    }
    @media (min-width: 768px) {
      .container-fluid { padding-left: 32px; padding-right: 32px; }
    }
    @media (min-width: 992px) {
      .row { margin-left: -16px; margin-right: -16px; }
      .row > [class*=col] { padding-left: 16px; padding-right: 16px; }
    }

    .mbr-section-btn {
      margin-left: -0.6rem;
      margin-right: -0.6rem;
      font-size: 0;
    }

    .btn {
      font-family: 'Jost', sans-serif;
      min-height: 52px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 0.6rem 1.5rem;
      margin: 0.6rem 0.6rem;
      border-radius: 4px;
      font-weight: 600;
      font-size: min({$buttonFontPx}px, 4.8vw);
      line-height: 1.35;
      text-decoration: none;
      border: 1px solid transparent;
      color: #fff;
      background: {$theme['buttonStart']};
      white-space: normal;
      word-break: break-word;
      cursor: pointer;
      position: relative;
      transition: transform .2s ease, box-shadow .25s ease, background .25s ease, color .2s ease;
    }

    /* Всплывающий комментарий (POPup) при наведении на кнопку */
    .btn[data-tooltip]::after {
      content: attr(data-tooltip);
      position: absolute;
      bottom: 125%;
      left: 50%;
      transform: translateX(-50%) translateY(8px);
      background: rgba(18, 22, 23, 0.96);
      color: #fff;
      padding: 10px 14px;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 500;
      line-height: 1.45;
      width: 280px;
      text-align: center;
      box-shadow: 0 10px 26px rgba(0, 0, 0, 0.5);
      border: 1px solid rgba(255, 255, 255, 0.14);
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.2s ease, transform 0.2s ease;
      z-index: 999;
      font-family: 'Jost', sans-serif;
    }
    .btn[data-tooltip]::before {
      content: "";
      position: absolute;
      bottom: 115%;
      left: 50%;
      transform: translateX(-50%) translateY(8px);
      border-width: 6px;
      border-style: solid;
      border-color: rgba(18, 22, 23, 0.96) transparent transparent transparent;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.2s ease, transform 0.2s ease;
      z-index: 999;
    }
    .btn[data-tooltip]:hover::after,
    .btn[data-tooltip]:hover::before {
      opacity: 1;
      transform: translateX(-50%) translateY(0);
    }

    @keyframes pulse-play {
      0% { transform: scale(1); }
      50% { transform: scale(1.15); }
      100% { transform: scale(1); }
    }
    @keyframes wiggle-video {
      0% { transform: rotate(0deg); }
      25% { transform: rotate(-8deg); }
      75% { transform: rotate(8deg); }
      100% { transform: rotate(0deg); }
    }
    @keyframes bounce-file {
      0% { transform: translateY(0); }
      50% { transform: translateY(-4px); }
      100% { transform: translateY(0); }
    }
    .btn:not(.btn-disabled) .fa-circle-play { display: inline-block; animation: pulse-play 2s infinite ease-in-out; }
    .btn:not(.btn-disabled) .fa-video { display: inline-block; animation: wiggle-video 2.5s infinite ease-in-out; }
    .btn:not(.btn-disabled) .fa-file-lines { display: inline-block; animation: bounce-file 2s infinite ease-in-out; }
    .btn i { margin-right: 10px; }

    .display-1 { font-family: 'Jost', sans-serif; font-size: 4.6rem; line-height: 1.1; }
    .display-2 { font-family: 'Jost', sans-serif; font-size: 3rem;   line-height: 1.1; }
    .display-4 { font-family: 'Jost', sans-serif; font-size: 1.1rem; line-height: 1.5; }
    .display-5 { font-family: 'Jost', sans-serif; font-size: 2rem;   line-height: 1.5; }
    .display-7 { font-family: 'Jost', sans-serif; font-size: 1.2rem; line-height: 1.5; }

    .kodex-hero .kodex-kicker { font-size: min({$dateFontPx}px, 11vw); line-height: 1.1; }
    .kodex-hero .kodex-title  { font-size: min({$titleFontPx}px, 9vw);  line-height: 1.1; }
    .kodex-hero .kodex-meta   { font-size: min({$metaFontPx}px, 5.4vw); line-height: 1.5; }

    .kodex-hero {
      background-color: #241b14;
      background-image: {$bgImages};
    }
    .kodex-overlay {
      position: absolute;
      left: 0; right: 0; top: 0; bottom: 0;
      background: #000;
      opacity: {$overlayOpacity};
      pointer-events: none;
    }
    .kodex-copy { width: min({$centerPx}px, 100%); margin: 0 auto; }
  </style>
  <style id="lpDynamicBtnStyle">{$dynamicButtonCss}</style>
</head>
<body data-lp-api="{$liveApi}">
  <!-- Шаблон КОДЕКС -->
  <section class="kodex-hero mbr-fullscreen mbr-parallax-background">
    <div class="kodex-overlay"></div>
    <div class="align-center container-fluid">
      <div class="row justify-content-center">
        <div class="col-12 col-lg-12">
          <div class="kodex-copy" id="lpContent">
            <h1 class="mbr-section-title mbr-fonts-style mbr-white mb-3 display-2 kodex-kicker"><strong id="lpKicker">ВЕБИНАР {$webinarDateTime}</strong><div><br></div></h1>
            <h2 class="mbr-section-subtitle mbr-fonts-style mbr-white mb-3 display-2 kodex-title"><strong id="lpTitle">{$title}</strong><strong>&nbsp;</strong><div><div><div><div><div><div><div><div><div><strong><br></strong></div></div></div></div></div></div></div></div></div></h2>
            <p class="mbr-text mbr-fonts-style mbr-white display-7 kodex-meta"><span id="lpRequirement">{$requirementHtml}</span><br><span id="lpSupport">{$supportHtml}</span></p>
            <div class="mbr-section-btn mt-3" id="lpButtons">{$buttonsHtml}</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <script>
    document.addEventListener('click', function (event) {
      var link = event.target && event.target.closest ? event.target.closest('a.js-download') : null;
      if (link && link.getAttribute('href') === '#') {
        event.preventDefault();
      }
    });
  </script>
  <script>{$liveJs}</script>
</body>
</html>
HTML;
}
