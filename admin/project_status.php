<?php
include '../config.php';
require_admin();
verify_csrf();

$id = (int) ($_POST['id'] ?? 0);
$status = ($_POST['status'] ?? '') === 'approved' ? 'approved' : 'pending';

$project = db_one('SELECT user_id, title FROM projects WHERE id = ?', [$id], 'i');
if ($project) {
    db_query('UPDATE projects SET status = ? WHERE id = ?', [$status, $id], 'si');
    db_query('INSERT INTO notifications(user_id, message, link) VALUES(?, ?, ?)', [(int) $project['user_id'], 'Do an "' . $project['title'] . '" da duoc cap nhat trang thai: ' . $status, base_url('projects/detail.php?id=' . $id)], 'iss');
    flash('Da cap nhat trang thai.');
}

redirect('projects.php');
