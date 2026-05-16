<?php
include '../config.php';
require_admin();

if (!hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'] ?? '')) {
    die('CSRF token không hợp lệ.');
}

$id = (int) ($_GET['id'] ?? 0);
db_query("DELETE FROM users WHERE id = ? AND role <> 'admin'", [$id], 'i');
flash('Đã xóa user.');
redirect('users.php');
