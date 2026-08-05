<?php
/**
 * Настройки SMTP для отправки писем (используется «Выгрузить для отправки»
 * → «Отправить на почту»).
 *   GET               — текущие настройки (пароль маскируется, не отдаётся в браузер)
 *   POST              — сохранить настройки {host,port,encryption,username,password,fromEmail,fromName}
 *                        если password === маска или пусто — старый пароль не трогаем
 *   POST ?action=test — {toEmail} — отправить тестовое письмо текущими настройками
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
auth_require_json();

require_once __DIR__ . '/includes/storage.php';
require_once __DIR__ . '/includes/mailer.php';

header('Content-Type: application/json; charset=utf-8');

const SMTP_FILE = __DIR__ . '/data/smtp.json';
const SMTP_PASSWORD_MASK = '••••••••';

function smtp_default_settings(): array {
    return ['host' => '', 'port' => 587, 'encryption' => 'tls', 'username' => '', 'password' => '', 'fromEmail' => '', 'fromName' => 'ЦМК Реестр вебинаров'];
}

function smtp_read(): array {
    return array_merge(smtp_default_settings(), json_read(SMTP_FILE, []));
}

function respond_smtp(bool $ok, $data = null, string $error = ''): void {
    echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $error], JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    if ($method === 'GET') {
        $s = smtp_read();
        $s['password'] = $s['password'] !== '' ? SMTP_PASSWORD_MASK : '';
        $s['configured'] = $s['host'] !== '' && $s['fromEmail'] !== '';
        respond_smtp(true, $s);
    }

    if ($method === 'POST' && $action === 'test') {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $toEmail = trim((string)($input['toEmail'] ?? ''));
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) respond_smtp(false, null, 'Некорректный email');

        $s = smtp_read();
        if ($s['host'] === '') respond_smtp(false, null, 'Сначала сохраните настройки SMTP');

        $result = send_mail_via_smtp($s, [$toEmail], 'Тестовое письмо — ЦМК Реестр вебинаров',
            '<p>Это тестовое письмо подтверждает, что настройки SMTP в реестре вебинаров работают корректно.</p>');
        if ($result !== true) respond_smtp(false, null, 'Не удалось отправить: ' . $result);
        respond_smtp(true);
    }

    if ($method === 'POST' && $action === '') {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $current = smtp_read();

        $password = (string)($input['password'] ?? '');
        if ($password === '' || $password === SMTP_PASSWORD_MASK) {
            $password = $current['password'];
        }

        $clean = [
            'host'       => trim((string)($input['host'] ?? '')),
            'port'       => max(1, (int)($input['port'] ?? 587)),
            'encryption' => in_array($input['encryption'] ?? '', ['none', 'ssl', 'tls'], true) ? $input['encryption'] : 'tls',
            'username'   => trim((string)($input['username'] ?? '')),
            'password'   => $password,
            'fromEmail'  => trim((string)($input['fromEmail'] ?? '')),
            'fromName'   => trim((string)($input['fromName'] ?? '')) ?: 'ЦМК Реестр вебинаров',
        ];
        if (!json_write(SMTP_FILE, $clean)) respond_smtp(false, null, 'Не удалось сохранить настройки');

        $out = $clean;
        $out['password'] = $out['password'] !== '' ? SMTP_PASSWORD_MASK : '';
        $out['configured'] = $clean['host'] !== '' && $clean['fromEmail'] !== '';
        respond_smtp(true, $out);
    }

    http_response_code(405);
    respond_smtp(false, null, 'Метод не поддерживается');
} catch (Throwable $e) {
    http_response_code(500);
    respond_smtp(false, null, 'Ошибка сервера: ' . $e->getMessage());
}
