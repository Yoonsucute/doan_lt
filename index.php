<?php
include 'config.php';

$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = 6;
$start = ($page - 1) * $limit;

$totalProject = (int) (db_one("SELECT COUNT(*) AS total FROM projects WHERE status = 'approved'")['total'] ?? 0);
$totalPage = max(1, (int) ceil($totalProject / $limit));

$projects = db_all(
    "SELECT projects.*, users.name, categories.name AS category_name,
        COALESCE(AVG(ratings.star), 0) AS avg_star,
        COUNT(DISTINCT bookmarks.id) AS bookmark_count
     FROM projects
     JOIN users ON projects.user_id = users.id
     JOIN categories ON projects.category_id = categories.id
     LEFT JOIN ratings ON ratings.project_id = projects.id
     LEFT JOIN bookmarks ON bookmarks.project_id = projects.id
     WHERE projects.status = 'approved'
     GROUP BY projects.id
     ORDER BY projects.id DESC
     LIMIT ?, ?",
    [$start, $limit],
    'ii'
);

$topProjects = db_all(
    "SELECT id, title, slug, image, description
     FROM projects
     WHERE status = 'approved'
     ORDER BY downloads DESC, id DESC
     LIMIT 3"
);
$categories = db_all(
    "SELECT categories.*, COUNT(projects.id) AS project_count
     FROM categories
     LEFT JOIN projects ON projects.category_id = categories.id AND projects.status = 'approved'
     GROUP BY categories.id
     ORDER BY categories.name"
);

$pageTitle = 'Share Do An';
$metaDescription = 'Tim va chia se source code do an PHP, Laravel, Java, Python, NodeJS.';
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<main class="container py-4">
    <section class="hero-swiper swiper mb-4">
        <div class="swiper-wrapper">
            <?php foreach ($topProjects ?: [['title' => 'Share Do An', 'description' => 'Kham pha source code va do an lap trinh chat luong.', 'image' => '']] as $item) { ?>
                <div class="swiper-slide hero-slide" style="background-image:url('<?php echo e(base_url('uploads/images/' . ($item['image'] ?: 'default.jpg'))); ?>')">
                    <div>
                        <span class="badge text-bg-primary mb-3">Source code moi</span>
                        <h1 class="fw-bold mb-2"><?php echo e($item['title']); ?></h1>
                        <p class="mb-3"><?php echo e($item['description']); ?></p>
                        <?php if (!empty($item['id'])) { ?>
                            <a class="btn btn-light btn-sm" href="<?php echo e(base_url('projects/detail.php?id=' . (int) $item['id'] . '&slug=' . $item['slug'])); ?>">Xem chi tiet</a>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
        <div class="swiper-pagination"></div>
    </section>

    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Do an da duyet</h2>
            <p class="text-muted mb-0">Tai source, danh gia, bookmark va theo doi tac gia ban thich.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <span class="stat-pill"><i class="fa-solid fa-folder-open"></i><?php echo $totalProject; ?> do an</span>
            <span class="stat-pill"><i class="fa-solid fa-shield-halved"></i>Dang duyet noi dung</span>
        </div>
    </div>

    <div class="d-flex gap-2 flex-wrap mb-4">
        <?php foreach ($categories as $cat) { ?>
            <a class="stat-pill" href="<?php echo e(base_url('projects/category.php?id=' . (int) $cat['id'] . '&slug=' . slugify($cat['name']))); ?>">
                <?php echo e($cat['name']); ?>
                <span class="text-muted"><?php echo (int) $cat['project_count']; ?></span>
            </a>
        <?php } ?>
    </div>

    <div class="row">
        <?php foreach ($projects as $row) { ?>
            <div class="col-md-6 col-xl-4 mb-4">
                <div class="card h-100">
                    <img class="project-cover" src="<?php echo e(base_url('uploads/images/' . $row['image'])); ?>" alt="<?php echo e($row['title']); ?>">
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2 d-flex justify-content-between gap-2">
                            <span class="badge text-bg-primary"><?php echo e($row['category_name']); ?></span>
                            <span class="text-warning small"><i class="fa-solid fa-star"></i> <?php echo number_format((float) $row['avg_star'], 1); ?></span>
                        </div>
                        <div class="project-title"><?php echo e($row['title']); ?></div>
                        <div class="project-desc mt-2"><?php echo e($row['description']); ?></div>
                        <div class="mt-3 small text-muted">
                            <i class="fa-solid fa-user"></i> <?php echo e($row['name']); ?>
                            <span class="ms-2"><i class="fa-solid fa-download"></i> <?php echo (int) $row['downloads']; ?></span>
                            <span class="ms-2"><i class="fa-solid fa-bookmark"></i> <?php echo (int) $row['bookmark_count']; ?></span>
                        </div>
                        <div class="mt-auto pt-3 d-flex gap-2">
                            <a href="<?php echo e(base_url('projects/detail.php?id=' . (int) $row['id'] . '&slug=' . $row['slug'])); ?>" class="btn btn-primary btn-sm">Chi tiet</a>
                            <a href="<?php echo e(base_url('projects/download.php?id=' . (int) $row['id'])); ?>" class="btn btn-success btn-sm">Download</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>

    <?php if (!$projects) { ?>
        <div class="card p-4 text-center">Chua co do an nao duoc duyet.</div>
    <?php } ?>

    <nav class="mt-2">
        <ul class="pagination">
            <?php for ($i = 1; $i <= $totalPage; $i++) { ?>
                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php } ?>
        </ul>
    </nav>
</main>

<?php include 'chatbot/widget.php'; ?>
<?php include 'includes/footer.php'; ?>
