<?php
require_once dirname(__DIR__) . '/config.php';
require_login();
verify_csrf();

$commentId = (int) ($_POST['comment_id'] ?? 0);
$projectId = (int) ($_POST['project_id'] ?? 0);
$vote = (int) ($_POST['vote'] ?? 0);
$vote = $vote === 1 ? 1 : -1;
$userId = (int) current_user()['id'];

$exists = db_one('SELECT id FROM comment_votes WHERE comment_id = ? AND user_id = ?', [$commentId, $userId], 'ii');
if ($exists) {
    db_query('UPDATE comment_votes SET vote = ? WHERE id = ?', [$vote, (int) $exists['id']], 'ii');
} else {
    db_query('INSERT INTO comment_votes(comment_id, user_id, vote) VALUES(?, ?, ?)', [$commentId, $userId, $vote], 'iii');
}

redirect(base_url('projects/detail.php?id=' . $projectId));
