<?php
require_once dirname(__DIR__) . '/config.php';
require_login();

if (!hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'] ?? '')) {
    die('CSRF token khong hop le.');
}

$id = (int) ($_GET['id'] ?? 0);
$project = db_one('SELECT * FROM projects WHERE id = ?', [$id], 'i');

if ($project && ((int) $project['user_id'] === (int) current_user()['id'] || is_admin())) {
    db_query('DELETE FROM projects WHERE id = ?', [$id], 'i');
    flash('Da xoa do an.');
}

redirect(base_url('auth/profile.php'));
