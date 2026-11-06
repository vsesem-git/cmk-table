<?php
// Run with: php tests/landing_design.php
declare(strict_types=1);
require_once __DIR__ . '/../includes/storage.php';
require_once __DIR__ . '/../includes/landing_template.php';
function check_design(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
}
$defaults = lp_default_design_profiles();
$w = ['title' => 'Тест', 'date' => '2026-11-05', 'link_host' => 'https://example.com'];
$kodex = ['themePreset' => 'kodex', 'designProfiles' => $defaults];
$buttons = lp_build_buttons($w, $kodex);
check_design($buttons[0]['label'] === 'Смотреть вебинар онлайн', 'Название КОДЕКС');
check_design(strpos(lp_buttons_html($buttons, $kodex), 'data-tooltip') === false, 'Подсказки КОДЕКС по умолчанию выключены');
check_design(strpos(lp_dynamic_button_css($kodex), 'cursor: default !important') !== false, 'Курсор КОДЕКС');
$cmk = ['themePreset' => 'wood', 'designProfiles' => $defaults];
check_design(strpos(lp_buttons_html(lp_build_buttons($w, $cmk), $cmk), 'data-tooltip') !== false, 'Подсказки ЦМК');
$kodex['designProfiles']['kodex']['buttons']['link_recording']['commentEnabled'] = true;
$kodex['designProfiles']['kodex']['buttons']['link_recording']['commentText'] = 'Свой комментарий';
check_design(strpos(lp_buttons_html(lp_build_buttons($w, $kodex), $kodex), 'Свой комментарий') !== false, 'Свой комментарий');
$kodex['designProfiles']['kodex']['borderRadiusPx'] = 13;
$kodex['designProfiles']['kodex']['shadowIntensity'] = 0;
$css = lp_dynamic_button_css($kodex);
check_design(strpos($css, 'border-radius: 13px') !== false && strpos($css, 'box-shadow: none') !== false, 'Скругление и тень');
$kodex['buttons'] = lp_build_buttons($w, $kodex);
$html = render_landing_html($w, $kodex, 'abc.html');
check_design(strpos($html, 'id="lpDynamicBtnStyle"') !== false && strpos($html, 'Смотреть вебинар онлайн') !== false, 'HTML');
$custom = [['isCustom'=>true,'label'=>'Запись','url'=>'#','icon'=>'video','commentEnabled'=>true,'commentText'=>'Другое']];
check_design(strpos(lp_buttons_html($custom, $kodex), 'Другое') !== false, 'Комментарий своей кнопки');
// Материалы скрываются до даты вебинара в обоих шаблонах и не выдаются в HTML.
foreach (['wood', 'kodex'] as $theme) {
    $landing = ['themePreset' => $theme, 'designProfiles' => $defaults];
    $event = ['title' => 'Тест', 'date' => '2026-11-06', 'link_host' => 'https://example.com/live',
        'link_recording' => 'https://example.com/recording', 'link_materials' => 'https://cloud.mail.ru/public/kQjT/S7VG2YWG2'];
    $bySlot = static function (array $buttons): array { return array_column($buttons, null, 'slot'); };
    $before = $bySlot(lp_build_buttons($event, $landing, '2026-01-01'));
    check_design($before['link_materials']['url'] === '#' && $before['link_materials']['dateLocked'], 'Материалы закрыты до даты: ' . $theme);
    check_design($before['link_host']['url'] === $event['link_host'] && $before['link_recording']['url'] === $event['link_recording'], 'Другие ссылки не изменяются');
    $beforeHtml = lp_buttons_html(array_values($before), $landing);
    check_design(strpos($beforeHtml, $event['link_materials']) === false && strpos($beforeHtml, 'btn-disabled') !== false, 'Ссылка не попадает в HTML');
    if ($theme === 'wood') check_design(strpos($beforeHtml, 'Материалы будут доступны с 06 ноября 2026 г.') !== false, 'Корректное объяснение блокировки');
    if ($theme === 'kodex') check_design(strpos($beforeHtml, 'data-tooltip') === false, 'КОДЕКС без подсказок');
    $renderData = array_merge(lp_static_text_fields(), $landing, ['buttons' => array_values($before)]);
    check_design(strpos(render_landing_html($event, $renderData, 'abc.html'), $event['link_materials']) === false, 'Ссылка не попадает в опубликованную страницу');
    foreach (['2026-11-06', '2026-11-07'] as $day) {
        $after = $bySlot(lp_build_buttons($event, $landing, $day));
        check_design($after['link_materials']['url'] === $event['link_materials'], 'Материалы открыты с даты: ' . $day);
    }
    $event['link_materials'] = '';
    check_design($bySlot(lp_build_buttons($event, $landing, '2026-11-06'))['link_materials']['url'] === '#', 'Пустая ссылка не активируется');
    $event['link_materials'] = 'https://example.com/materials';
    foreach (['', '2026-02-30', 'не дата'] as $badDate) {
        $event['date'] = $badDate;
        check_design($bySlot(lp_build_buttons($event, $landing, '2026-11-06'))['link_materials']['url'] === '#', 'Неверная дата не открывает ссылку');
    }
}
echo "Landing design tests passed\n";
