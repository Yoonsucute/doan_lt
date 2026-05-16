<?php
include '../config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    if ($name !== '') {
        db_query('INSERT INTO categories(name, slug) VALUES(?, ?)', [$name, slugify($name)], 'ss');
        flash('Đã thêm danh mục.');
        redirect('categories.php');
    }
}

$categories = db_all(
    "SELECT categories.*, COUNT(projects.id) AS project_count
     FROM categories
     LEFT JOIN projects ON projects.category_id = categories.id
     GROUP BY categories.id
     ORDER BY categories.name"
);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý danh mục</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Quản lý danh mục</h2>
        <a class="btn btn-outline-secondary" href="dashboard.php">Dashboard</a>
    </div>
    <form method="POST" class="card p-3 mb-4">
        <?php echo csrf_field(); ?>
        <div class="input-group">
            <input name="name" class="form-control" placeholder="Tên danh mục mới" required>
            <button class="btn btn-primary">Them</button>
        </div>
    </form>
    <div class="card p-3">
        <table class="table align-middle">
            <tr><th>ID</th><th>Ten</th><th>Slug</th><th>Source</th></tr>
            <?php foreach ($categories as $cat) { ?>
                <tr>
                    <td><?php echo (int) $cat['id']; ?></td>
                    <td><?php echo e($cat['name']); ?></td>
                    <td><?php echo e($cat['slug']); ?></td>
                    <td><?php echo (int) $cat['project_count']; ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
</main>
</body>
</html>
