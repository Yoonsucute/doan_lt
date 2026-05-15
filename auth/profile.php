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
        flash('Da cap nhat profile.');
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
                <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" width="110" class="mx-auto mb-3" alt="Avatar">
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
                <?php if (!$notifications) { ?><p class="text-muted mb-0">Chua co thong bao.</p><?php } ?>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card p-4 mb-4">
                <h4 class="mb-3">Do an da dang</h4>
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
                                    <a href="<?php echo e(base_url('projects/edit.php?id=' . (int) $p['id'])); ?>" class="btn btn-warning btn-sm">Sua</a>
                                    <a href="<?php echo e(base_url('projects/delete.php?id=' . (int) $p['id'] . '&csrf_token=' . csrf_token())); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xoa do an nay?')">Xoa</a>
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
                <?php if (!$bookmarks) { ?><p class="text-muted mb-0">Chua bookmark do an nao.</p><?php } ?>
            </div>
        </div>
    </div>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
