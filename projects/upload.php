<?php
require_once dirname(__DIR__) . '/config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $metaTitle = trim($_POST['meta_title'] ?? '') ?: $title;
    $price = max(0, (int) ($_POST['price'] ?? 0));
    $salePrice = max(0, (int) ($_POST['sale_price'] ?? 0));

    try {
        if ($title === '' || $description === '' || $categoryId <= 0) {
            throw new RuntimeException('Vui long nhap day du thong tin.');
        }

        $image = safe_filename($_FILES['image']['name'] ?? '', ['jpg', 'jpeg', 'png', 'webp'], 3 * 1024 * 1024, $_FILES['image']['tmp_name'] ?? '', (int) ($_FILES['image']['size'] ?? 0));
        $source = safe_filename($_FILES['source']['name'] ?? '', ['zip', 'rar', '7z'], 50 * 1024 * 1024, $_FILES['source']['tmp_name'] ?? '', (int) ($_FILES['source']['size'] ?? 0));

        if (!move_uploaded_file($_FILES['image']['tmp_name'], dirname(__DIR__) . '/uploads/images/' . $image)) {
            throw new RuntimeException('Khong luu duoc anh preview.');
        }
        if (!move_uploaded_file($_FILES['source']['tmp_name'], dirname(__DIR__) . '/uploads/files/' . $source)) {
            throw new RuntimeException('Khong luu duoc file source.');
        }

        $slug = ensure_unique_slug($title);
        db_query(
            "INSERT INTO projects(user_id, category_id, title, slug, meta_title, description, image, source_file, price, sale_price, status)
             VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')",
            [current_user()['id'], $categoryId, $title, $slug, $metaTitle, $description, $image, $source, $price, $salePrice],
            'iissssssii'
        );

        flash('Da upload do an. Bai dang dang cho admin duyet.');
        redirect(base_url('auth/profile.php'));
    } catch (RuntimeException $e) {
        flash($e->getMessage(), 'danger');
    }
}

$categories = db_all('SELECT * FROM categories ORDER BY name');
$pageTitle = 'Upload do an';
?>
<?php include dirname(__DIR__) . '/includes/header.php'; ?>
<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>

<main class="container py-5">
    <div class="card col-lg-8 mx-auto">
        <div class="card-body p-4">
            <h3 class="mb-4">Upload do an</h3>
            <form method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <label class="form-label">Ten do an</label>
                <input type="text" name="title" class="form-control mb-3" required>

                <label class="form-label">Meta title SEO</label>
                <input type="text" name="meta_title" class="form-control mb-3" placeholder="De trong se lay theo ten do an">

                <label class="form-label">Mo ta</label>
                <textarea name="description" class="form-control mb-3" rows="6" required></textarea>

                <label class="form-label">Danh muc</label>
                <select name="category_id" class="form-select mb-3" required>
                    <?php foreach ($categories as $cat) { ?>
                        <option value="<?php echo (int) $cat['id']; ?>"><?php echo e($cat['name']); ?></option>
                    <?php } ?>
                </select>

                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Gia goc (VND)</label>
                        <input type="number" name="price" class="form-control mb-3" min="0" step="1000" placeholder="0 neu mien phi">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gia khuyen mai (VND)</label>
                        <input type="number" name="sale_price" class="form-control mb-3" min="0" step="1000" placeholder="De trong neu khong sale">
                    </div>
                </div>

                <label class="form-label">Anh preview (jpg, png, webp - toi da 3MB)</label>
                <input type="file" name="image" class="form-control mb-3" accept=".jpg,.jpeg,.png,.webp" required>

                <label class="form-label">File source (zip, rar, 7z - toi da 50MB)</label>
                <input type="file" name="source" class="form-control mb-4" accept=".zip,.rar,.7z" required>

                <button class="btn btn-success">Dang do an</button>
            </form>
        </div>
    </div>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
