<?php
require_once __DIR__ . '/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('Method not allowed.', 405);
$body = json_body();
$username = strtolower(trim((string)($body['username'] ?? '')));
$password = (string)($body['password'] ?? '');
if ($username === '' || $password === '') fail('Username and password are required.');
$stmt = db()->prepare('SELECT id, workspace_id, full_name, username, role, perms, password_hash FROM users WHERE LOWER(username) = ? AND active = 1 LIMIT 1');
$stmt->execute([$username]);
$user = $stmt->fetch();
if (!$user || !password_verify($password, $user['password_hash'])) fail('Invalid username or password.', 401);
session_regenerate_id(true);
$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['csrf'] = bin2hex(random_bytes(32));
respond(['ok'=>true,'csrf'=>$_SESSION['csrf'],'user'=>[
    'id'=>(int)$user['id'],'full_name'=>$user['full_name'],'username'=>$user['username'],
    'role'=>$user['role'],'perms'=>json_decode($user['perms'] ?: '[]', true) ?: []
]]);
