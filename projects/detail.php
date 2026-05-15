<?php
require_once dirname(__DIR__) . '/config.php';

$id = (int) ($_GET['id'] ?? 0);
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
    die('Khong tim thay do an.');
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

$following = current_user()
    ? db_one('SELECT id FROM follows WHERE follower_id = ? AND following_id = ?', [current_user()['id'], $project['author_id']], 'ii')
    : null;
$discount = discount_percent($project['price'] ?? 0, $project['sale_price'] ?? 0);
$finalPrice = project_price($project);

$pageTitle = $project['title'];
$metaTitle = $project['meta_title'] ?: $project['title'];
$plainDescription = strip_tags($project['description']);
$metaDescription = function_exists('mb_substr') ? mb_substr($plainDescription, 0, 155) : substr($plainDescription, 0, 155);
?>
<?php include dirname(__DIR__) . '/includes/header.php'; ?>
<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>

<main class="container py-4">
    <div class="row g-4">
        <div class="col-lg-8">
            <article class="card">
                <img class="project-cover" style="height: 420px;" src="<?php echo e(base_url('uploads/images/' . $project['image'])); ?>" alt="<?php echo e($project['title']); ?>">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge text-bg-primary"><?php echo e($project['category_name']); ?></span>
                        <span class="badge text-bg-warning"><i class="fa-solid fa-star"></i> <?php echo number_format((float) $project['avg_star'], 1); ?> (<?php echo (int) $project['rating_count']; ?>)</span>
                        <span class="badge text-bg-secondary"><?php echo e($project['status']); ?></span>
                    </div>
                    <h1 class="fw-bold"><?php echo e($project['title']); ?></h1>
                    <p class="text-muted mb-4">
                        <i class="fa-solid fa-user"></i> <?php echo e($project['name']); ?>
                        <span class="ms-2"><i class="fa-solid fa-calendar"></i> <?php echo e($project['created_at']); ?></span>
                    </p>
                    <div class="mb-4"><?php echo nl2br(e($project['description'])); ?></div>
                    <div class="d-flex flex-wrap gap-2">
                        <form method="POST" action="<?php echo e(base_url('shop/cart_action.php')); ?>">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="project_id" value="<?php echo (int) $project['id']; ?>">
                            <button class="btn btn-dark">
                                <i class="fa-solid fa-cart-plus"></i> Them vao gio hang
                            </button>
                        </form>
                        <form method="POST" action="<?php echo e(base_url('shop/cart_action.php')); ?>">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="project_id" value="<?php echo (int) $project['id']; ?>">
                            <input type="hidden" name="redirect_to" value="<?php echo e(base_url('shop/checkout.php')); ?>">
                            <button class="btn btn-success">
                                <i class="fa-solid fa-credit-card"></i> Mua ngay
                            </button>
                        </form>
                        <?php if (current_user()) { ?>
                                <form method="POST" action="<?php echo e(base_url('actions/bookmark.php')); ?>">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="project_id" value="<?php echo (int) $project['id']; ?>">
                                <button class="btn btn-outline-primary">
                                    <i class="fa-solid fa-bookmark"></i> <?php echo $bookmarked ? 'Bo bookmark' : 'Bookmark'; ?>
                                </button>
                            </form>
                            <?php if ((int) current_user()['id'] !== (int) $project['author_id']) { ?>
                                <form method="POST" action="<?php echo e(base_url('actions/follow.php')); ?>">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="user_id" value="<?php echo (int) $project['author_id']; ?>">
                                    <input type="hidden" name="project_id" value="<?php echo (int) $project['id']; ?>">
                                    <button class="btn btn-outline-secondary">
                                        <i class="fa-solid fa-user-plus"></i> <?php echo $following ? 'Bo follow' : 'Follow tac gia'; ?>
                                    </button>
                                </form>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
            </article>

            <section class="card mt-4">
                <div class="card-body p-4">
                    <h4 class="mb-3">Binh luan</h4>
                    <?php if (current_user()) { ?>
                        <form method="POST" action="<?php echo e(base_url('actions/save_comment.php')); ?>" class="mb-4">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="project_id" value="<?php echo (int) $project['id']; ?>">
                            <textarea name="content" class="form-control mb-3" rows="3" placeholder="Nhap binh luan..." required></textarea>
                            <button class="btn btn-primary" name="comment">Gui binh luan</button>
                        </form>
                    <?php } ?>

                    <?php foreach ($comments as $c) { ?>
                        <div class="border-top py-3">
                            <div class="fw-bold"><?php echo e($c['name']); ?></div>
                            <div class="text-muted small"><?php echo e($c['created_at']); ?></div>
                            <div class="mt-2"><?php echo nl2br(e($c['content'])); ?></div>
                            <?php if (current_user()) { ?>
                                <form method="POST" action="<?php echo e(base_url('actions/comment_vote.php')); ?>" class="d-inline-flex gap-2 mt-2">
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
            </section>
        </div>

        <aside class="col-lg-4">
            <div class="shop-sidebar mb-4">
                <h5>Gia source</h5>
                <?php if ($discount > 0) { ?>
                    <span class="sale-badge static-sale">-<?php echo $discount; ?>%</span>
                <?php } ?>
                <div class="product-price detail-price">
                    <?php if ((int) ($project['price'] ?? 0) > 0 && (int) ($project['sale_price'] ?? 0) > 0 && (int) $project['sale_price'] < (int) $project['price']) { ?>
                        <del><?php echo money($project['price']); ?></del>
                    <?php } ?>
                    <strong><?php echo money($finalPrice); ?></strong>
                </div>
                <form method="POST" action="<?php echo e(base_url('shop/cart_action.php')); ?>" class="mt-3">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="project_id" value="<?php echo (int) $project['id']; ?>">
                    <button class="btn btn-dark w-100"><i class="fa-solid fa-cart-plus"></i> Them vao gio hang</button>
                </form>
                <a href="<?php echo e(base_url('shop/cart.php')); ?>" class="btn btn-outline-success w-100 mt-2">Xem gio hang</a>
            </div>

            <div class="card mb-4">
                <div class="card-body p-4">
                    <h5>Thong tin do an</h5>
                    <p class="mb-2"><i class="fa-solid fa-folder"></i> Danh muc: <b><?php echo e($project['category_name']); ?></b></p>
                    <p class="mb-2"><i class="fa-solid fa-download"></i> Luot tai: <b><?php echo (int) $project['downloads']; ?></b></p>
                    <p class="mb-0"><i class="fa-solid fa-link"></i> Slug: <b><?php echo e($project['slug']); ?></b></p>
                </div>
            </div>

            <?php if (current_user()) { ?>
                <div class="card mb-4">
                    <div class="card-body p-4">
                        <h5>Danh gia</h5>
                        <form method="POST" action="<?php echo e(base_url('actions/rating.php')); ?>">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="project_id" value="<?php echo (int) $project['id']; ?>">
                            <select name="star" class="form-select mb-3">
                                <?php for ($i = 5; $i >= 1; $i--) { ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?> sao</option>
                                <?php } ?>
                            </select>
                            <button class="btn btn-warning w-100" name="rating">Danh gia</button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body p-4">
                        <h5>Report bai viet</h5>
                        <form method="POST" action="<?php echo e(base_url('actions/report_project.php')); ?>">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="project_id" value="<?php echo (int) $project['id']; ?>">
                            <textarea name="reason" class="form-control mb-3" rows="3" placeholder="Ly do report..." required></textarea>
                            <button class="btn btn-outline-danger w-100">Gui report</button>
                        </form>
                    </div>
                </div>
            <?php } ?>
        </aside>
    </div>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
