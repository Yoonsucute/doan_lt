<?php
require_once dirname(__DIR__) . '/config.php';
require_login();

$id = (int) current_user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    if ($name !== '') {
        db_query('UPDATE users SET name = ? WHERE id = ?', [$name, $id], 'si');
        $_SESSION['user']['name'] = $name;
        flash('Đã cập nhật profile.');
        redirect(base_url('auth/profile.php'));
    }
}

$projects = db_all('SELECT * FROM projects WHERE user_id = ? ORDER BY id DESC', [$id], 'i');
$bookmarks = db_all(
    'SELECT projects.* FROM bookmarks JOIN projects ON projects.id = bookmarks.project_id WHERE bookmarks.user_id = ? ORDER BY bookmarks.id DESC',
    [$id],
    'i'
);
$notifications = db_all('SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT 8', [$id], 'i');
$pageTitle = 'Profile';
?>
<?php include dirname(__DIR__) . '/includes/header.php'; ?>
<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>

<main class="container py-5">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card p-4 text-center">
                <div class="profile-avatar mx-auto mb-3">
                    <?php
                    $firstLetter = function_exists('mb_substr')
                        ? mb_substr(current_user()['name'], 0, 1, 'UTF-8')
                        : substr(current_user()['name'], 0, 1);
                    echo e($firstLetter);
                    ?>
                </div>
                <h4><?php echo e(current_user()['name']); ?></h4>
                <p class="text-muted"><?php echo e(current_user()['email']); ?></p>
                <form method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="text" name="name" class="form-control mb-3" value="<?php echo e(current_user()['name']); ?>" required>
                    <button class="btn btn-primary w-100">Cap nhat profile</button>
                </form>
            </div>
            <div class="card p-4 mt-4">
                <h5>Notification</h5>
                <?php foreach ($notifications as $n) { ?>
                    <a class="d-block border-top py-2" href="<?php echo e($n['link'] ?: '#'); ?>"><?php echo e($n['message']); ?></a>
                <?php } ?>
                <?php if (!$notifications) { ?><p class="text-muted mb-0">Chưa có thông báo.</p><?php } ?>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card p-4 mb-4">
                <h4 class="mb-3">Đồ án đã đăng</h4>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <tr><th>ID</th><th>Ten</th><th>Status</th><th>Download</th><th>Action</th></tr>
                        <?php foreach ($projects as $p) { ?>
                            <tr>
                                <td><?php echo (int) $p['id']; ?></td>
                                <td><?php echo e($p['title']); ?></td>
                                <td><span class="badge text-bg-<?php echo $p['status'] === 'approved' ? 'success' : 'warning'; ?>"><?php echo e($p['status']); ?></span></td>
                                <td><?php echo (int) $p['downloads']; ?></td>
                                <td>
                                    <a href="<?php echo e(base_url('projects/edit.php?id=' . (int) $p['id'])); ?>" class="btn btn-warning btn-sm">Sửa</a>
                                    <a href="<?php echo e(base_url('projects/delete.php?id=' . (int) $p['id'] . '&csrf_token=' . csrf_token())); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa đồ án này?')">Xoa</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
            </div>
            <div class="card p-4">
                <h4 class="mb-3">Bookmark</h4>
                <?php foreach ($bookmarks as $p) { ?>
                    <a class="d-block border-top py-2" href="<?php echo e(base_url('projects/detail.php?id=' . (int) $p['id'])); ?>"><?php echo e($p['title']); ?></a>
                <?php } ?>
                <?php if (!$bookmarks) { ?><p class="text-muted mb-0">Chưa bookmark đồ án nào.</p><?php } ?>
            </div>
        </div>
    </div>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
