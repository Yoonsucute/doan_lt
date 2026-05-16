<?php
require_once dirname(__DIR__) . '/config.php';

$id = (int) ($_GET['id'] ?? 0);
db_query('UPDATE projects SET views = views + 1 WHERE id = ?', [$id], 'i');

$project = db_one(
    "SELECT projects.*, users.name, users.id AS author_id, categories.name AS category_name,
        COALESCE(AVG(ratings.star), 0) AS avg_star,
        COUNT(DISTINCT ratings.id) AS rating_count
     FROM projects
     JOIN users ON projects.user_id = users.id
     JOIN categories ON projects.category_id = categories.id
     LEFT JOIN ratings ON ratings.project_id = projects.id
     WHERE projects.id = ? AND (projects.status = 'approved' OR projects.user_id = ? OR ? = 1)
     GROUP BY projects.id",
    [$id, current_user()['id'] ?? 0, is_admin() ? 1 : 0],
    'iii'
);

if (!$project) {
    http_response_code(404);
    die('Không tìm thấy source.');
}

$comments = db_all(
    "SELECT comments.*, users.name,
        COALESCE(SUM(CASE WHEN comment_votes.vote = 1 THEN 1 ELSE 0 END), 0) AS likes,
        COALESCE(SUM(CASE WHEN comment_votes.vote = -1 THEN 1 ELSE 0 END), 0) AS dislikes
     FROM comments
     JOIN users ON comments.user_id = users.id
     LEFT JOIN comment_votes ON comment_votes.comment_id = comments.id
     WHERE comments.project_id = ?
     GROUP BY comments.id
     ORDER BY comments.id DESC",
    [$id],
    'i'
);

$bookmarked = current_user()
    ? db_one('SELECT id FROM bookmarks WHERE user_id = ? AND project_id = ?', [current_user()['id'], $id], 'ii')
    : null;

$isFree = (int) $project['is_free'] === 1 || ((int) $project['price'] === 0 && (int) $project['sale_price'] === 0);
$discount = discount_percent($project['price'] ?? 0, $project['sale_price'] ?? 0);
$finalPrice = project_price($project);
$pageTitle = $project['title'];
$metaTitle = $project['meta_title'] ?: $project['title'];
$plainDescription = strip_tags($project['short_description'] ?: $project['description']);
$metaDescription = function_exists('mb_substr') ? mb_substr($plainDescription, 0, 155) : substr($plainDescription, 0, 155);
?>
<?php include dirname(__DIR__) . '/includes/header.php'; ?>
<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>

<main class="container py-5 source-detail-page">
    <nav class="shop-breadcrumb mb-3">
        <a href="<?php echo e(base_url('index.php')); ?>">Trang chủ</a> /
        <a href="<?php echo e(base_url('projects/category.php?id=' . (int) $project['category_id'])); ?>"><?php echo e($project['category_name']); ?></a> /
        <span><?php echo e($project['title']); ?></span>
    </nav>

    <section class="detail-hero">
        <div class="detail-media">
            <img src="<?php echo e(base_url('uploads/images/' . $project['image'])); ?>" alt="<?php echo e($project['title']); ?>">
            <a class="demo-link" href="<?php echo e(base_url('projects/demo.php?id=' . (int) $project['id'])); ?>"><i class="fa-solid fa-play"></i> Demo nội bộ</a>
        </div>
        <aside class="detail-buybox">
            <div class="d-flex gap-2 flex-wrap mb-3">
                <span class="source-badge <?php echo $isFree ? 'source-badge-free' : 'source-badge-premium'; ?>"><?php echo $isFree ? 'Free' : ucfirst($project['tier']); ?></span>
                <?php if ((int) $project['is_hot'] === 1) { ?><span class="badge text-bg-warning">Hot</span><?php } ?>
                <?php if ((int) $project['is_featured'] === 1) { ?><span class="badge text-bg-info">Featured</span><?php } ?>
            </div>
            <h1><?php echo e($project['title']); ?></h1>
            <p><?php echo e($project['short_description'] ?: $project['description']); ?></p>
            <div class="detail-meta-grid">
                <span><i class="fa-solid fa-folder"></i> <?php echo e($project['category_name']); ?></span>
                <span><i class="fa-solid fa-user"></i> <?php echo e($project['name']); ?></span>
                <span><i class="fa-regular fa-eye"></i> <?php echo (int) $project['views']; ?> view</span>
                <span><i class="fa-solid fa-download"></i> <?php echo (int) ($project['downloads_count'] ?: $project['downloads']); ?> tai</span>
                <span><i class="fa-solid fa-star text-warning"></i> <?php echo number_format((float) $project['avg_star'], 1); ?> (<?php echo (int) $project['rating_count']; ?>)</span>
                <span><i class="fa-solid fa-calendar"></i> <?php echo e(date('d/m/Y', strtotime($project['created_at']))); ?></span>
            </div>

            <div class="detail-price-card">
                <?php if ($discount > 0) { ?><span class="sale-badge static-sale">-<?php echo $discount; ?>%</span><?php } ?>
                <?php if (!$isFree && (int) $project['price'] > 0 && (int) $project['sale_price'] > 0 && (int) $project['sale_price'] < (int) $project['price']) { ?>
                    <del><?php echo money($project['price']); ?></del>
                <?php } ?>
                <strong><?php echo $isFree ? 'Miễn phí' : money($finalPrice); ?></strong>
            </div>

            <div class="detail-actions">
                <?php if ($isFree) { ?>
                    <a class="btn btn-success btn-lg" href="<?php echo e(base_url('projects/download.php?id=' . (int) $project['id'])); ?>"><i class="fa-solid fa-download"></i> Tải miễn phí</a>
                <?php } else { ?>
                    <form method="POST" action="<?php echo e(base_url('shop/cart_action.php')); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="project_id" value="<?php echo (int) $project['id']; ?>">
                        <button class="btn btn-primary btn-lg"><i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ</button>
                    </form>
                    <form method="POST" action="<?php echo e(base_url('shop/cart_action.php')); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="project_id" value="<?php echo (int) $project['id']; ?>">
                        <input type="hidden" name="redirect_to" value="<?php echo e(base_url('shop/checkout.php')); ?>">
                        <button class="btn btn-success btn-lg"><i class="fa-solid fa-credit-card"></i> Mua ngay</button>
                    </form>
                <?php } ?>
                <?php if (current_user()) { ?>
                    <form method="POST" action="<?php echo e(base_url('actions/bookmark.php')); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="project_id" value="<?php echo (int) $project['id']; ?>">
                        <button class="btn btn-outline-primary btn-lg"><i class="fa-solid fa-bookmark"></i> <?php echo $bookmarked ? 'Đã lưu' : 'Lưu'; ?></button>
                    </form>
                <?php } ?>
            </div>
        </aside>
    </section>

    <section class="detail-tabs card mt-4">
        <div class="card-body">
            <ul class="nav nav-pills mb-4" role="tablist">
                <?php foreach ([
                    'desc' => 'Mô tả',
                    'features' => 'Chức năng',
                    'tech' => 'Công nghệ',
                    'install' => 'Hướng dẫn cài đặt',
                    'comments' => 'Bình luận',
                    'rating' => 'Đánh giá',
                ] as $key => $label) { ?>
                    <li class="nav-item"><button class="nav-link <?php echo $key === 'desc' ? 'active' : ''; ?>" data-bs-toggle="pill" data-bs-target="#tab-<?php echo $key; ?>" type="button"><?php echo $label; ?></button></li>
                <?php } ?>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="tab-desc">
                    <h3>Mô tả source</h3>
                    <p><?php echo nl2br(e($project['description'])); ?></p>
                    <p><a href="<?php echo e(base_url('projects/demo.php?id=' . (int) $project['id'])); ?>">Xem demo nội bộ của CodeDoAn</a></p>
                </div>
                <div class="tab-pane fade" id="tab-features">
                    <h3>Chức năng chinh</h3>
                    <p><?php echo nl2br(e($project['main_features'] ?: 'Quản lý dữ liệu, dang nhap, phan quyen, CRUD va cac chuc nang theo mo ta source.')); ?></p>
                </div>
                <div class="tab-pane fade" id="tab-tech">
                    <h3>Công nghệ su dung</h3>
                    <div class="detail-info-list">
                        <span><b>Tech stack:</b> <?php echo e($project['tech_stack'] ?: 'Đang cập nhật'); ?></span>
                        <span><b>Version:</b> <?php echo e($project['version'] ?: '1.0'); ?></span>
                        <span><b>File size:</b> <?php echo e($project['file_size'] ?: 'Đang cập nhật'); ?></span>
                        <span><b>Tier:</b> <?php echo e($project['tier']); ?></span>
                    </div>
                </div>
                <div class="tab-pane fade" id="tab-install">
                    <h3>Hướng dẫn cài đặt</h3>
                    <p><?php echo nl2br(e($project['install_guide'] ?: "1. Giải nén file source.\n2. Import database nếu có.\n3. Cấu hình kết nối database.\n4. Chạy trên XAMPP/localhost.")); ?></p>
                </div>
                <div class="tab-pane fade" id="tab-comments">
                    <?php if (current_user()) { ?>
                        <form method="POST" action="<?php echo e(base_url('actions/save_comment.php')); ?>" class="mb-4">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="project_id" value="<?php echo (int) $project['id']; ?>">
                            <textarea name="content" class="form-control mb-3" rows="3" placeholder="Nhập bình luận..." required></textarea>
                            <button class="btn btn-primary">Gửi bình luận</button>
                        </form>
                    <?php } else { ?>
                        <div class="alert alert-info">Đăng nhập de binh luan.</div>
                    <?php } ?>
                    <?php foreach ($comments as $c) { ?>
                        <div class="comment-item">
                            <strong><?php echo e($c['name']); ?></strong>
                            <small><?php echo e($c['created_at']); ?></small>
                            <p><?php echo nl2br(e($c['content'])); ?></p>
                            <?php if (current_user()) { ?>
                                <form method="POST" action="<?php echo e(base_url('actions/comment_vote.php')); ?>" class="d-inline-flex gap-2">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="comment_id" value="<?php echo (int) $c['id']; ?>">
                                    <input type="hidden" name="project_id" value="<?php echo (int) $project['id']; ?>">
                                    <button name="vote" value="1" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-thumbs-up"></i> <?php echo (int) $c['likes']; ?></button>
                                    <button name="vote" value="-1" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-thumbs-down"></i> <?php echo (int) $c['dislikes']; ?></button>
                                </form>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
                <div class="tab-pane fade" id="tab-rating">
                    <?php if (current_user()) { ?>
                        <form method="POST" action="<?php echo e(base_url('actions/rating.php')); ?>" class="detail-rating-form">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="project_id" value="<?php echo (int) $project['id']; ?>">
                            <select name="star" class="form-select">
                                <?php for ($i = 5; $i >= 1; $i--) { ?><option value="<?php echo $i; ?>"><?php echo $i; ?> sao</option><?php } ?>
                            </select>
                            <button class="btn btn-warning">Gửi đánh giá</button>
                        </form>
                        <hr>
                        <form method="POST" action="<?php echo e(base_url('actions/report_project.php')); ?>">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="project_id" value="<?php echo (int) $project['id']; ?>">
                            <textarea name="reason" class="form-control mb-3" rows="3" placeholder="Báo cáo source vi pham..."></textarea>
                            <button class="btn btn-outline-danger">Báo cáo source</button>
                        </form>
                    <?php } else { ?>
                        <div class="alert alert-info">Đăng nhập để đánh giá va báo cáo source.</div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
