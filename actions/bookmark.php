<?php
require_once dirname(__DIR__) . '/config.php';
require_login();
verify_csrf();

$projectId = (int) ($_POST['project_id'] ?? 0);
$userId = (int) current_user()['id'];

$exists = db_one('SELECT id FROM bookmarks WHERE user_id = ? AND project_id = ?', [$userId, $projectId], 'ii');
if ($exists) {
    db_query('DELETE FROM bookmarks WHERE id = ?', [(int) $exists['id']], 'i');
    flash('Da bo bookmark.');
} else {
    db_query('INSERT INTO bookmarks(user_id, project_id) VALUES(?, ?)', [$userId, $projectId], 'ii');
    flash('Da bookmark do an.');
}

redirect(base_url('projects/detail.php?id=' . $projectId));
