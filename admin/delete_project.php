<?php
include '../config.php';
require_admin();

if (!hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'] ?? '')) {
    die('CSRF token không hợp lệ.');
}

$id = (int) ($_GET['id'] ?? 0);
db_query('DELETE FROM projects WHERE id = ?', [$id], 'i');
flash('Đã xóa đồ án.');
redirect('projects.php');
