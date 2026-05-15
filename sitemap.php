<?php
include 'config.php';

header('Content-Type: application/xml; charset=utf-8');
$base = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';
$projects = db_all("SELECT id, slug, created_at FROM projects WHERE status = 'approved' ORDER BY id DESC");

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc><?php echo e($base); ?>index.php</loc></url>
    <?php foreach ($projects as $p) { ?>
        <url>
            <loc><?php echo e($base . 'projects/detail.php?id=' . (int) $p['id'] . '&amp;slug=' . $p['slug']); ?></loc>
            <lastmod><?php echo e(date('Y-m-d', strtotime($p['created_at']))); ?></lastmod>
        </url>
    <?php } ?>
</urlset>
