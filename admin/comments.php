<?php
include '../config.php';
require_admin();

if (isset($_GET['delete'], $_GET['csrf_token']) && hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'])) {
    db_query('DELETE FROM comments WHERE id = ?', [(int) $_GET['delete']], 'i');
    flash('Da xoa binh luan.');
    redirect('comments.php');
}

$comments = db_all(
    "SELECT comments.*, users.name, projects.title
     FROM comments
     JOIN users ON users.id = comments.user_id
     JOIN projects ON projects.id = comments.project_id
     ORDER BY comments.id DESC"
);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý bình luận</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Quản lý bình luận</h2>
        <a class="btn btn-outline-secondary" href="dashboard.php">Dashboard</a>
    </div>
    <div class="card p-3">
        <table class="table align-middle">
            <tr><th>ID</th><th>Source</th><th>User</th><th>Noi dung</th><th>Ngay</th><th></th></tr>
            <?php foreach ($comments as $c) { ?>
                <tr>
                    <td><?php echo (int) $c['id']; ?></td>
                    <td><?php echo e($c['title']); ?></td>
                    <td><?php echo e($c['name']); ?></td>
                    <td><?php echo e($c['content']); ?></td>
                    <td><?php echo e($c['created_at']); ?></td>
                    <td><a class="btn btn-danger btn-sm" href="comments.php?delete=<?php echo (int) $c['id']; ?>&csrf_token=<?php echo e(csrf_token()); ?>" onclick="return confirm('Xoa binh luan?')">Xoa</a></td>
                </tr>
            <?php } ?>
        </table>
    </div>
</main>
</body>
</html>
