<?php
require_once dirname(__DIR__) . '/config.php';
require_login();
verify_csrf();

$projectId = (int) ($_POST['project_id'] ?? 0);
$reason = trim($_POST['reason'] ?? '');

if ($projectId > 0 && $reason !== '') {
    db_query(
        'INSERT INTO reports(project_id, user_id, reason) VALUES(?, ?, ?)',
        [$projectId, current_user()['id'], $reason],
        'iis'
    );
    flash('Da gui report. Admin se kiem tra som.');
}

redirect(base_url('projects/detail.php?id=' . $projectId));
