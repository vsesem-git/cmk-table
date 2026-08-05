<?php
/**
 * Отправка сформированной таблицы («Выгрузить для отправки») на почту —
 * письмо оформляется в фирменном стиле реестра.
 *   POST {to:[...], subject, tableHtml}
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
auth_require_json();

require_once __DIR__ . '/includes/storage.php';
require_once __DIR__ . '/includes/mailer.php';

header('Content-Type: application/json; charset=utf-8');

const MAIL_SMTP_FILE = __DIR__ . '/data/smtp.json';

function respond_mail(bool $ok, $data = null, string $error = ''): void {
    echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $error], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    respond_mail(false, null, 'Метод не поддерживается');
}

try {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $to = array_values(array_filter(array_map('trim', (array)($input['to'] ?? []))));
    $subject = trim((string)($input['subject'] ?? 'Ссылки на вебинары'));
    $tableHtml = (string)($input['tableHtml'] ?? '');

    if (empty($to)) respond_mail(false, null, 'Не указан ни один получатель');
    foreach ($to as $addr) {
        if (!filter_var($addr, FILTER_VALIDATE_EMAIL)) respond_mail(false, null, 'Некорректный email: ' . $addr);
    }
    if ($tableHtml === '') respond_mail(false, null, 'Пустая таблица — нечего отправлять');

    $smtp = array_merge(
        ['host' => '', 'port' => 587, 'encryption' => 'tls', 'username' => '', 'password' => '', 'fromEmail' => '', 'fromName' => 'ЦМК Реестр вебинаров'],
        json_read(MAIL_SMTP_FILE, [])
    );
    if ($smtp['host'] === '' || $smtp['fromEmail'] === '') {
        respond_mail(false, null, 'SMTP не настроен — заполните его в аккаунте пользователя');
    }

    $styledTable = '<div style="overflow-x:auto;">' . $tableHtml . '</div>
    <style>
      table.mail-table { border-collapse: collapse; width: 100%; font-size: 13px; }
      table.mail-table th { background:#0A5E5E; color:#fff; text-align:left; padding:8px 12px; }
      table.mail-table td { padding:7px 12px; border-bottom:1px solid #E7EEEC; }
      table.mail-table tr:nth-child(even) { background:#EDF5F3; }
    </style>';

    $html = wrap_branded_email_html($subject, $styledTable);
    $result = send_mail_via_smtp($smtp, $to, $subject, $html);
    if ($result !== true) respond_mail(false, null, 'Не удалось отправить: ' . $result);

    respond_mail(true, ['sentTo' => $to]);
} catch (Throwable $e) {
    http_response_code(500);
    respond_mail(false, null, 'Ошибка сервера: ' . $e->getMessage());
}
