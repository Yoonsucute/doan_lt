<?php
include '../config.php';
require_admin();
verify_csrf();

$id = (int) ($_POST['id'] ?? 0);
$status = $_POST['status'] ?? 'pending';
$allowed = ['pending', 'completed', 'cancelled'];
if (!in_array($status, $allowed, true)) {
    $status = 'pending';
}

$order = db_one('SELECT user_id FROM orders WHERE id = ?', [$id], 'i');
if ($order) {
    db_query('UPDATE orders SET status = ? WHERE id = ?', [$status, $id], 'si');
    db_query(
        'INSERT INTO notifications(user_id, message, link) VALUES(?, ?, ?)',
        [(int) $order['user_id'], 'Don hang #' . $id . ' da cap nhat trang thai: ' . $status, 'orders.php'],
        'iss'
    );
    flash('Da cap nhat don hang.');
}

redirect('orders.php');
