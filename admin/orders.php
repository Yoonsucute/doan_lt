<?php
include '../config.php';
require_admin();

$orders = db_all(
    "SELECT orders.*, users.email AS account_email
     FROM orders
     JOIN users ON users.id = orders.user_id
     ORDER BY orders.id DESC"
);

$itemsByOrder = [];
if ($orders) {
    $ids = array_map('intval', array_column($orders, 'id'));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $items = db_all(
        "SELECT * FROM order_items WHERE order_id IN ($placeholders) ORDER BY id",
        $ids,
        str_repeat('i', count($ids))
    );
    foreach ($items as $item) {
        $itemsByOrder[$item['order_id']][] = $item;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Quản lý đơn hàng</h2>
        <a class="btn btn-outline-secondary" href="dashboard.php">Dashboard</a>
    </div>

    <?php foreach ($orders as $order) { ?>
        <div class="card p-4 mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                <div>
                    <h4>Don #<?php echo (int) $order['id']; ?> - <?php echo e($order['customer_name']); ?></h4>
                    <div class="text-muted"><?php echo e($order['customer_email']); ?> | <?php echo e($order['customer_phone']); ?></div>
                    <div class="text-muted"><?php echo e($order['created_at']); ?></div>
                </div>
                <form method="POST" action="order_status.php" class="d-flex gap-2 align-items-start">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="id" value="<?php echo (int) $order['id']; ?>">
                    <select name="status" class="form-select form-select-sm">
                        <?php foreach (['pending', 'paid', 'completed', 'cancelled'] as $status) { ?>
                            <option value="<?php echo $status; ?>" <?php echo $order['status'] === $status ? 'selected' : ''; ?>><?php echo $status; ?></option>
                        <?php } ?>
                    </select>
                    <button class="btn btn-primary btn-sm">Lưu</button>
                </form>
            </div>
            <div class="fw-bold mb-2">Tổng: <?php echo money($order['total']); ?></div>
            <?php if ($order['note']) { ?><div class="alert alert-light"><?php echo e($order['note']); ?></div><?php } ?>
            <div class="table-responsive">
                <table class="table">
                    <tr><th>Source</th><th>Giá</th><th>SL</th></tr>
                    <?php foreach ($itemsByOrder[$order['id']] ?? [] as $item) { ?>
                        <tr>
                            <td><?php echo e($item['title']); ?></td>
                            <td><?php echo money($item['price']); ?></td>
                            <td><?php echo (int) $item['quantity']; ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    <?php } ?>

    <?php if (!$orders) { ?><div class="shop-empty">Chưa có đơn hàng nào.</div><?php } ?>
</main>
</body>
</html>
