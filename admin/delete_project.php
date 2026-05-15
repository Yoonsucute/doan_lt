<?php
include '../config.php';
require_admin();

if (!hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'] ?? '')) {
    die('CSRF token khong hop le.');
}

$id = (int) ($_GET['id'] ?? 0);
db_query('DELETE FROM projects WHERE id = ?', [$id], 'i');
flash('Da xoa do an.');
redirect('projects.php');
