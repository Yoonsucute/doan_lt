<?php
require_once dirname(__DIR__) . '/config.php';

$keyword = trim($_GET['keyword'] ?? '');
$like = '%' . $keyword . '%';
$projects = $keyword === '' ? [] : db_all(
    "SELECT projects.*, users.name, categories.name AS category_name
     FROM projects
     JOIN users ON projects.user_id = users.id
     JOIN categories ON projects.category_id = categories.id
     WHERE projects.status = 'approved'
       AND (projects.title LIKE ? OR projects.description LIKE ? OR categories.name LIKE ?)
     ORDER BY projects.id DESC",
    [$like, $like, $like],
    'sss'
);

$pageTitle = 'Tim kiem';
?>
<?php include dirname(__DIR__) . '/includes/header.php'; ?>
<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>

<main class="container py-4">
    <h3 class="mb-4">Ket qua tim kiem: "<?php echo e($keyword); ?>"</h3>
    <div class="row">
        <?php foreach ($projects as $row) { ?>
            <div class="col-md-6 col-xl-4 mb-4">
                <div class="card h-100">
                    <img class="project-cover" src="<?php echo e(base_url('uploads/images/' . $row['image'])); ?>" alt="<?php echo e($row['title']); ?>">
                    <div class="card-body d-flex flex-column">
                        <span class="badge text-bg-primary align-self-start mb-2"><?php echo e($row['category_name']); ?></span>
                        <div class="project-title"><?php echo e($row['title']); ?></div>
                        <div class="project-desc mt-2"><?php echo e($row['description']); ?></div>
                        <a href="<?php echo e(base_url('projects/detail.php?id=' . (int) $row['id'] . '&slug=' . $row['slug'])); ?>" class="btn btn-primary btn-sm mt-auto">Chi tiet</a>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
    <?php if ($keyword !== '' && !$projects) { ?>
        <div class="card p-4 text-center">Khong tim thay ket qua phu hop.</div>
    <?php } ?>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
