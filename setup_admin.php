<?php
declare(strict_types=1);
require_once __DIR__ . '/api/config.php';
header('Content-Type: text/html; charset=utf-8');
$message = '';
try {
    $count = (int)db()->query("SELECT COUNT(*) FROM users WHERE role='Admin' AND active=1")->fetchColumn();
    if ($count > 0) {
        $message = 'An active Admin already exists. Delete setup_admin.php from the server.';
    } else {
        $stmt = db()->prepare('INSERT INTO users (workspace_id, full_name, username, password_hash, role, perms, active) VALUES (?, ?, ?, ?, ?, ?, 1)');
        $stmt->execute([
            WORKSPACE_ID, 'Administrator', 'admin',
            password_hash('Admin@1234', PASSWORD_DEFAULT),
            'Admin', json_encode(['dash','tasks','rec','expiry','kpi','master','leave'])
        ]);
        $message = 'Admin created. Username: admin | Password: Admin@1234. Delete setup_admin.php now and change the password after login.';
    }
} catch (Throwable $e) {
    $message = 'Setup failed: ' . htmlspecialchars($e->getMessage());
}
?><!doctype html><html><head><meta charset="utf-8"><title>WinnerHC Setup</title>
<style>body{font-family:Arial,sans-serif;background:#f4f6fa;padding:40px}.box{max-width:760px;margin:auto;background:#fff;padding:28px;border-radius:12px;border:1px solid #ddd}code{background:#eee;padding:3px 6px}</style></head>
<body><div class="box"><h1>WinnerHC HR Ops Setup</h1><p><?= $message ?></p><p><strong>Security:</strong> delete <code>setup_admin.php</code> after successful setup.</p></div></body></html>
