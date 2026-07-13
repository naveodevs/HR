<?php
require_once __DIR__ . '/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('Method not allowed.', 405);
require_csrf();
$user=current_user();
$body=json_body();
$password=(string)($body['password'] ?? '');
if (strlen($password)<8) fail('Password must be at least 8 characters.');
$stmt=db()->prepare('UPDATE users SET password_hash=? WHERE id=?');
$stmt->execute([password_hash($password,PASSWORD_DEFAULT),(int)$user['id']]);
respond(['ok'=>true]);
