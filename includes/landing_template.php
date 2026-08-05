<?php
/**
 * Рендер статической HTML-страницы для участников вебинара.
 * Дизайн — точный перенос присланного шаблона (тема/фон/оверлей/кнопки/шрифты),
 * адаптированный на сервер (PHP heredoc вместо JS-строки).
 * Домен нигде не хардкодится — все абсолютные ссылки уже посчитаны вызывающим кодом.
 */

declare(strict_types=1);

const TEMPLATE_VERSION = '2.0.0';

const THEME_PRESETS = [
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
    if ($value <= 0) $value = $fallback;
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

function render_landing_html(array $webinar, array $landing, string $fileName): string {
    $themeKey = (string)($landing['themePreset'] ?? 'wood');
    $theme = THEME_PRESETS[$themeKey] ?? THEME_PRESETS['wood'];

    $title = landing_nl2br((string)($webinar['title'] ?? 'Вебинар'));
    $title = str_replace(':', ':<br>', $title); // авто-перенос строки после каждого двоеточия — только на лендинге
    $pageTitle = landing_escape(landing_format_date_long((string)($webinar['date'] ?? '')) . ' — ' . trim(preg_replace('/\s*\n\s*/', ' ', (string)($webinar['title'] ?? 'Вебинар'))));
    $webinarDateTime = landing_escape(landing_format_webinar_datetime((string)($webinar['date'] ?? ''), (string)($landing['eventTime'] ?? '10:00')));

    $safeBrowser = landing_nl2br((string)($landing['browserText'] ?? ''));
    $safePorts = landing_escape((string)($landing['portsText'] ?? '') ?: 'Откройте порты TCP:443 UDP:16384 - 32768');
    $supportText = landing_escape((string)($landing['supportText'] ?? '') ?: 'Техническая поддержка');
    $supportPhoneRaw = (string)($landing['supportPhone'] ?? '') ?: '+7 910 154 76 86';
    $supportPhone = landing_escape($supportPhoneRaw);
    $supportHref = landing_escape(landing_phone_href($supportPhoneRaw));
    $supportSchedule = landing_escape((string)($landing['supportSchedule'] ?? '') ?: '(гарантируется только в день вебинара с 10-00 до 15-00)');
    $footerText = landing_escape((string)($landing['footerText'] ?? '') ?: '© 2026 Страница создана по материалам вебинара');

    $bgUrl = trim((string)($landing['backgroundImage'] ?? ''));
    $bg = str_replace(['"', "'"], '', $bgUrl !== '' ? $bgUrl : $theme['baseImage']);

    $generatedAt = landing_escape(date('d.m.Y H:i'));
    $faqUrl = landing_escape(TECH_REQUIREMENTS_FAQ_URL);

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

    $buttonsHtml = '';
    foreach ((is_array($landing['buttons'] ?? null) ? $landing['buttons'] : []) as $btn) {
        $label = trim((string)($btn['label'] ?? ''));
        if ($label === '') continue;
        $url = trim((string)($btn['url'] ?? '')) ?: '#';
        $iconKey = (string)($btn['icon'] ?? 'download');
        $iconClass = BUTTON_ICON_CLASSES[$iconKey]['class'] ?? BUTTON_ICON_CLASSES['download']['class'];
        
        $titleAttr = '';
        $extraClass = '';
        if ($url === '#') {
            $extraClass = ' btn-disabled';
            $tooltipText = '';
            if (mb_strpos($label, 'запись') !== false || $iconKey === 'video') {
                $tooltipText = (string)($landing['tooltipRecording'] ?? 'Организатор еще не разместил запись трансляции. Обычно это занимает от 2 до 5 дней. Обновите страницу Ctrl+F.');
            } elseif (mb_strpos($label, 'материалы') !== false || $iconKey === 'file') {
                $tooltipText = (string)($landing['tooltipMaterials'] ?? 'Организатор еще не разместил материалы вебинара. Обновите страницу Ctrl+F5.');
            }
            if ($tooltipText !== '') {
                $titleAttr = ' data-tooltip="' . landing_escape($tooltipText) . '"';
            }
        }
        
        $rel = ($url !== '#' && str_starts_with($url, 'http')) ? ' rel="noopener noreferrer" target="_blank"' : '';
        $buttonsHtml .= '<a class="btn js-download' . $extraClass . '" href="' . landing_escape($url) . '"' . $rel . $titleAttr . '>'
            . '<i class="' . landing_escape($iconClass) . '" aria-hidden="true"></i>' . landing_escape($label) . '</a>';
    }

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
    .btn { min-height: 56px; display: inline-flex; align-items: center; justify-content: center; padding: 0 26px; border-radius: 8px; font-weight: 700; font-size: min({$buttonFontPx}px, 4.8vw); text-decoration: none; border: 1px solid transparent; color: #fff; background: linear-gradient(135deg, {$theme['buttonStart']}, {$theme['buttonEnd']}); box-shadow: 0 12px 24px rgba(0,0,0,.28); transition: transform .2s ease, box-shadow .25s ease, background .25s ease; }
    .btn:hover { transform: translateY(-2px); box-shadow: 0 16px 30px rgba(0,0,0,.34); background: linear-gradient(135deg, {$theme['hoverStart']}, {$theme['hoverEnd']}); }
    .btn.btn-disabled { background: #7a7a7a !important; cursor: not-allowed; box-shadow: none !important; opacity: 0.85; position: relative; }
    .btn.btn-disabled:hover { transform: none !important; box-shadow: none !important; background: #7a7a7a !important; }
    
    /* Beautiful CSS Tooltip */
    .btn.btn-disabled[data-tooltip]::after {
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
    .btn.btn-disabled[data-tooltip]::before {
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
    .btn.btn-disabled[data-tooltip]:hover::after,
    .btn.btn-disabled[data-tooltip]:hover::before {
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
</head>
<body>
  <header class="hero">
    <main class="content">
      <section class="center" aria-label="Информация о вебинаре">
        <p class="kicker">ВЕБИНАР {$webinarDateTime}</p>
        <h1 class="main">{$title}</h1>
        <section class="meta" aria-label="Технические требования и поддержка">
          <p>{$safeBrowser} {$safePorts}</p>
          <p style="margin-top: 12px;"><span class="terminal">{$supportText} <a class="support-link" href="{$supportHref}">{$supportPhone}</a></span></p>
        </section>
        <section class="actions" aria-label="Скачивание материалов">{$buttonsHtml}</section>
      </section>
    </main>
    <footer class="bottom">
  </header>

  <script>
    document.querySelectorAll('.js-download').forEach((link) => {
      if (link.getAttribute('href') === '#') {
        link.addEventListener('click', (event) => event.preventDefault());
      }
    });
  </script>
</body>
</html>
HTML;
}
