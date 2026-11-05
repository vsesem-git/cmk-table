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
echo "Landing design tests passed\n";
