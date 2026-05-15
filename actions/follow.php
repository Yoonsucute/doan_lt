<?php
require_once dirname(__DIR__) . '/config.php';
require_login();
verify_csrf();

$followingId = (int) ($_POST['user_id'] ?? 0);
$projectId = (int) ($_POST['project_id'] ?? 0);
$followerId = (int) current_user()['id'];

if ($followingId > 0 && $followingId !== $followerId) {
    $exists = db_one('SELECT id FROM follows WHERE follower_id = ? AND following_id = ?', [$followerId, $followingId], 'ii');
    if ($exists) {
        db_query('DELETE FROM follows WHERE id = ?', [(int) $exists['id']], 'i');
        flash('Da bo follow tac gia.');
    } else {
        db_query('INSERT INTO follows(follower_id, following_id) VALUES(?, ?)', [$followerId, $followingId], 'ii');
        db_query('INSERT INTO notifications(user_id, message, link) VALUES(?, ?, ?)', [$followingId, current_user()['name'] . ' da follow ban.', base_url('auth/profile.php')], 'iss');
        flash('Da follow tac gia.');
    }
}

redirect(base_url('projects/detail.php?id=' . $projectId));
