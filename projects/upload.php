<?php
require_once dirname(__DIR__) . '/config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title = trim($_POST['title'] ?? '');
    $shortDescription = trim($_POST['short_description'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $metaTitle = trim($_POST['meta_title'] ?? '') ?: $title;
    $techStack = trim($_POST['tech_stack'] ?? '');
    $version = trim($_POST['version'] ?? '');
    $demoLink = trim($_POST['demo_link'] ?? '');
    $videoDemo = trim($_POST['video_demo'] ?? '');
    $installGuide = trim($_POST['install_guide'] ?? '');
    $mainFeatures = trim($_POST['main_features'] ?? '');
    $tier = $_POST['tier'] ?? 'basic';
    $tier = in_array($tier, ['basic', 'premium', 'exclusive'], true) ? $tier : 'basic';
    $price = max(0, (int) ($_POST['price'] ?? 0));
    $salePrice = max(0, (int) ($_POST['sale_price'] ?? 0));
    $isFree = isset($_POST['is_free']) || ($price === 0 && $salePrice === 0) ? 1 : 0;

    try {
        if ($title === '' || $description === '' || $categoryId <= 0) {
            throw new RuntimeException('Vui lòng nhập đầy đủ thông tin.');
        }

        $image = safe_filename($_FILES['image']['name'] ?? '', ['jpg', 'jpeg', 'png', 'webp'], 3 * 1024 * 1024, $_FILES['image']['tmp_name'] ?? '', (int) ($_FILES['image']['size'] ?? 0));
        $source = safe_filename($_FILES['source']['name'] ?? '', ['zip', 'rar', '7z'], 50 * 1024 * 1024, $_FILES['source']['tmp_name'] ?? '', (int) ($_FILES['source']['size'] ?? 0));

        if (!move_uploaded_file($_FILES['image']['tmp_name'], dirname(__DIR__) . '/uploads/images/' . $image)) {
            throw new RuntimeException('Không lưu được ảnh preview.');
        }
        if (!move_uploaded_file($_FILES['source']['tmp_name'], dirname(__DIR__) . '/uploads/files/' . $source)) {
            throw new RuntimeException('Không lưu được file source.');
        }

        $slug = ensure_unique_slug($title);
        db_query(
            "INSERT INTO projects(user_id, category_id, title, slug, meta_title, short_description, description, tech_stack, version, demo_link, video_demo, install_guide, main_features, image, source_file, price, sale_price, is_free, tier, file_size, status)
             VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')",
            [
                current_user()['id'], $categoryId, $title, $slug, $metaTitle, $shortDescription,
                $description, $techStack, $version, $demoLink, $videoDemo, $installGuide,
                $mainFeatures, $image, $source, $price, $salePrice, $isFree, $tier,
                round(((int) $_FILES['source']['size']) / 1024 / 1024, 2) . ' MB',
            ],
            'iisssssssssssssiiiss'
        );

        flash('Đã upload đồ án. Bài đăng đang chờ admin duyệt.');
        redirect(base_url('auth/profile.php'));
    } catch (RuntimeException $e) {
        flash($e->getMessage(), 'danger');
    }
}

$categories = db_all('SELECT * FROM categories ORDER BY name');
$pageTitle = 'Upload đồ án';
?>
<?php include dirname(__DIR__) . '/includes/header.php'; ?>
<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>

<main class="container py-5">
    <div class="card col-lg-8 mx-auto">
        <div class="card-body p-4">
            <h3 class="mb-4">Upload đồ án</h3>
            <form method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <label class="form-label">Tên đồ án</label>
                <input type="text" name="title" class="form-control mb-3" required>

                <label class="form-label">Meta title SEO</label>
                <input type="text" name="meta_title" class="form-control mb-3" placeholder="Để trống sẽ lấy theo tên đồ án">

                <label class="form-label">Mô tả ngan</label>
                <input type="text" name="short_description" class="form-control mb-3" maxlength="255" placeholder="Tóm tắt source trong 1 câu">

                <label class="form-label">Mô tả chi tiet</label>
                <textarea name="description" class="form-control mb-3" rows="6" required></textarea>

                <label class="form-label">Danh mục</label>
                <select name="category_id" class="form-select mb-3" required>
                    <?php foreach ($categories as $cat) { ?>
                        <option value="<?php echo (int) $cat['id']; ?>"><?php echo e($cat['name']); ?></option>
                    <?php } ?>
                </select>

                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Tech stack</label>
                        <input type="text" name="tech_stack" class="form-control mb-3" placeholder="PHP, MySQL, Bootstrap">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Version</label>
                        <input type="text" name="version" class="form-control mb-3" placeholder="1.0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Loại source</label>
                        <select name="tier" class="form-select mb-3">
                            <option value="basic">Basic</option>
                            <option value="premium">Premium</option>
                            <option value="exclusive">Exclusive</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Giá gốc (VND)</label>
                        <input type="number" name="price" class="form-control mb-3" min="0" step="1000" placeholder="0 neu mien phi">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Giá khuyến mãi (VND)</label>
                        <input type="number" name="sale_price" class="form-control mb-3" min="0" step="1000" placeholder="Để trống nếu không sale">
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="is_free" id="is_free">
                    <label class="form-check-label" for="is_free">Source miễn phí</label>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Link demo</label>
                        <input type="url" name="demo_link" class="form-control mb-3" placeholder="Nếu có demo nội bộ của bạn">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Link video demo</label>
                        <input type="url" name="video_demo" class="form-control mb-3" placeholder="Nếu có video demo từ bạn">
                    </div>
                </div>

                <label class="form-label">Chức năng chinh</label>
                <textarea name="main_features" class="form-control mb-3" rows="4" placeholder="- Đăng nhập&#10;- Quan ly san pham&#10;- Giỏ hàng"></textarea>

                <label class="form-label">Hướng dẫn cài đặt</label>
                <textarea name="install_guide" class="form-control mb-3" rows="4" placeholder="Các bước cài đặt source"></textarea>

                <label class="form-label">Ảnh preview (jpg, png, webp - tối đa 3MB)</label>
                <input type="file" name="image" class="form-control mb-3" accept=".jpg,.jpeg,.png,.webp" required>

                <label class="form-label">File source (zip, rar, 7z - tối đa 50MB)</label>
                <input type="file" name="source" class="form-control mb-4" accept=".zip,.rar,.7z" required>

                <button class="btn btn-success">Đăng đồ án</button>
            </form>
        </div>
    </div>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
