<?php
require_once __DIR__ . '/bootstrap.php';
$user = current_user();
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt=db()->prepare('SELECT data_json FROM workspace_state WHERE workspace_id=? LIMIT 1');
    $stmt->execute([$user['workspace_id']]);
    $row=$stmt->fetch();
    $data=$row ? json_decode($row['data_json'], true) : [];
    respond(['ok'=>true,'data'=>is_array($data)?$data:[],'csrf'=>csrf_token()]);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('Method not allowed.', 405);
require_csrf();
$body=json_body();
$data=$body['data'] ?? null;
if (!is_array($data)) fail('Invalid workspace data.');
$json=json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
$stmt=db()->prepare('INSERT INTO workspace_state (workspace_id,data_json,updated_by) VALUES (?,?,?) ON DUPLICATE KEY UPDATE data_json=VALUES(data_json), updated_by=VALUES(updated_by), updated_at=CURRENT_TIMESTAMP');
$stmt->execute([$user['workspace_id'],$json,(int)$user['id']]);
respond(['ok'=>true]);
