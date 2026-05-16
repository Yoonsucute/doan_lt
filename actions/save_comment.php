<?php
require_once dirname(__DIR__) . '/config.php';
require_login();
verify_csrf();

$projectId = (int) ($_POST['project_id'] ?? 0);
$content = trim($_POST['content'] ?? '');

if ($projectId <= 0 || $content === '') {
    flash('Bình luận không hợp lệ.', 'danger');
    redirect(base_url('index.php'));
}

db_query(
    'INSERT INTO comments(project_id, user_id, content) VALUES(?, ?, ?)',
    [$projectId, current_user()['id'], $content],
    'iis'
);

$owner = db_one('SELECT user_id, title FROM projects WHERE id = ?', [$projectId], 'i');
if ($owner && (int) $owner['user_id'] !== (int) current_user()['id']) {
    db_query(
        'INSERT INTO notifications(user_id, message, link) VALUES(?, ?, ?)',
        [(int) $owner['user_id'], current_user()['name'] . ' da binh luan ve "' . $owner['title'] . '"', base_url('projects/detail.php?id=' . $projectId)],
        'iss'
    );
}

flash('Đã gửi bình luận.');
redirect(base_url('projects/detail.php?id=' . $projectId));
