<?php
require_once dirname(__DIR__) . '/config.php';
require_login();

$id = (int) ($_GET['id'] ?? 0);
$project = db_one('SELECT * FROM projects WHERE id = ?', [$id], 'i');
if (!$project || ((int) $project['user_id'] !== (int) current_user()['id'] && !is_admin())) {
    flash('Khong co quyen sua do an nay.', 'danger');
    redirect(base_url('auth/profile.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $metaTitle = trim($_POST['meta_title'] ?? '') ?: $title;

    if ($title !== '' && $description !== '' && $categoryId > 0) {
        $slug = ensure_unique_slug($title, $id);
        db_query(
            'UPDATE projects SET title = ?, slug = ?, meta_title = ?, description = ?, category_id = ? WHERE id = ?',
            [$title, $slug, $metaTitle, $description, $categoryId, $id],
            'ssssii'
        );
        flash('Da cap nhat do an.');
        redirect(base_url('auth/profile.php'));
    }
}

$categories = db_all('SELECT * FROM categories ORDER BY name');
$pageTitle = 'Sua do an';
?>
<?php include dirname(__DIR__) . '/includes/header.php'; ?>
<?php include dirname(__DIR__) . '/includes/navbar.php'; ?>

<main class="container py-5">
    <div class="card col-lg-8 mx-auto">
        <div class="card-body p-4">
            <h3 class="mb-4">Chinh sua do an</h3>
            <form method="POST">
                <?php echo csrf_field(); ?>
                <label class="form-label">Ten do an</label>
                <input type="text" name="title" class="form-control mb-3" value="<?php echo e($project['title']); ?>" required>
                <label class="form-label">Meta title SEO</label>
                <input type="text" name="meta_title" class="form-control mb-3" value="<?php echo e($project['meta_title']); ?>">
                <label class="form-label">Mo ta</label>
                <textarea name="description" class="form-control mb-3" rows="6" required><?php echo e($project['description']); ?></textarea>
                <label class="form-label">Danh muc</label>
                <select name="category_id" class="form-select mb-4">
                    <?php foreach ($categories as $cat) { ?>
                        <option value="<?php echo (int) $cat['id']; ?>" <?php echo (int) $cat['id'] === (int) $project['category_id'] ? 'selected' : ''; ?>>
                            <?php echo e($cat['name']); ?>
                        </option>
                    <?php } ?>
                </select>
                <button class="btn btn-primary">Cap nhat</button>
            </form>
        </div>
    </div>
</main>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
