<?php
require_once dirname(__DIR__) . '/config.php';
require_login();
verify_csrf();

$projectId = (int) ($_POST['project_id'] ?? 0);
$star = max(1, min(5, (int) ($_POST['star'] ?? 5)));
$userId = (int) current_user()['id'];

$exists = db_one('SELECT id FROM ratings WHERE project_id = ? AND user_id = ?', [$projectId, $userId], 'ii');

if ($exists) {
    db_query('UPDATE ratings SET star = ? WHERE id = ?', [$star, (int) $exists['id']], 'ii');
} else {
    db_query('INSERT INTO ratings(project_id, user_id, star) VALUES(?, ?, ?)', [$projectId, $userId, $star], 'iii');
}

flash('Đã lưu đánh giá của bạn.');
redirect(base_url('projects/detail.php?id=' . $projectId));
