<?php
require_once dirname(__DIR__) . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $passwordRaw = $_POST['password'] ?? '';

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($passwordRaw) < 6) {
        flash('Thông tin đăng ký không hợp lệ. Mật khẩu tối thiểu 6 ký tự.', 'danger');
    } elseif (db_one('SELECT id FROM users WHERE email = ?', [$email], 's')) {
        flash('Email đã tồn tại.', 'danger');
    } else {
        $password = password_hash($passwordRaw, PASSWORD_DEFAULT);
        db_query('INSERT INTO users(name, email, password) VALUES(?, ?, ?)', [$name, $email, $password], 'sss');
        flash('Đăng ký thành công. Hãy đăng nhập.');
        redirect(base_url('auth/login.php'));
    }
}

$pageTitle = 'Đăng ký';
?>
<?php include dirname(__DIR__) . '/includes/header.php'; ?>

<main class="container py-5">
    <div class="auth-card col-md-5 col-lg-4 mx-auto p-4">
        <h3 class="text-center mb-4">Đăng ký</h3>
        <form method="POST">
            <?php echo csrf_field(); ?>
            <input type="text" name="name" class="form-control mb-3" placeholder="Họ tên" required>
            <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>
            <input type="password" name="password" class="form-control mb-3" placeholder="Mật khẩu" minlength="6" required>
            <button class="btn btn-primary w-100">Đăng ký</button>
        </form>
        <div class="text-center mt-3">Đã có tài khoản? <a href="<?php echo e(base_url('auth/login.php')); ?>">Đăng nhập</a></div>
    </div>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
