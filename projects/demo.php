<?php
require_once dirname(__DIR__) . '/config.php';

$id = (int) ($_GET['id'] ?? 0);
$project = db_one(
    "SELECT projects.*, categories.name AS category_name, users.name AS author_name
     FROM projects
     JOIN categories ON categories.id = projects.category_id
     JOIN users ON users.id = projects.user_id
     WHERE projects.id = ? AND projects.status = 'approved'
     LIMIT 1",
    [$id],
    'i'
);

if (!$project) {
    http_response_code(404);
    die('Không tìm thấy demo source.');
}

$pageTitle = 'Demo - ' . $project['title'];
?>
<?php include dirname(__DIR__) . '/includes/header.php'; ?>
<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>

<main class="container py-5">
    <section class="demo-preview">
        <div class="demo-preview-screen">
            <div class="demo-topbar">
                <span></span><span></span><span></span>
                <strong><?php echo e($project['title']); ?></strong>
            </div>
            <div class="demo-body">
                <aside>
                    <b><?php echo e($project['category_name']); ?></b>
                    <a>Dashboard</a>
                    <a>Quản lý dữ liệu</a>
                    <a>Báo cáo</a>
                    <a>Cài đặt</a>
                </aside>
                <div>
                    <h1><?php echo e($project['title']); ?></h1>
                    <p><?php echo e($project['short_description'] ?: $project['description']); ?></p>
                    <div class="demo-stat-grid">
                        <article><span>Tech stack</span><strong><?php echo e($project['tech_stack'] ?: 'Đang cập nhật'); ?></strong></article>
                        <article><span>Version</span><strong><?php echo e($project['version'] ?: '1.0'); ?></strong></article>
                        <article><span>Tác giả</span><strong><?php echo e($project['author_name']); ?></strong></article>
                    </div>
                    <div class="demo-table">
                        <div><b>Module</b><b>Trạng thái</b></div>
                        <div><span>Đăng nhập va phan quyen</span><span>Hoàn thành</span></div>
                        <div><span>CRUD du lieu chinh</span><span>Hoàn thành</span></div>
                        <div><span>Giáo dien responsive</span><span>Hoàn thành</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4 d-flex gap-2 flex-wrap">
            <a class="btn btn-primary" href="<?php echo e(base_url('projects/detail.php?id=' . (int) $project['id'] . '&slug=' . $project['slug'])); ?>">Quay lại chi tiết</a>
            <a class="btn btn-success" href="<?php echo e(base_url('projects/download.php?id=' . (int) $project['id'])); ?>">Tải source</a>
        </div>
    </section>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
