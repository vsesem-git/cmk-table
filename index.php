<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
auth_require_page();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ЦМК · Реестр вебинаров</title>
<link rel="stylesheet" href="assets/style.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body>
<div class="app">
<?php require __DIR__ . '/partials/toolbar.php'; ?>
</div>

<!-- Всплывающий Excel-фильтр -->
<div id="filterPop" class="filter-pop" style="display:none"></div>

<?php
require __DIR__ . '/partials/modal_webinar.php';
require __DIR__ . '/partials/modal_settings.php';
require __DIR__ . '/partials/modal_landing.php';
require __DIR__ . '/partials/modal_crm_fields.php';
require __DIR__ . '/partials/modal_send_export.php';
require __DIR__ . '/partials/modal_contract_gen.php';
require __DIR__ . '/partials/modal_user_account.php';
require __DIR__ . '/partials/modal_add_column.php';
require __DIR__ . '/partials/modal_sub_gen.php';
?>

<div id="toast" class="toast"></div>

<script src="assets/app.js"></script>
</body>
</html>
