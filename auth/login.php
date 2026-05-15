<?php
require_once dirname(__DIR__) . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $user = db_one('SELECT * FROM users WHERE email = ? LIMIT 1', [$email], 's');

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(false);
        $_SESSION['user'] = $user;
        flash('Dang nhap thanh cong.');
        redirect($user['role'] === 'admin' ? base_url('admin/dashboard.php') : base_url('index.php'));
    }

    flash('Sai email hoac mat khau.', 'danger');
}

$pageTitle = 'Dang nhap';
?>
<?php include dirname(__DIR__) . '/includes/header.php'; ?>

<main class="container py-5">
    <div class="auth-card col-md-5 col-lg-4 mx-auto p-4">
        <h3 class="text-center mb-4">Dang nhap</h3>
        <form method="POST">
            <?php echo csrf_field(); ?>
            <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>
            <input type="password" name="password" class="form-control mb-3" placeholder="Mat khau" required>
            <button class="btn btn-success w-100">Dang nhap</button>
        </form>
        <div class="text-center mt-3">Chua co tai khoan? <a href="<?php echo e(base_url('auth/register.php')); ?>">Dang ky</a></div>
    </div>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
