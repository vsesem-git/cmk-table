<?php
/**
 * Авторизация: логин/логаут, первичная настройка (создание первого аккаунта),
 * управление пользователями (несколько именных учётных записей).
 *
 *   GET  ?action=me            — текущий пользователь + есть ли вообще аккаунты
 *   POST ?action=login         — {username, password}
 *   POST ?action=logout        —
 *   POST ?action=register-first — {username, password} — только если аккаунтов ещё нет
 *   POST ?action=add-user      — {username, password, displayName?} — нужен логин
 *   POST ?action=change-password — {newPassword} — себе, нужен логин
 *   DELETE ?action=remove-user&username=… — нужен логин, нельзя удалить последнего/себя-единственного
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

function respond_auth(bool $ok, $data = null, string $error = ''): void {
    echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $error], JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    if ($method === 'GET' && $action === 'me') {
        $username = auth_current_username();
        respond_auth(true, [
            'username'   => $username,
            'needsSetup' => !auth_has_any_user(),
        ]);
    }

    if ($method === 'POST' && $action === 'register-first') {
        if (auth_has_any_user()) respond_auth(false, null, 'Аккаунты уже созданы — используйте обычный вход');
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $username = trim((string)($input['username'] ?? ''));
        $password = (string)($input['password'] ?? '');
        if ($username === '' || mb_strlen($username) < 2) respond_auth(false, null, 'Укажите логин (минимум 2 символа)');
        if (mb_strlen($password) < 6) respond_auth(false, null, 'Пароль должен быть не короче 6 символов');
        if (!auth_save_user($username, $password)) respond_auth(false, null, 'Не удалось создать пользователя');
        auth_start_session($username);
        respond_auth(true, ['username' => $username]);
    }

    if ($method === 'POST' && $action === 'login') {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $username = trim((string)($input['username'] ?? ''));
        $password = (string)($input['password'] ?? '');
        $user = auth_verify_login($username, $password);
        if ($user === null) respond_auth(false, null, 'Неверный логин или пароль');
        auth_start_session($user['username']);
        respond_auth(true, $user);
    }

    if ($method === 'POST' && $action === 'logout') {
        auth_end_session();
        respond_auth(true);
    }

    // Всё, что ниже, требует авторизации.
    auth_require_json();
    $me = auth_current_username();

    if ($method === 'GET' && $action === 'users') {
        $users = array_map(fn($u) => ['username' => $u['username'], 'displayName' => $u['displayName'] ?? $u['username']], auth_users());
        respond_auth(true, $users);
    }

    if ($method === 'POST' && $action === 'add-user') {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $username = trim((string)($input['username'] ?? ''));
        $password = (string)($input['password'] ?? '');
        $displayName = trim((string)($input['displayName'] ?? ''));
        if ($username === '' || mb_strlen($username) < 2) respond_auth(false, null, 'Укажите логин (минимум 2 символа)');
        if (mb_strlen($password) < 6) respond_auth(false, null, 'Пароль должен быть не короче 6 символов');
        if (auth_find_user(auth_users(), $username) !== null) respond_auth(false, null, 'Такой логин уже существует');
        if (!auth_save_user($username, $password, $displayName)) respond_auth(false, null, 'Не удалось создать пользователя');
        respond_auth(true, ['username' => $username]);
    }

    if ($method === 'POST' && $action === 'change-password') {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $newPassword = (string)($input['newPassword'] ?? '');
        if (mb_strlen($newPassword) < 6) respond_auth(false, null, 'Пароль должен быть не короче 6 символов');
        if (!auth_change_password($me, $newPassword)) respond_auth(false, null, 'Не удалось сменить пароль');
        respond_auth(true);
    }

    if ($method === 'POST' && $action === 'reset-password') {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $username = trim((string)($input['username'] ?? ''));
        $newPassword = (string)($input['newPassword'] ?? '');
        if ($username === '') respond_auth(false, null, 'Не указан пользователь');
        if (mb_strlen($newPassword) < 6) respond_auth(false, null, 'Пароль должен быть не короче 6 символов');
        if (!auth_change_password($username, $newPassword)) respond_auth(false, null, 'Пользователь не найден');
        respond_auth(true);
    }

    if ($method === 'DELETE' && $action === 'remove-user') {
        parse_str(file_get_contents('php://input'), $bodyParams);
        $username = (string)($_GET['username'] ?? $bodyParams['username'] ?? '');
        if ($username === '') respond_auth(false, null, 'Не указан логин');
        $users = auth_users();
        if (count($users) <= 1) respond_auth(false, null, 'Нельзя удалить последнего пользователя — иначе никто не сможет войти');
        if (!auth_remove_user($username)) respond_auth(false, null, 'Пользователь не найден');
        respond_auth(true);
    }

    http_response_code(405);
    respond_auth(false, null, 'Метод не поддерживается');
} catch (Throwable $e) {
    http_response_code(500);
    respond_auth(false, null, 'Ошибка сервера: ' . $e->getMessage());
}
