<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
ini_set('session.use_strict_mode', '1');
session_name('winnerhc_hr_session');
session_set_cookie_params([
    'lifetime' => 0, 'path' => '/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true, 'samesite' => 'Lax',
]);
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
function json_body(): array {
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') return [];
    $data = json_decode($raw, true);
    if (!is_array($data)) fail('Invalid JSON request.', 400);
    return $data;
}
function respond(array $data, int $status = 200): never {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}
function fail(string $message, int $status = 400): never { respond(['ok'=>false,'error'=>$message], $status); }
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function require_csrf(): void {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!$token || !hash_equals(csrf_token(), $token)) fail('Invalid security token. Please log in again.', 403);
}
function current_user(): array {
    if (empty($_SESSION['user_id'])) fail('Please log in.', 401);
    $stmt = db()->prepare('SELECT id, workspace_id, full_name, username, role, perms FROM users WHERE id = ? AND active = 1 LIMIT 1');
    $stmt->execute([(int)$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user) fail('User account is not active.', 401);
    $user['perms'] = json_decode($user['perms'] ?: '[]', true) ?: [];
    return $user;
}
function require_admin(): array {
    $user = current_user();
    if ($user['role'] !== 'Admin') fail('Admin access required.', 403);
    return $user;
}
