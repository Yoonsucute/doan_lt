<?php
require_once dirname(__DIR__) . '/config.php';

$id = (int) ($_GET['id'] ?? 0);
$project = db_one("SELECT * FROM projects WHERE id = ? AND status = 'approved'", [$id], 'i');

if (!$project) {
    http_response_code(404);
    die('Khong tim thay file.');
}

if (!current_user()) {
    flash('Vui long dang nhap de tai source.', 'warning');
    redirect(base_url('auth/login.php'));
}

$isFree = (int) ($project['is_free'] ?? 0) === 1 || ((int) $project['price'] === 0 && (int) $project['sale_price'] === 0);
$canDownload = $isFree || is_admin() || (current_user() && (int) current_user()['id'] === (int) $project['user_id']);

if (!$canDownload && current_user()) {
    $order = db_one(
        "SELECT orders.id
         FROM orders
         JOIN order_items ON order_items.order_id = orders.id
         WHERE orders.user_id = ?
           AND order_items.project_id = ?
           AND orders.status IN ('paid','completed')
         LIMIT 1",
        [current_user()['id'], $id],
        'ii'
    );
    $canDownload = (bool) $order;
}

if (!$canDownload) {
    flash('Source tra phi can mua va duoc admin xac nhan paid truoc khi tai.', 'warning');
    redirect(base_url('projects/detail.php?id=' . $id));
}

db_query('UPDATE projects SET downloads = downloads + 1, downloads_count = downloads_count + 1 WHERE id = ?', [$id], 'i');
db_query(
    'INSERT INTO downloads(user_id, project_id, ip_address) VALUES(?, ?, ?)',
    [(int) current_user()['id'], $id, $_SERVER['REMOTE_ADDR'] ?? 'CLI'],
    'iis'
);

$file = dirname(__DIR__) . '/uploads/files/' . basename($project['source_file']);
if (!is_file($file)) {
    http_response_code(404);
    die('File không tồn tại.');
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file));
readfile($file);
exit;
