<?php
require_once dirname(__DIR__) . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $passwordRaw = $_POST['password'] ?? '';

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($passwordRaw) < 6) {
        flash('Thong tin dang ky khong hop le. Mat khau toi thieu 6 ky tu.', 'danger');
    } elseif (db_one('SELECT id FROM users WHERE email = ?', [$email], 's')) {
        flash('Email da ton tai.', 'danger');
    } else {
        $password = password_hash($passwordRaw, PASSWORD_DEFAULT);
        db_query('INSERT INTO users(name, email, password) VALUES(?, ?, ?)', [$name, $email, $password], 'sss');
        flash('Dang ky thanh cong. Hay dang nhap.');
        redirect(base_url('auth/login.php'));
    }
}

$pageTitle = 'Dang ky';
?>
<?php include dirname(__DIR__) . '/includes/header.php'; ?>

<main class="container py-5">
    <div class="auth-card col-md-5 col-lg-4 mx-auto p-4">
        <h3 class="text-center mb-4">Dang ky</h3>
        <form method="POST">
            <?php echo csrf_field(); ?>
            <input type="text" name="name" class="form-control mb-3" placeholder="Ho ten" required>
            <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>
            <input type="password" name="password" class="form-control mb-3" placeholder="Mat khau" minlength="6" required>
            <button class="btn btn-primary w-100">Dang ky</button>
        </form>
        <div class="text-center mt-3">Da co tai khoan? <a href="<?php echo e(base_url('auth/login.php')); ?>">Dang nhap</a></div>
    </div>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
