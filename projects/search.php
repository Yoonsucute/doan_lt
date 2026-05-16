<?php
require_once dirname(__DIR__) . '/config.php';

$keyword = trim($_GET['keyword'] ?? '');
$categoryId = (int) ($_GET['category_id'] ?? 0);
$priceType = $_GET['price'] ?? '';
$tier = $_GET['tier'] ?? '';
$minPrice = max(0, (int) ($_GET['min_price'] ?? 0));
$maxPrice = max(0, (int) ($_GET['max_price'] ?? 0));
$sort = $_GET['sort'] ?? 'latest';
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

$where = ["projects.status = 'approved'"];
$params = [];
$types = '';

if ($keyword !== '') {
    $where[] = "(projects.title LIKE ? OR projects.short_description LIKE ? OR projects.description LIKE ? OR projects.tech_stack LIKE ? OR categories.name LIKE ?)";
    $like = '%' . $keyword . '%';
    array_push($params, $like, $like, $like, $like, $like);
    $types .= 'sssss';
}
if ($categoryId > 0) {
    $where[] = 'projects.category_id = ?';
    $params[] = $categoryId;
    $types .= 'i';
}
if ($priceType === 'free') {
    $where[] = "(projects.is_free = 1 OR (projects.price = 0 AND projects.sale_price = 0))";
}
if ($priceType === 'paid') {
    $where[] = "(projects.price > 0 OR projects.sale_price > 0)";
}
if (in_array($tier, ['basic', 'premium', 'exclusive'], true)) {
    $where[] = 'projects.tier = ?';
    $params[] = $tier;
    $types .= 's';
}
if ($minPrice > 0) {
    $where[] = "CASE WHEN projects.sale_price > 0 THEN projects.sale_price ELSE projects.price END >= ?";
    $params[] = $minPrice;
    $types .= 'i';
}
if ($maxPrice > 0) {
    $where[] = "CASE WHEN projects.sale_price > 0 THEN projects.sale_price ELSE projects.price END <= ?";
    $params[] = $maxPrice;
    $types .= 'i';
}

$orderBy = match ($sort) {
    'downloads' => 'projects.downloads_count DESC, projects.downloads DESC',
    'views' => 'projects.views DESC',
    'rating' => 'avg_star DESC',
    'price_asc' => 'final_price ASC',
    'price_desc' => 'final_price DESC',
    default => 'projects.id DESC',
};

$whereSql = implode(' AND ', $where);
$countRow = db_one(
    "SELECT COUNT(DISTINCT projects.id) AS total
     FROM projects
     JOIN categories ON categories.id = projects.category_id
     WHERE $whereSql",
    $params,
    $types
);
$total = (int) ($countRow['total'] ?? 0);
$totalPage = max(1, (int) ceil($total / $limit));

$projects = db_all(
    "SELECT projects.*, users.name AS author_name, categories.name AS category_name,
        COALESCE(AVG(ratings.star), 0) AS avg_star,
        COUNT(DISTINCT ratings.id) AS rating_count,
        CASE WHEN projects.sale_price > 0 THEN projects.sale_price ELSE projects.price END AS final_price
     FROM projects
     JOIN users ON users.id = projects.user_id
     JOIN categories ON categories.id = projects.category_id
     LEFT JOIN ratings ON ratings.project_id = projects.id
     WHERE $whereSql
     GROUP BY projects.id
     ORDER BY $orderBy, projects.id DESC
     LIMIT ?, ?",
    array_merge($params, [$offset, $limit]),
    $types . 'ii'
);

$categories = db_all('SELECT * FROM categories ORDER BY name');
$pageTitle = 'Danh sách source';
?>
<?php include dirname(__DIR__) . '/includes/header.php'; ?>
<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>

<main class="container py-5 marketplace-list">
    <div class="section-heading">
        <div>
            <span>CodeDoAn</span>
            <h1>Danh sách source code</h1>
            <p>Lọc source theo danh mục, giá, tier và độ phổ biến.</p>
        </div>
    </div>

    <form method="GET" class="filter-panel mb-4">
        <div>
            <label>Từ khóa</label>
            <input class="form-control" name="keyword" value="<?php echo e($keyword); ?>" placeholder="ban hang, quan ly kho, PHP...">
        </div>
        <div>
            <label>Danh mục</label>
            <select class="form-select" name="category_id">
                <option value="0">Tất cả</option>
                <?php foreach ($categories as $cat) { ?>
                    <option value="<?php echo (int) $cat['id']; ?>" <?php echo (int) $cat['id'] === $categoryId ? 'selected' : ''; ?>><?php echo e($cat['name']); ?></option>
                <?php } ?>
            </select>
        </div>
        <div>
            <label>Giá</label>
            <select class="form-select" name="price">
                <option value="">Tất cả</option>
                <option value="free" <?php echo $priceType === 'free' ? 'selected' : ''; ?>>Miễn phí</option>
                <option value="paid" <?php echo $priceType === 'paid' ? 'selected' : ''; ?>>Trả phí</option>
            </select>
        </div>
        <div>
            <label>Tier</label>
            <select class="form-select" name="tier">
                <option value="">Tất cả</option>
                <?php foreach (['basic', 'premium', 'exclusive'] as $item) { ?>
                    <option value="<?php echo $item; ?>" <?php echo $tier === $item ? 'selected' : ''; ?>><?php echo ucfirst($item); ?></option>
                <?php } ?>
            </select>
        </div>
        <div>
            <label>Giá tu</label>
            <input class="form-control" type="number" name="min_price" value="<?php echo $minPrice ?: ''; ?>" min="0" step="1000">
        </div>
        <div>
            <label>Đến</label>
            <input class="form-control" type="number" name="max_price" value="<?php echo $maxPrice ?: ''; ?>" min="0" step="1000">
        </div>
        <div>
            <label>Sắp xếp</label>
            <select class="form-select" name="sort">
                <option value="latest" <?php echo $sort === 'latest' ? 'selected' : ''; ?>>Mới nhất</option>
                <option value="downloads" <?php echo $sort === 'downloads' ? 'selected' : ''; ?>>Tải nhiều nhất</option>
                <option value="views" <?php echo $sort === 'views' ? 'selected' : ''; ?>>Xem nhiều nhất</option>
                <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Đánh giá cao</option>
                <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Giá thap den cao</option>
                <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Giá cao den thap</option>
            </select>
        </div>
        <div class="filter-actions">
            <button class="btn btn-primary w-100"><i class="fa-solid fa-filter"></i> Lọc source</button>
        </div>
    </form>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <strong><?php echo $total; ?> source phù hợp</strong>
        <a href="<?php echo e(base_url('projects/search.php')); ?>">Xóa bộ lọc</a>
    </div>

    <div class="source-grid">
        <?php foreach ($projects as $row) {
            $isFree = (int) $row['is_free'] === 1 || ((int) $row['price'] === 0 && (int) $row['sale_price'] === 0);
            $price = project_price($row);
        ?>
            <article class="source-card">
                <a class="source-thumb" href="<?php echo e(base_url('projects/detail.php?id=' . (int) $row['id'] . '&slug=' . $row['slug'])); ?>">
                    <img src="<?php echo e(base_url('uploads/images/' . $row['image'])); ?>" alt="<?php echo e($row['title']); ?>">
                    <span class="source-badge <?php echo $isFree ? 'source-badge-free' : 'source-badge-premium'; ?>"><?php echo $isFree ? 'Free' : ucfirst($row['tier']); ?></span>
                </a>
                <div class="source-body">
                    <a class="source-title" href="<?php echo e(base_url('projects/detail.php?id=' . (int) $row['id'] . '&slug=' . $row['slug'])); ?>"><?php echo e($row['title']); ?></a>
                    <p><?php echo e($row['short_description'] ?: $row['description']); ?></p>
                    <div class="source-tags"><span><?php echo e($row['category_name']); ?></span><span><?php echo e($row['tech_stack'] ?: 'Source code'); ?></span></div>
                    <div class="source-meta">
                        <span><i class="fa-solid fa-star"></i> <?php echo number_format((float) $row['avg_star'], 1); ?></span>
                        <span><i class="fa-regular fa-eye"></i> <?php echo (int) $row['views']; ?></span>
                        <span><i class="fa-solid fa-download"></i> <?php echo (int) ($row['downloads_count'] ?: $row['downloads']); ?></span>
                    </div>
                    <div class="source-bottom">
                        <div class="source-price"><strong><?php echo $isFree ? 'Miễn phí' : money($price); ?></strong></div>
                        <div class="source-actions">
                            <a class="btn btn-sm btn-outline-primary" href="<?php echo e(base_url('projects/detail.php?id=' . (int) $row['id'] . '&slug=' . $row['slug'])); ?>">Chi tiết</a>
                            <?php if ($isFree) { ?>
                                <a class="btn btn-sm btn-success" href="<?php echo e(base_url('projects/download.php?id=' . (int) $row['id'])); ?>">Tai</a>
                            <?php } else { ?>
                                <form method="POST" action="<?php echo e(base_url('shop/cart_action.php')); ?>">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="project_id" value="<?php echo (int) $row['id']; ?>">
                                    <button class="btn btn-sm btn-primary">Thêm giỏ</button>
                                </form>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </article>
        <?php } ?>
    </div>

    <?php if (!$projects) { ?><div class="empty-state mt-3">Khong tim thay source phù hợp.</div><?php } ?>

    <nav class="mt-4">
        <ul class="pagination">
            <?php for ($i = 1; $i <= $totalPage; $i++) {
                $query = $_GET;
                $query['page'] = $i;
            ?>
                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?<?php echo e(http_build_query($query)); ?>"><?php echo $i; ?></a>
                </li>
            <?php } ?>
        </ul>
    </nav>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
