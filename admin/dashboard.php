<?php
include '../config.php';
require_admin();

$totalUsers = (int) (db_one('SELECT COUNT(*) AS total FROM users')['total'] ?? 0);
$totalProjects = (int) (db_one('SELECT COUNT(*) AS total FROM projects')['total'] ?? 0);
$totalComments = (int) (db_one('SELECT COUNT(*) AS total FROM comments')['total'] ?? 0);
$totalDownloads = (int) (db_one('SELECT COALESCE(SUM(downloads_count), SUM(downloads), 0) AS total FROM projects')['total'] ?? 0);
$pending = (int) (db_one("SELECT COUNT(*) AS total FROM projects WHERE status = 'pending'")['total'] ?? 0);
$reports = (int) (db_one("SELECT COUNT(*) AS total FROM reports WHERE status = 'open'")['total'] ?? 0);
$ordersCount = (int) (db_one('SELECT COUNT(*) AS total FROM orders')['total'] ?? 0);
$revenue = (int) (db_one("SELECT COALESCE(SUM(total), 0) AS total FROM orders WHERE status IN ('paid','completed')")['total'] ?? 0);
$monthly = db_all("SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS total FROM projects GROUP BY month ORDER BY month DESC LIMIT 6");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="d-flex">
    <aside class="admin-sidebar p-4" style="width:260px;">
        <h3 class="text-white mb-4">ADMIN</h3>
        <a class="d-block mb-3" href="dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
        <a class="d-block mb-3" href="projects.php"><i class="fa-solid fa-folder"></i> Quản lý đồ án</a>
        <a class="d-block mb-3" href="categories.php"><i class="fa-solid fa-layer-group"></i> Quản lý danh mục</a>
        <a class="d-block mb-3" href="orders.php"><i class="fa-solid fa-cart-shopping"></i> Quản lý đơn hàng</a>
        <a class="d-block mb-3" href="comments.php"><i class="fa-solid fa-comments"></i> Quản lý bình luận</a>
        <a class="d-block mb-3" href="reports.php"><i class="fa-solid fa-flag"></i> Quản lý báo cáo</a>
        <a class="d-block mb-3" href="users.php"><i class="fa-solid fa-users"></i> Quản lý user</a>
        <a class="d-block text-danger" href="../auth/logout.php">Đăng xuất</a>
    </aside>
    <main class="flex-grow-1 p-4">
        <h2 class="mb-4">Dashboard</h2>
        <div class="row">
            <?php foreach ([
                ['User', $totalUsers, 'users'],
                ['Do an', $totalProjects, 'folder'],
                ['Bình luận', $totalComments, 'comments'],
                ['Download', $totalDownloads, 'download'],
                ['Chờ duyệt', $pending, 'clock'],
                ['Báo cáo mở', $reports, 'flag'],
                ['Đơn hàng', $ordersCount, 'cart-shopping'],
                ['Doanh thu', $revenue, 'sack-dollar'],
            ] as $stat) { ?>
                <div class="col-md-4 col-xl-2 mb-4">
                    <div class="card p-3">
                        <div class="text-muted"><i class="fa-solid fa-<?php echo $stat[2]; ?>"></i> <?php echo $stat[0]; ?></div>
                        <h2 class="mb-0"><?php echo $stat[1]; ?></h2>
                    </div>
                </div>
            <?php } ?>
        </div>
        <div class="card p-4">
            <h5>Thống kê bài đăng theo tháng</h5>
            <canvas id="projectChart" height="95"></canvas>
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('projectChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_reverse(array_column($monthly, 'month'))); ?>,
        datasets: [{label: 'Do an', data: <?php echo json_encode(array_reverse(array_map('intval', array_column($monthly, 'total')))); ?>, backgroundColor: '#2563eb'}]
    }
});
</script>
</body>
</html>
