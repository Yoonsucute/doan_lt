<?php
include 'config.php';

$stats = [
    'sources' => (int) (db_one("SELECT COUNT(*) AS total FROM projects WHERE status = 'approved'")['total'] ?? 0),
    'downloads' => (int) (db_one("SELECT COALESCE(SUM(downloads_count), SUM(downloads), 0) AS total FROM projects")['total'] ?? 0),
    'users' => (int) (db_one('SELECT COUNT(*) AS total FROM users')['total'] ?? 0),
    'categories' => (int) (db_one('SELECT COUNT(*) AS total FROM categories')['total'] ?? 0),
];

$categories = db_all(
    "SELECT categories.*, COUNT(projects.id) AS project_count
     FROM categories
     LEFT JOIN projects ON projects.category_id = categories.id AND projects.status = 'approved'
     GROUP BY categories.id
     ORDER BY project_count DESC, categories.name
     LIMIT 8"
);

$baseSelect = "SELECT projects.*, users.name AS author_name, categories.name AS category_name,
    COALESCE(AVG(ratings.star), 0) AS avg_star,
    COUNT(DISTINCT ratings.id) AS rating_count";
$baseFrom = " FROM projects
    JOIN users ON users.id = projects.user_id
    JOIN categories ON categories.id = projects.category_id
    LEFT JOIN ratings ON ratings.project_id = projects.id
    WHERE projects.status = 'approved'";
$baseGroup = " GROUP BY projects.id";

$featured = db_all($baseSelect . $baseFrom . " AND (projects.is_featured = 1 OR projects.is_hot = 1)" . $baseGroup . " ORDER BY projects.is_hot DESC, projects.downloads_count DESC, projects.id DESC LIMIT 6");
$latest = db_all($baseSelect . $baseFrom . $baseGroup . " ORDER BY projects.id DESC LIMIT 6");
$freeSources = db_all($baseSelect . $baseFrom . " AND (projects.is_free = 1 OR (projects.price = 0 AND projects.sale_price = 0))" . $baseGroup . " ORDER BY projects.id DESC LIMIT 6");
$premiumSources = db_all($baseSelect . $baseFrom . " AND (projects.price > 0 OR projects.sale_price > 0 OR projects.tier IN ('premium','exclusive'))" . $baseGroup . " ORDER BY projects.is_hot DESC, projects.id DESC LIMIT 6");
$categorySources = db_all($baseSelect . $baseFrom . $baseGroup . " ORDER BY projects.is_hot DESC, projects.is_featured DESC, projects.id DESC LIMIT 48");

if (!$featured) {
    $featured = $latest;
}

function source_card(array $row): void
{
    $isFree = (int) ($row['is_free'] ?? 0) === 1 || ((int) ($row['price'] ?? 0) === 0 && (int) ($row['sale_price'] ?? 0) === 0);
    $tier = $isFree ? 'Free' : ucfirst($row['tier'] ?: 'premium');
    $badgeClass = $isFree ? 'source-badge-free' : (($row['tier'] ?? '') === 'exclusive' ? 'source-badge-exclusive' : 'source-badge-premium');
    $price = project_price($row);
    $short = $row['short_description'] ?: $row['description'];
    ?>
    <article class="source-card home-source-card" data-category-id="<?php echo (int) ($row['category_id'] ?? 0); ?>" data-category-name="<?php echo e($row['category_name'] ?? ''); ?>">
        <a class="source-thumb" href="<?php echo e(base_url('projects/detail.php?id=' . (int) $row['id'] . '&slug=' . $row['slug'])); ?>">
            <img src="<?php echo e(base_url('uploads/images/' . $row['image'])); ?>" alt="<?php echo e($row['title']); ?>">
            <span class="source-badge <?php echo e($badgeClass); ?>"><?php echo e($tier); ?></span>
            <?php if ((int) ($row['is_hot'] ?? 0) === 1) { ?><span class="source-hot">Hot</span><?php } ?>
        </a>
        <div class="source-body">
            <a class="source-title" href="<?php echo e(base_url('projects/detail.php?id=' . (int) $row['id'] . '&slug=' . $row['slug'])); ?>"><?php echo e($row['title']); ?></a>
            <p><?php echo e($short); ?></p>
            <div class="source-tags">
                <span><?php echo e($row['category_name']); ?></span>
                <?php if (!empty($row['tech_stack'])) { ?><span><?php echo e($row['tech_stack']); ?></span><?php } ?>
            </div>
            <div class="source-meta">
                <span><i class="fa-solid fa-star"></i> <?php echo number_format((float) $row['avg_star'], 1); ?></span>
                <span><i class="fa-regular fa-eye"></i> <?php echo (int) ($row['views'] ?? 0); ?></span>
                <span><i class="fa-solid fa-download"></i> <?php echo (int) (($row['downloads_count'] ?? 0) ?: ($row['downloads'] ?? 0)); ?></span>
            </div>
            <div class="source-bottom">
                <div class="source-price">
                    <?php if (!$isFree && (int) ($row['price'] ?? 0) > 0 && (int) ($row['sale_price'] ?? 0) > 0 && (int) $row['sale_price'] < (int) $row['price']) { ?>
                        <del><?php echo money($row['price']); ?></del>
                    <?php } ?>
                    <strong><?php echo $isFree ? 'Miễn phí' : money($price); ?></strong>
                </div>
                <div class="source-actions">
                    <a class="btn btn-sm btn-outline-primary" href="<?php echo e(base_url('projects/detail.php?id=' . (int) $row['id'] . '&slug=' . $row['slug'])); ?>">Chi tiết</a>
                    <?php if ($isFree) { ?>
                        <a class="btn btn-sm btn-success" href="<?php echo e(base_url('projects/download.php?id=' . (int) $row['id'])); ?>">Tải miễn phí</a>
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
            <div class="source-author">Đăng bởi <?php echo e($row['author_name']); ?></div>
        </div>
    </article>
    <?php
}

$pageTitle = 'CodeDoAn - Kho source code và đồ án lập trình';
$metaDescription = 'CodeDoAn là website chia sẻ và mua bán source code, đồ án lập trình cho sinh viên và lập trình viên.';
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<main class="home-page">
    <section class="home-hero">
        <div class="container">
            <div class="home-hero-grid">
                <div>
                    <span class="hero-kicker">Marketplace source code cho sinh viên</span>
                    <h1>Kho source code & đồ án lập trình chất lượng</h1>
                    <p>Tìm kiếm, tải về và chia sẻ hàng ngàn source code PHP, Laravel, Java, Python, Flutter, ASP.NET và nhiều nền tảng khác.</p>
                    <form action="<?php echo e(base_url('projects/search.php')); ?>" method="GET" class="hero-search">
                        <input type="text" name="keyword" placeholder="Tìm source bán hàng PHP MySQL, quản lý kho, khách sạn...">
                        <button><i class="fa-solid fa-magnifying-glass"></i> Tìm source</button>
                    </form>
                    <div class="hero-actions">
                        <a class="btn-gradient" href="#featured-sources">Khám phá source</a>
                        <a class="btn-soft" href="<?php echo e(base_url('projects/upload.php')); ?>">Đăng source của bạn</a>
                    </div>
                </div>
                <div class="hero-panel">
                    <div class="hero-code-window">
                        <div class="dots"><span></span><span></span><span></span></div>
                        <pre><code>CodeDoAn::find('website ban hang')
    ->filter('PHP & MySQL')
    ->sortBy('rating')
    ->download();</code></pre>
                    </div>
                    <div class="hero-floating-card">
                        <strong><?php echo $stats['sources']; ?>+</strong>
                        <span>source đã duyệt</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container home-stats">
        <?php foreach ([
            ['Tổng source', $stats['sources'], 'folder-open'],
            ['Lượt tải', $stats['downloads'], 'download'],
            ['Thành viên', $stats['users'], 'users'],
            ['Danh mục', $stats['categories'], 'layer-group'],
        ] as $stat) { ?>
            <div class="stat-card">
                <i class="fa-solid fa-<?php echo $stat[2]; ?>"></i>
                <strong><?php echo number_format($stat[1]); ?></strong>
                <span><?php echo e($stat[0]); ?></span>
            </div>
        <?php } ?>
    </section>

    <section class="container section-block">
        <div class="section-heading">
            <div>
                <span>Danh mục nổi bật</span>
                <h2>Tìm nhanh theo công nghệ và đề tài</h2>
            </div>
        </div>
        <div class="category-grid">
            <?php foreach ($categories as $cat) { ?>
                <button class="category-tile js-category-filter" type="button" data-category-id="<?php echo (int) $cat['id']; ?>" data-category-name="<?php echo e($cat['name']); ?>">
                    <i class="fa-solid fa-code"></i>
                    <strong><?php echo e($cat['name']); ?></strong>
                    <span><?php echo (int) $cat['project_count']; ?> source</span>
                </button>
            <?php } ?>
        </div>
    </section>

    <section id="category-results" class="container section-block category-results" hidden>
        <div class="section-heading">
            <div>
                <span>Kết quả lọc nhanh</span>
                <h2 id="category-results-title">Source theo danh mục</h2>
                <p>Kết quả hiện ngay trên trang chủ, không chuyển sang trang khác.</p>
            </div>
            <button class="btn btn-sm btn-outline-secondary" type="button" id="clearCategoryFilter">Xem lại tất cả</button>
        </div>
        <div class="source-grid" id="category-results-grid">
            <?php foreach ($categorySources as $row) { source_card($row); } ?>
        </div>
        <div class="empty-state mt-3" id="category-results-empty" hidden>Chưa có source phù hợp với danh mục này.</div>
    </section>

    <?php foreach ([
        ['featured-sources', 'Source nổi bật', 'Những source được admin đề xuất và tải nhiều nhất.', $featured],
        ['latest-sources', 'Source mới nhất', 'Cập nhật các source vừa được duyệt trên hệ thống.', $latest],
        ['free-sources', 'Source miễn phí', 'Tải nhanh các source free phù hợp học tập và tham khảo.', $freeSources],
        ['premium-sources', 'Source premium', 'Source trả phí chất lượng cao, có hỗ trợ cài đặt và báo cáo.', $premiumSources],
    ] as $section) { ?>
        <section id="<?php echo e($section[0]); ?>" class="container section-block">
            <div class="section-heading">
                <div>
                    <span>CodeDoAn</span>
                    <h2><?php echo e($section[1]); ?></h2>
                    <p><?php echo e($section[2]); ?></p>
                </div>
                <a href="<?php echo e(base_url('projects/search.php')); ?>">Xem tất cả</a>
            </div>
            <div class="source-grid">
                <?php foreach ($section[3] as $row) { source_card($row); } ?>
            </div>
            <?php if (!$section[3]) { ?>
                <div class="empty-state">Chưa có source phù hợp.</div>
            <?php } ?>
        </section>
    <?php } ?>
</main>

<?php include 'chatbot/widget.php'; ?>
<script>
document.querySelectorAll('.js-category-filter').forEach((button) => {
    button.addEventListener('click', () => {
        const categoryId = button.dataset.categoryId;
        const categoryName = button.dataset.categoryName || 'danh mục này';
        const results = document.getElementById('category-results');
        const title = document.getElementById('category-results-title');
        const empty = document.getElementById('category-results-empty');
        let visibleCount = 0;

        document.querySelectorAll('.js-category-filter').forEach((item) => item.classList.remove('active'));
        button.classList.add('active');
        title.textContent = 'Source ' + categoryName;
        results.hidden = false;

        document.querySelectorAll('#category-results-grid .source-card').forEach((card) => {
            const matched = card.dataset.categoryId === categoryId;
            card.hidden = !matched;
            if (matched) {
                visibleCount++;
            }
        });

        empty.hidden = visibleCount > 0;
        results.scrollIntoView({behavior: 'smooth', block: 'start'});
    });
});

document.getElementById('clearCategoryFilter')?.addEventListener('click', () => {
    document.querySelectorAll('.js-category-filter').forEach((item) => item.classList.remove('active'));
    document.querySelectorAll('#category-results-grid .source-card').forEach((card) => {
        card.hidden = false;
    });
    document.getElementById('category-results-title').textContent = 'Tất cả source nổi bật';
    document.getElementById('category-results-empty').hidden = true;
});
</script>
<?php include 'includes/footer.php'; ?>
