<?php
/**
 * Авторизация реестра — логин/пароль, несколько именных учётных записей.
 *
 * Сессия не завязана на стандартный PHP session (у него сервер обычно сам
 * убивает данные сессии через ~24 минуты бездействия, даже если кука долгая).
 * Вместо этого — свой долгоживущий токен в куке (до года, "как на обычном
 * сайте"): валиден, пока не очистят куки/кэш в браузере или не выйдут сами.
 *
 * Защищает только сам реестр (index.php + все API-эндпоинты). Опубликованные
 * страницы участников и template.json — статические файлы, эта проверка их
 * не касается.
 */

declare(strict_types=1);

require_once __DIR__ . '/storage.php';

const AUTH_USERS_FILE    = __DIR__ . '/../data/users.json';
const AUTH_SESSIONS_FILE = __DIR__ . '/../data/sessions.json';
const AUTH_COOKIE_NAME   = 'cmk_webinar_auth';
const AUTH_COOKIE_DAYS   = 365;

function auth_users(): array {
    return json_read(AUTH_USERS_FILE, []);
}

function auth_has_any_user(): bool {
    return count(auth_users()) > 0;
}

function auth_find_user(array $users, string $username): ?array {
    foreach ($users as $u) {
        if (strcasecmp((string)($u['username'] ?? ''), $username) === 0) return $u;
    }
    return null;
}

function auth_save_user(string $username, string $password, string $displayName = ''): bool {
    $users = auth_users();
    if (auth_find_user($users, $username) !== null) return false; // уже существует
    $users[] = [
        'username'     => $username,
        'displayName'  => $displayName !== '' ? $displayName : $username,
        'passwordHash' => password_hash($password, PASSWORD_BCRYPT),
        'createdAt'    => date('c'),
    ];
    return json_write(AUTH_USERS_FILE, $users);
}

function auth_remove_user(string $username): bool {
    $users = auth_users();
    $filtered = array_values(array_filter($users, fn($u) => strcasecmp((string)($u['username'] ?? ''), $username) !== 0));
    if (count($filtered) === count($users)) return false;
    return json_write(AUTH_USERS_FILE, $filtered);
}

function auth_change_password(string $username, string $newPassword): bool {
    $users = auth_users();
    $found = false;
    foreach ($users as &$u) {
        if (strcasecmp((string)($u['username'] ?? ''), $username) === 0) {
            $u['passwordHash'] = password_hash($newPassword, PASSWORD_BCRYPT);
            $found = true;
            break;
        }
    }
    unset($u);
    if (!$found) return false;
    return json_write(AUTH_USERS_FILE, $users);
}

/** Проверяет логин/пароль, при успехе возвращает данные пользователя (без хэша). */
function auth_verify_login(string $username, string $password): ?array {
    $user = auth_find_user(auth_users(), $username);
    if ($user === null) return null;
    if (!password_verify($password, (string)($user['passwordHash'] ?? ''))) return null;
    return ['username' => $user['username'], 'displayName' => $user['displayName'] ?? $user['username']];
}

function auth_sessions(): array {
    return (array)json_read(AUTH_SESSIONS_FILE, []);
}

/** Создаёт долгоживущий токен, сохраняет его хэш на сервере и ставит куку. */
function auth_start_session(string $username): void {
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);

    $sessions = auth_sessions();
    $sessions[$tokenHash] = ['username' => $username, 'createdAt' => date('c')];
    json_write(AUTH_SESSIONS_FILE, $sessions);

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    setcookie(AUTH_COOKIE_NAME, $token, [
        'expires'  => time() + 60 * 60 * 24 * AUTH_COOKIE_DAYS,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function auth_end_session(): void {
    $token = $_COOKIE[AUTH_COOKIE_NAME] ?? '';
    if ($token !== '') {
        $tokenHash = hash('sha256', $token);
        $sessions = auth_sessions();
        unset($sessions[$tokenHash]);
        json_write(AUTH_SESSIONS_FILE, $sessions);
    }
    setcookie(AUTH_COOKIE_NAME, '', ['expires' => time() - 3600, 'path' => '/']);
}

/** Текущий залогиненный пользователь (username) или null. */
function auth_current_username(): ?string {
    $token = $_COOKIE[AUTH_COOKIE_NAME] ?? '';
    if ($token === '') return null;
    $tokenHash = hash('sha256', $token);
    $sessions = auth_sessions();
    return $sessions[$tokenHash]['username'] ?? null;
}

function auth_is_logged_in(): bool {
    return auth_current_username() !== null;
}

/** Для API-эндпоинтов: если не авторизован — отдаёт 401 JSON и завершает выполнение. */
function auth_require_json(): void {
    if (!auth_is_logged_in()) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'data' => null, 'error' => 'Требуется авторизация'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/** Для страниц (index.php): если не авторизован — редирект на страницу входа. */
function auth_require_page(string $loginUrl = 'login.php'): void {
    if (!auth_is_logged_in()) {
        header('Location: ' . $loginUrl);
        exit;
    }
}
