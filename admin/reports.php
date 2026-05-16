<?php
include '../config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    $status = ($_POST['status'] ?? '') === 'resolved' ? 'resolved' : 'open';
    db_query('UPDATE reports SET status = ? WHERE id = ?', [$status, $id], 'si');
    if (isset($_POST['hide_project'])) {
        $projectId = (int) ($_POST['project_id'] ?? 0);
        db_query("UPDATE projects SET status = 'hidden' WHERE id = ?", [$projectId], 'i');
    }
    flash('Da xu ly report.');
    redirect('reports.php');
}

$reports = db_all(
    "SELECT reports.*, projects.title, users.name
     FROM reports
     JOIN projects ON projects.id = reports.project_id
     JOIN users ON users.id = reports.user_id
     ORDER BY reports.id DESC"
);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý báo cáo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Quản lý báo cáo</h2>
        <a class="btn btn-outline-secondary" href="dashboard.php">Dashboard</a>
    </div>
    <div class="card p-3">
        <table class="table align-middle">
            <tr><th>ID</th><th>Source</th><th>User</th><th>Ly do</th><th>Status</th><th>Action</th></tr>
            <?php foreach ($reports as $r) { ?>
                <tr>
                    <td><?php echo (int) $r['id']; ?></td>
                    <td><?php echo e($r['title']); ?></td>
                    <td><?php echo e($r['name']); ?></td>
                    <td><?php echo e($r['reason']); ?></td>
                    <td><?php echo e($r['status']); ?></td>
                    <td>
                        <form method="POST" class="d-flex gap-2 flex-wrap">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id" value="<?php echo (int) $r['id']; ?>">
                            <input type="hidden" name="project_id" value="<?php echo (int) $r['project_id']; ?>">
                            <select name="status" class="form-select form-select-sm" style="width:120px">
                                <option value="open" <?php echo $r['status'] === 'open' ? 'selected' : ''; ?>>open</option>
                                <option value="resolved" <?php echo $r['status'] === 'resolved' ? 'selected' : ''; ?>>resolved</option>
                            </select>
                            <label class="small"><input type="checkbox" name="hide_project"> An source</label>
                            <button class="btn btn-primary btn-sm">Lưu</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</main>
</body>
</html>
