<?php
require_once dirname(__DIR__) . '/config.php';

$cart = cart_items();
$items = [];
if ($cart) {
    $ids = array_map('intval', array_keys($cart));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $items = db_all(
        "SELECT id, title, slug, image, price, sale_price FROM projects WHERE status = 'approved' AND id IN ($placeholders)",
        $ids,
        str_repeat('i', count($ids))
    );
}

$pageTitle = 'Giỏ hàng';
?>
<?php include dirname(__DIR__) . '/includes/header.php'; ?>
<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>

<main class="container py-5">
    <div class="shop-page-head mb-4">
        <h1>Giỏ hàng</h1>
        <p>Kiem tra source code truoc khi tao don hang.</p>
    </div>

    <?php if (!$items) { ?>
        <div class="shop-empty">
            Giỏ hàng dang trong. <a href="<?php echo e(base_url('index.php')); ?>">Quay lai cua hang</a>
        </div>
    <?php } else { ?>
        <form method="POST" action="<?php echo e(base_url('shop/cart_action.php')); ?>" class="card p-3">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="update">
            <div class="table-responsive">
                <table class="table align-middle cart-table">
                    <tr>
                        <th>San pham</th>
                        <th>Giá</th>
                        <th style="width:120px;">So luong</th>
                        <th>Tam tinh</th>
                        <th></th>
                    </tr>
                    <?php foreach ($items as $item) {
                        $qty = (int) ($cart[$item['id']] ?? 1);
                        $price = project_price($item);
                    ?>
                        <tr>
                            <td>
                                <div class="cart-product">
                                    <img src="<?php echo e(base_url('uploads/images/' . $item['image'])); ?>" alt="">
                                    <a href="<?php echo e(base_url('projects/detail.php?id=' . (int) $item['id'] . '&slug=' . $item['slug'])); ?>"><?php echo e($item['title']); ?></a>
                                </div>
                            </td>
                            <td><?php echo money($price); ?></td>
                            <td><input type="number" name="qty[<?php echo (int) $item['id']; ?>]" value="<?php echo $qty; ?>" min="0" max="99" class="form-control form-control-sm"></td>
                            <td><strong><?php echo money($price * $qty); ?></strong></td>
                            <td><a class="btn btn-sm btn-outline-danger" href="<?php echo e(base_url('shop/cart_action.php?action=remove&project_id=' . (int) $item['id'])); ?>">Xoa</a></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <a href="<?php echo e(base_url('shop/cart_action.php?action=clear')); ?>" class="btn btn-outline-secondary">Làm trống giỏ hàng</a>
                <div class="d-flex flex-column flex-md-row gap-2 align-items-md-center">
                    <strong>Tổng: <?php echo money(cart_total()); ?></strong>
                    <button class="btn btn-outline-primary">Cap nhat gio</button>
                    <a href="<?php echo e(base_url('shop/checkout.php')); ?>" class="btn btn-success">Tiến hành thanh toán</a>
                </div>
            </div>
        </form>
    <?php } ?>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
