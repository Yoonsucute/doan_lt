<?php
include '../config.php';
require_admin();

$users = db_all('SELECT * FROM users ORDER BY id DESC');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý user</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Quản lý user</h2>
        <a class="btn btn-outline-secondary" href="dashboard.php">Dashboard</a>
    </div>
    <div class="card p-3">
        <div class="table-responsive">
            <table class="table align-middle">
                <tr><th>ID</th><th>Họ tên</th><th>Email</th><th>Role</th><th>Ngay tao</th><th>Action</th></tr>
                <?php foreach ($users as $u) { ?>
                    <tr>
                        <td><?php echo (int) $u['id']; ?></td>
                        <td><?php echo e($u['name']); ?></td>
                        <td><?php echo e($u['email']); ?></td>
                        <td><?php echo e($u['role']); ?></td>
                        <td><?php echo e($u['created_at']); ?></td>
                        <td>
                            <?php if ($u['role'] !== 'admin') { ?>
                                <a href="delete_user.php?id=<?php echo (int) $u['id']; ?>&csrf_token=<?php echo e(csrf_token()); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xoa user nay?')">Xoa</a>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</main>
</body>
</html>
