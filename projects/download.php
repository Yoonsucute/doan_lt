<?php
require_once dirname(__DIR__) . '/config.php';

$id = (int) ($_GET['id'] ?? 0);
$project = db_one("SELECT * FROM projects WHERE id = ? AND status = 'approved'", [$id], 'i');

if (!$project) {
    http_response_code(404);
    die('Khong tim thay file.');
}

db_query('UPDATE projects SET downloads = downloads + 1 WHERE id = ?', [$id], 'i');

$file = dirname(__DIR__) . '/uploads/files/' . basename($project['source_file']);
if (!is_file($file)) {
    http_response_code(404);
    die('File khong ton tai.');
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
