<?php
require_once dirname(__DIR__) . '/config.php';

$categoryId = (int) ($_GET['id'] ?? 0);
$sort = $_GET['sort'] ?? 'newest';
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = 12;
$start = ($page - 1) * $limit;

$category = db_one('SELECT * FROM categories WHERE id = ?', [$categoryId], 'i');
if (!$category) {
    http_response_code(404);
    die('Không tìm thấy danh mục.');
}

$orderBy = match ($sort) {
    'popular' => 'projects.downloads DESC, projects.id DESC',
    'price_asc' => 'final_price ASC, projects.id DESC',
    'price_desc' => 'final_price DESC, projects.id DESC',
    'rating' => 'avg_star DESC, projects.id DESC',
    default => 'projects.id DESC',
};

$total = (int) (db_one(
    "SELECT COUNT(*) AS total FROM projects WHERE status = 'approved' AND category_id = ?",
    [$categoryId],
    'i'
)['total'] ?? 0);
$totalPage = max(1, (int) ceil($total / $limit));
$from = $total ? $start + 1 : 0;
$to = min($start + $limit, $total);

$projects = db_all(
    "SELECT projects.*, users.name,
        COALESCE(AVG(ratings.star), 0) AS avg_star,
        COUNT(DISTINCT bookmarks.id) AS bookmark_count,
        CASE
            WHEN projects.sale_price > 0 THEN projects.sale_price
            WHEN projects.price > 0 THEN projects.price
            ELSE 0
        END AS final_price
     FROM projects
     JOIN users ON users.id = projects.user_id
     LEFT JOIN ratings ON ratings.project_id = projects.id
     LEFT JOIN bookmarks ON bookmarks.project_id = projects.id
     WHERE projects.status = 'approved' AND projects.category_id = ?
     GROUP BY projects.id
     ORDER BY $orderBy
     LIMIT ?, ?",
    [$categoryId, $start, $limit],
    'iii'
);

$categories = db_all(
    "SELECT categories.*, COUNT(projects.id) AS project_count
     FROM categories
     LEFT JOIN projects ON projects.category_id = categories.id AND projects.status = 'approved'
     GROUP BY categories.id
     ORDER BY categories.name"
);

$pageTitle = $category['name'] . ' - Code do an';
$metaTitle = $category['name'] . ' - Code do an';
$metaDescription = 'Danh mục source code ' . $category['name'] . ' co gio hang, gia ban, loc va sap xep.';
?>
<?php include dirname(__DIR__) . '/includes/header.php'; ?>

<header class="codedoan-header">
    <div class="codedoan-top">
        <div class="container d-flex flex-column flex-lg-row justify-content-between gap-2">
            <div class="d-flex flex-wrap gap-4">
                <span><i class="fa-regular fa-envelope"></i> codedoan.com@gmail.com</span>
                <span><i class="fa-regular fa-clock"></i> Hỗ trợ cài đặt: 09h-21h</span>
                <span><i class="fa-solid fa-phone"></i> 0384972984</span>
                <a href="<?php echo e(base_url('index.php')); ?>">Blog ve lap trinh</a>
            </div>
            <a class="login-pill" href="<?php echo e(current_user() ? base_url('auth/profile.php') : base_url('auth/login.php')); ?>">
                <?php echo current_user() ? e(current_user()['name']) : 'Đăng nhập / Đăng ký'; ?>
            </a>
        </div>
    </div>

    <div class="codedoan-main">
        <div class="container">
            <div class="codedoan-main-row">
                <a class="codedoan-logo" href="<?php echo e(base_url('index.php')); ?>">
                    <span class="logo-mark">C</span>
                    <span>CODEDOAN.COM</span>
                </a>

                <form action="<?php echo e(base_url('projects/search.php')); ?>" method="GET" class="codedoan-search">
                    <input type="text" name="keyword" placeholder="Nhap tu khoa can tim..." value="<?php echo e($_GET['keyword'] ?? ''); ?>">
                    <button><i class="fa-solid fa-magnifying-glass"></i></button>
                    <div class="install-tooltip">Hỗ trợ cài đặt 09h-21h | Hỗ trợ cài đặt hoàn toàn miễn phí</div>
                </form>

                <a class="header-action" href="<?php echo e(base_url('shop/orders.php')); ?>">
                    <span><i class="fa-regular fa-clipboard"></i></span>
                    Theo doi don hang
                </a>
                <a class="header-action" href="<?php echo e(base_url('shop/orders.php')); ?>">
                    <span><i class="fa-regular fa-clipboard"></i></span>
                    Theo doi don hang
                </a>
                <a class="cart-pill" href="<?php echo e(base_url('shop/cart.php')); ?>">
                    <span>Giỏ hàng /</span>
                    <strong><?php echo money(cart_total()); ?></strong>
                    <i class="fa-solid fa-cart-shopping"></i>
                </a>
            </div>

            <div class="codedoan-service-row">
                <a class="category-trigger" href="#shop-sidebar"><i class="fa-solid fa-list"></i> Danh mục san pham</a>
                <div class="service-mini"><span>100%</span><b>CODE PHONG PHU</b><em>Day du cac the loai...</em></div>
                <div class="service-mini"><span>100%</span><b>CODE CHẤT LƯỢNG</b><em>Cam kết hỗ trợ cài đặt</em></div>
                <div class="service-mini"><span>24h</span><b>HO TRO 24/24</b><em>Giáo dich tu dong</em></div>
                <div class="service-mini"><span><i class="fa-solid fa-shield-halved"></i></span><b>THANH TOAN</b><em>Thanh toán an toan bao mat</em></div>
            </div>
        </div>
    </div>
</header>

<main class="codedoan-page">
    <div class="container">
        <div class="shop-breadcrumb">
            <a href="<?php echo e(base_url('index.php')); ?>">Trang chủ</a>
            <span>/</span>
            <a href="<?php echo e(base_url('index.php')); ?>">San pham</a>
            <span>/</span>
            <strong><?php echo e($category['name']); ?></strong>
        </div>

        <div class="codedoan-layout">
            <aside class="codedoan-sidebar" id="shop-sidebar">
                <h3>DANH MUC SAN PHAM</h3>
                <?php foreach ($categories as $cat) { ?>
                    <div class="sidebar-group <?php echo (int) $cat['id'] === $categoryId ? 'open' : ''; ?>">
                        <a class="sidebar-main-link" href="<?php echo e(base_url('projects/category.php?id=' . (int) $cat['id'] . '&slug=' . slugify($cat['name']))); ?>">
                            <?php echo e($cat['name']); ?>
                            <i class="fa-solid fa-chevron-<?php echo (int) $cat['id'] === $categoryId ? 'up' : 'down'; ?>"></i>
                        </a>
                        <?php if ((int) $cat['id'] === $categoryId) { ?>
                            <div class="sidebar-sub">
                                <a href="<?php echo e(base_url('projects/category.php?id=' . (int) $categoryId . '&sort=newest')); ?>">Code free</a>
                                <a href="<?php echo e(base_url('projects/category.php?id=' . (int) $categoryId . '&sort=popular')); ?>">Du lich</a>
                                <a href="<?php echo e(base_url('projects/category.php?id=' . (int) $categoryId . '&sort=price_desc')); ?>">Quan ly ban hang</a>
                                <a href="<?php echo e(base_url('projects/category.php?id=' . (int) $categoryId . '&sort=rating')); ?>">Quan ly khac</a>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </aside>

            <section class="codedoan-products">
                <div class="codedoan-toolbar">
                    <span>Hien thi <?php echo $from; ?>-<?php echo $to; ?> cua <?php echo $total; ?> ket qua</span>
                    <form method="GET">
                        <input type="hidden" name="id" value="<?php echo (int) $categoryId; ?>">
                        <select name="sort" onchange="this.form.submit()">
                            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Sắp xếp theo mới nhất</option>
                            <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Sắp xếp theo pho bien</option>
                            <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Giá thap den cao</option>
                            <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Giá cao den thap</option>
                            <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Đánh giá cao</option>
                        </select>
                    </form>
                </div>

                <div class="codedoan-grid">
                    <?php foreach ($projects as $row) {
                        $discount = discount_percent($row['price'], $row['sale_price']);
                        $finalPrice = project_price($row);
                    ?>
                        <article class="codedoan-card">
                            <a class="codedoan-thumb" href="<?php echo e(base_url('projects/detail.php?id=' . (int) $row['id'] . '&slug=' . $row['slug'])); ?>">
                                <?php if ($discount > 0) { ?><span>-<?php echo $discount; ?>%</span><?php } ?>
                                <img src="<?php echo e(base_url('uploads/images/' . $row['image'])); ?>" alt="<?php echo e($row['title']); ?>">
                            </a>
                            <a class="codedoan-title" href="<?php echo e(base_url('projects/detail.php?id=' . (int) $row['id'] . '&slug=' . $row['slug'])); ?>">
                                <?php echo e($row['title']); ?>
                            </a>
                            <div class="codedoan-price">
                                <?php if ((int) $row['price'] > 0 && (int) $row['sale_price'] > 0 && (int) $row['sale_price'] < (int) $row['price']) { ?>
                                    <del><?php echo money($row['price']); ?></del>
                                <?php } ?>
                                <strong><?php echo money($finalPrice); ?></strong>
                            </div>
                            <form method="POST" action="<?php echo e(base_url('shop/cart_action.php')); ?>">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="project_id" value="<?php echo (int) $row['id']; ?>">
                                <button><i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ</button>
                            </form>
                        </article>
                    <?php } ?>
                </div>

                <?php if (!$projects) { ?>
                    <div class="codedoan-empty">Danh mục này chưa có sản phẩm nào.</div>
                <?php } ?>

                <nav class="mt-4">
                    <ul class="pagination shop-pagination">
                        <?php for ($i = 1; $i <= $totalPage; $i++) { ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo e(base_url('projects/category.php?id=' . (int) $categoryId . '&sort=' . $sort . '&page=' . $i)); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php } ?>
                    </ul>
                </nav>
            </section>
        </div>
    </div>
</main>

<div class="floating-tools">
    <button title="Code"><i class="fa-solid fa-code"></i></button>
    <a href="<?php echo e(base_url('index.php')); ?>" title="Chat"><i class="fa-regular fa-comments"></i></a>
    <a href="tel:0384972984" title="Call"><i class="fa-solid fa-phone"></i></a>
</div>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
