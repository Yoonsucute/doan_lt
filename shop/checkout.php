<?php
require_once dirname(__DIR__) . '/config.php';
require_login();

$cart = cart_items();
if (!$cart) {
    flash('Gio hang dang trong.', 'warning');
    redirect(base_url('shop/cart.php'));
}

$ids = array_map('intval', array_keys($cart));
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$items = db_all(
    "SELECT id, title, slug, image, price, sale_price FROM projects WHERE status = 'approved' AND id IN ($placeholders)",
    $ids,
    str_repeat('i', count($ids))
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $note = trim($_POST['note'] ?? '');

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('Vui long nhap ten va email hop le.', 'danger');
    } else {
        $total = 0;
        foreach ($items as $item) {
            $total += project_price($item) * (int) ($cart[$item['id']] ?? 1);
        }

        db_query(
            "INSERT INTO orders(user_id, customer_name, customer_email, customer_phone, note, total, status)
             VALUES(?, ?, ?, ?, ?, ?, 'pending')",
            [current_user()['id'], $name, $email, $phone, $note, $total],
            'issssi'
        );
        $orderId = (int) mysqli_insert_id($conn);

        foreach ($items as $item) {
            $qty = (int) ($cart[$item['id']] ?? 1);
            db_query(
                'INSERT INTO order_items(order_id, project_id, title, price, quantity) VALUES(?, ?, ?, ?, ?)',
                [$orderId, (int) $item['id'], $item['title'], project_price($item), $qty],
                'iisii'
            );
        }

        $_SESSION['cart'] = [];
        flash('Da tao don hang. Admin se xac nhan va ho tro ban tai source.');
        redirect(base_url('shop/orders.php'));
    }
}

$pageTitle = 'Thanh toan';
?>
<?php include dirname(__DIR__) . '/includes/header.php'; ?>
<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>

<main class="container py-5">
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card p-4">
                <h3 class="mb-3">Thong tin thanh toan</h3>
                <form method="POST">
                    <?php echo csrf_field(); ?>
                    <label class="form-label">Ho ten</label>
                    <input type="text" name="name" class="form-control mb-3" value="<?php echo e(current_user()['name']); ?>" required>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control mb-3" value="<?php echo e(current_user()['email']); ?>" required>
                    <label class="form-label">So dien thoai</label>
                    <input type="text" name="phone" class="form-control mb-3">
                    <label class="form-label">Ghi chu</label>
                    <textarea name="note" class="form-control mb-4" rows="4" placeholder="Noi dung can ho tro, zalo, yeu cau cai dat..."></textarea>
                    <button class="btn btn-success w-100">Dat hang</button>
                </form>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="shop-sidebar">
                <h5>Don hang cua ban</h5>
                <?php foreach ($items as $item) {
                    $qty = (int) ($cart[$item['id']] ?? 1);
                    $price = project_price($item);
                ?>
                    <div class="checkout-line">
                        <span><?php echo e($item['title']); ?> x <?php echo $qty; ?></span>
                        <strong><?php echo money($price * $qty); ?></strong>
                    </div>
                <?php } ?>
                <div class="checkout-total">
                    <span>Tong cong</span>
                    <strong><?php echo money(cart_total()); ?></strong>
                </div>
                <p class="text-muted small mt-3 mb-0">Sau khi admin xac nhan, don hang se duoc cap nhat trang thai va ban co the tai source trong lich su don hang.</p>
            </div>
        </div>
    </div>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
