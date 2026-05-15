<?php
require_once dirname(__DIR__) . '/config.php';

$action = $_POST['action'] ?? $_GET['action'] ?? 'add';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($action === 'add') {
    $projectId = (int) ($_POST['project_id'] ?? $_GET['project_id'] ?? 0);
    $project = db_one("SELECT id FROM projects WHERE id = ? AND status = 'approved'", [$projectId], 'i');
    if ($project) {
        $_SESSION['cart'][$projectId] = min(99, (int) ($_SESSION['cart'][$projectId] ?? 0) + 1);
        flash('Da them vao gio hang.');
    }
    $redirectTo = $_POST['redirect_to'] ?? ($_SERVER['HTTP_REFERER'] ?? 'cart.php');
    redirect($redirectTo);
}

if ($action === 'update') {
    foreach ($_POST['qty'] ?? [] as $projectId => $qty) {
        $projectId = (int) $projectId;
        $qty = max(0, min(99, (int) $qty));
        if ($qty === 0) {
            unset($_SESSION['cart'][$projectId]);
        } else {
            $_SESSION['cart'][$projectId] = $qty;
        }
    }
    flash('Da cap nhat gio hang.');
    redirect(base_url('shop/cart.php'));
}

if ($action === 'remove') {
    $projectId = (int) ($_GET['project_id'] ?? 0);
    unset($_SESSION['cart'][$projectId]);
    flash('Da xoa san pham khoi gio hang.');
    redirect(base_url('shop/cart.php'));
}

if ($action === 'clear') {
    $_SESSION['cart'] = [];
    flash('Da lam trong gio hang.');
    redirect(base_url('shop/cart.php'));
}

redirect(base_url('shop/cart.php'));
