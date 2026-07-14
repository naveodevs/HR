<?php
require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    fail('Method not allowed.', 405);
}

$user = current_user();

respond([
    'ok' => true,
    'csrf' => csrf_token(),
    'user' => [
        'id' => (int)$user['id'],
        'full_name' => $user['full_name'],
        'username' => $user['username'],
        'role' => $user['role'],
        'perms' => $user['perms'],
    ],
]);
