<nav class="navbar navbar-expand-lg sticky-top app-navbar">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?php echo e(base_url('index.php')); ?>">
            <i class="fa-solid fa-code"></i> CodeDoAn
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <form action="<?php echo e(base_url('projects/search.php')); ?>" method="GET" class="ms-lg-4 my-3 my-lg-0 flex-grow-1">
                <div class="input-group search-box">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="keyword" class="form-control" placeholder="Tìm kiếm đồ án, source code..." value="<?php echo e($_GET['keyword'] ?? ''); ?>">
                </div>
            </form>
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <li class="nav-item"><a class="nav-link" href="<?php echo e(base_url('index.php')); ?>">Trang chủ</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo e(base_url('projects/search.php')); ?>">Danh mục</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo e(base_url('projects/search.php?price=free')); ?>">Code miễn phí</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo e(base_url('projects/search.php?price=paid&tier=premium')); ?>">Code Premium</a></li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(base_url('shop/orders.php')); ?>"><i class="fa-solid fa-box"></i> Đơn hàng</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link cart-link" href="<?php echo e(base_url('shop/cart.php')); ?>">
                        <i class="fa-solid fa-cart-shopping"></i> Giỏ hàng
                        <span class="badge text-bg-danger"><?php echo cart_count(); ?></span>
                    </a>
                </li>
                <?php if (current_user()) { ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo e(base_url('projects/upload.php')); ?>">Upload</a></li>
                    <?php if (is_admin()) { ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo e(base_url('admin/dashboard.php')); ?>">Admin</a></li>
                    <?php } ?>
                    <li class="nav-item">
                        <button class="nav-link theme-toggle" type="button" id="themeToggle" title="Dark mode">
                            <i class="fa-solid fa-moon"></i>
                        </button>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo e(base_url('auth/profile.php')); ?>"><?php echo e($_SESSION['user']['name']); ?></a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="<?php echo e(base_url('auth/logout.php')); ?>">Đăng xuất</a></li>
                <?php } else { ?>
                    <li class="nav-item">
                        <button class="nav-link theme-toggle" type="button" id="themeToggle" title="Dark mode">
                            <i class="fa-solid fa-moon"></i>
                        </button>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo e(base_url('auth/login.php')); ?>">Đăng nhập</a></li>
                    <li class="nav-item"><a class="btn btn-primary btn-sm ms-lg-2" href="<?php echo e(base_url('auth/register.php')); ?>">Đăng ký</a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
</nav>
