<?php
require_once dirname(__DIR__) . '/config.php';
require_login();

$orders = db_all(
    'SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC',
    [current_user()['id']],
    'i'
);

$itemsByOrder = [];
if ($orders) {
    $ids = array_map('intval', array_column($orders, 'id'));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $rows = db_all(
        "SELECT order_items.*, projects.slug, projects.source_file
         FROM order_items
         LEFT JOIN projects ON projects.id = order_items.project_id
         WHERE order_id IN ($placeholders)
         ORDER BY id",
        $ids,
        str_repeat('i', count($ids))
    );
    foreach ($rows as $row) {
        $itemsByOrder[$row['order_id']][] = $row;
    }
}

$pageTitle = 'Don hang';
?>
<?php include dirname(__DIR__) . '/includes/header.php'; ?>
<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>

<main class="container py-5">
    <div class="shop-page-head mb-4">
        <h1>Don hang cua toi</h1>
        <p>Theo doi trang thai don hang va tai source sau khi duoc xac nhan.</p>
    </div>

    <?php if (!$orders) { ?>
        <div class="shop-empty">Ban chua co don hang nao.</div>
    <?php } ?>

    <?php foreach ($orders as $order) { ?>
        <div class="card p-4 mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
                <div>
                    <h4 class="mb-1">Don hang #<?php echo (int) $order['id']; ?></h4>
                    <div class="text-muted"><?php echo e($order['created_at']); ?></div>
                </div>
                <div class="text-md-end">
                    <span class="badge text-bg-<?php echo $order['status'] === 'completed' ? 'success' : 'warning'; ?>"><?php echo e($order['status']); ?></span>
                    <div class="fw-bold mt-1"><?php echo money($order['total']); ?></div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <tr><th>Source</th><th>Gia</th><th>SL</th><th>Tai file</th></tr>
                    <?php foreach ($itemsByOrder[$order['id']] ?? [] as $item) { ?>
                        <tr>
                            <td><a href="<?php echo e(base_url('projects/detail.php?id=' . (int) $item['project_id'] . '&slug=' . $item['slug'])); ?>"><?php echo e($item['title']); ?></a></td>
                            <td><?php echo money($item['price']); ?></td>
                            <td><?php echo (int) $item['quantity']; ?></td>
                            <td>
                                <?php if ($order['status'] === 'completed') { ?>
                                    <a class="btn btn-sm btn-success" href="<?php echo e(base_url('projects/download.php?id=' . (int) $item['project_id'])); ?>">Download</a>
                                <?php } else { ?>
                                    <span class="text-muted">Cho xac nhan</span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    <?php } ?>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
