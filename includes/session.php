<?php
// Tempo maximo de inatividade da sessao: 30 minutos.
define('SESSION_TIMEOUT_SECONDS', 1800);

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();

    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
    }
}

function is_session_expired(): bool
{
    if (!isset($_SESSION['last_activity'])) {
        return false;
    }

    return (time() - (int) $_SESSION['last_activity']) > SESSION_TIMEOUT_SECONDS;
}

function touch_session_activity(): void
{
    $_SESSION['last_activity'] = time();
}

function login_user(string $username): void
{
    start_secure_session();
    session_regenerate_id(true);
    $_SESSION['usuario'] = $username;
    touch_session_activity();
}

// DESTAQUE: guarda o perfil no momento do login para controle de permissao.
function login_user_with_role(string $username, string $role, int $userId = 0): void
{
    start_secure_session();
    session_regenerate_id(true);
    $_SESSION['usuario'] = $username;
    $_SESSION['perfil'] = $role;
    if ($userId > 0) {
        $_SESSION['usuario_id'] = $userId;
    }
    touch_session_activity();
}

function logout_user(): void
{
    start_secure_session();

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool) $params['secure'], (bool) $params['httponly']);
    }

    session_destroy();
}

function require_authenticated_user(): void
{
    start_secure_session();

    if (is_session_expired()) {
        logout_user();
        header('Location: frontend.php?mensagem=' . urlencode('Sua sessao expirou. Faca login novamente.'));
        exit;
    }

    if (!isset($_SESSION['usuario']) || $_SESSION['usuario'] === '') {
        header('Location: frontend.php');
        exit;
    }

    touch_session_activity();
}

// DESTAQUE: acesso restrito ao perfil admin.
function require_admin_user(): void
{
    require_authenticated_user();

    $perfil = $_SESSION['perfil'] ?? 'usuario';
    if ($perfil !== 'admin') {
        header('Location: acessoNegado.php?mensagem=' . urlencode('Acesso permitido apenas para administrador.'));
        exit;
    }
}
