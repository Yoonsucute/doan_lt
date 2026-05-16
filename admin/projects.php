<?php
include '../config.php';
require_admin();

$projects = db_all(
    "SELECT projects.*, users.name,
        (SELECT COUNT(*) FROM reports WHERE reports.project_id = projects.id AND reports.status = 'open') AS report_count
     FROM projects
     JOIN users ON projects.user_id = users.id
     ORDER BY projects.id DESC"
);
$reports = db_all('SELECT reports.*, projects.title, users.name FROM reports JOIN projects ON projects.id = reports.project_id JOIN users ON users.id = reports.user_id ORDER BY reports.id DESC LIMIT 20');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đồ án</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Quản lý đồ án</h2>
        <a class="btn btn-outline-secondary" href="dashboard.php">Dashboard</a>
    </div>
    <div class="card p-3 mb-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <tr><th>ID</th><th>Anh</th><th>Ten</th><th>Người đăng</th><th>Status</th><th>Tier</th><th>Flags</th><th>Report</th><th>Action</th></tr>
                <?php foreach ($projects as $p) { ?>
                    <tr>
                        <td><?php echo (int) $p['id']; ?></td>
                        <td><img src="../uploads/images/<?php echo e($p['image']); ?>" width="90" alt=""></td>
                        <td><?php echo e($p['title']); ?></td>
                        <td><?php echo e($p['name']); ?></td>
                        <td><span class="badge text-bg-<?php echo $p['status'] === 'approved' ? 'success' : 'warning'; ?>"><?php echo e($p['status']); ?></span></td>
                        <td><?php echo e($p['tier']); ?></td>
                        <td>
                            <?php if ((int) $p['is_featured'] === 1) { ?><span class="badge text-bg-info">Featured</span><?php } ?>
                            <?php if ((int) $p['is_hot'] === 1) { ?><span class="badge text-bg-warning">Hot</span><?php } ?>
                        </td>
                        <td><?php echo (int) $p['report_count']; ?></td>
                        <td>
                            <form method="POST" action="project_status.php">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="id" value="<?php echo (int) $p['id']; ?>">
                                <div class="d-flex gap-2 flex-wrap">
                                    <select name="status" class="form-select form-select-sm" style="width:120px">
                                        <?php foreach (['pending', 'approved', 'rejected', 'hidden'] as $status) { ?>
                                            <option value="<?php echo $status; ?>" <?php echo $p['status'] === $status ? 'selected' : ''; ?>><?php echo $status; ?></option>
                                        <?php } ?>
                                    </select>
                                    <select name="tier" class="form-select form-select-sm" style="width:120px">
                                        <?php foreach (['basic', 'premium', 'exclusive'] as $tier) { ?>
                                            <option value="<?php echo $tier; ?>" <?php echo $p['tier'] === $tier ? 'selected' : ''; ?>><?php echo $tier; ?></option>
                                        <?php } ?>
                                    </select>
                                    <label class="form-check-label small"><input type="checkbox" name="is_featured" <?php echo (int) $p['is_featured'] === 1 ? 'checked' : ''; ?>> Featured</label>
                                    <label class="form-check-label small"><input type="checkbox" name="is_hot" <?php echo (int) $p['is_hot'] === 1 ? 'checked' : ''; ?>> Hot</label>
                                    <button class="btn btn-sm btn-primary">Lưu</button>
                                    <a href="delete_project.php?id=<?php echo (int) $p['id']; ?>&csrf_token=<?php echo e(csrf_token()); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xoa do an?')">Xoa</a>
                                </div>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>

    <div class="card p-3">
        <h4>Báo cáo mởi</h4>
        <div class="table-responsive">
            <table class="table">
                <tr><th>Do an</th><th>User</th><th>Ly do</th><th>Trạng thái</th></tr>
                <?php foreach ($reports as $r) { ?>
                    <tr>
                        <td><?php echo e($r['title']); ?></td>
                        <td><?php echo e($r['name']); ?></td>
                        <td><?php echo e($r['reason']); ?></td>
                        <td><?php echo e($r['status']); ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</main>
</body>
</html>
