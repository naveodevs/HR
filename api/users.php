<?php
require_once __DIR__ . '/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $me = current_user();
    $stmt = db()->prepare('SELECT id, full_name, username, role, perms FROM users WHERE workspace_id = ? AND active = 1 ORDER BY full_name');
    $stmt->execute([$me['workspace_id']]);
    $users = $stmt->fetchAll();
    foreach ($users as &$u) { $u['id']=(int)$u['id']; $u['perms']=json_decode($u['perms'] ?: '[]', true) ?: []; }
    respond(['ok'=>true,'users'=>$users,'csrf'=>csrf_token()]);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('Method not allowed.', 405);
require_csrf();
$admin = require_admin();
$body = json_body();
$action = (string)($body['action'] ?? '');
if ($action === 'create') {
    $fullName=trim((string)($body['fullName'] ?? ''));
    $username=strtolower(trim((string)($body['username'] ?? '')));
    $password=(string)($body['password'] ?? '');
    $role=(($body['role'] ?? 'Member') === 'Admin') ? 'Admin' : 'Member';
    $perms=is_array($body['perms'] ?? null) ? array_values($body['perms']) : [];
    if ($fullName==='' || $username==='' || $password==='') fail('Full name, username and password are required.');
    if (!preg_match('/^[a-z0-9._-]+$/', $username)) fail('Invalid username format.');
    if (strlen($password)<8) fail('Password must be at least 8 characters.');
    $check=db()->prepare('SELECT id FROM users WHERE LOWER(username)=? LIMIT 1');
    $check->execute([$username]);
    if ($check->fetch()) fail('That username already exists.', 409);
    if ($role==='Admin') $perms=['dash','tasks','rec','expiry','kpi','master','leave'];
    $stmt=db()->prepare('INSERT INTO users (workspace_id,full_name,username,password_hash,role,perms,active) VALUES (?,?,?,?,?,?,1)');
    $stmt->execute([$admin['workspace_id'],$fullName,$username,password_hash($password,PASSWORD_DEFAULT),$role,json_encode($perms)]);
    respond(['ok'=>true,'userId'=>(int)db()->lastInsertId()]);
}
if ($action === 'permissions') {
    $userId=(int)($body['userId'] ?? 0);
    $perms=is_array($body['perms'] ?? null) ? array_values($body['perms']) : [];
    $stmt=db()->prepare("UPDATE users SET perms=? WHERE id=? AND workspace_id=? AND role='Member'");
    $stmt->execute([json_encode($perms),$userId,$admin['workspace_id']]);
    respond(['ok'=>true]);
}
if ($action === 'delete') {
    $userId=(int)($body['userId'] ?? 0);
    if ($userId===(int)$admin['id']) fail('You cannot remove your own account.');
    $stmt=db()->prepare('SELECT role FROM users WHERE id=? AND workspace_id=? LIMIT 1');
    $stmt->execute([$userId,$admin['workspace_id']]);
    $target=$stmt->fetch();
    if (!$target) fail('User not found.', 404);
    if ($target['role']==='Admin') {
        $count=db()->prepare("SELECT COUNT(*) FROM users WHERE workspace_id=? AND role='Admin' AND active=1");
        $count->execute([$admin['workspace_id']]);
        if ((int)$count->fetchColumn()<=1) fail('Cannot remove the last Admin.');
    }
    $del=db()->prepare('UPDATE users SET active=0 WHERE id=? AND workspace_id=?');
    $del->execute([$userId,$admin['workspace_id']]);
    respond(['ok'=>true]);
}
fail('Unsupported action.');
