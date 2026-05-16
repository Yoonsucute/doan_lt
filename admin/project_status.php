<?php
include '../config.php';
require_admin();
verify_csrf();

$id = (int) ($_POST['id'] ?? 0);
$status = $_POST['status'] ?? 'pending';
$allowedStatus = ['pending', 'approved', 'rejected', 'hidden'];
if (!in_array($status, $allowedStatus, true)) {
    $status = 'pending';
}

$tier = $_POST['tier'] ?? 'basic';
$allowedTier = ['basic', 'premium', 'exclusive'];
if (!in_array($tier, $allowedTier, true)) {
    $tier = 'basic';
}

$isFeatured = isset($_POST['is_featured']) ? 1 : 0;
$isHot = isset($_POST['is_hot']) ? 1 : 0;

$project = db_one('SELECT user_id, title FROM projects WHERE id = ?', [$id], 'i');
if ($project) {
    db_query(
        'UPDATE projects SET status = ?, tier = ?, is_featured = ?, is_hot = ? WHERE id = ?',
        [$status, $tier, $isFeatured, $isHot, $id],
        'ssiii'
    );
    db_query(
        'INSERT INTO notifications(user_id, message, link) VALUES(?, ?, ?)',
        [(int) $project['user_id'], 'Source "' . $project['title'] . '" da cap nhat trang thai: ' . $status, base_url('projects/detail.php?id=' . $id)],
        'iss'
    );
    flash('Đã cập nhật source.');
}

redirect('projects.php');
