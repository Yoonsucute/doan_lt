<?php
$pageTitle = $pageTitle ?? 'CodeDoAn';
$metaTitle = $metaTitle ?? $pageTitle;
$metaDescription = $metaDescription ?? 'CodeDoAn - nền tảng chia sẻ, mua bán và tải source code, đồ án lập trình.';
$styleVersion = is_file(__DIR__ . '/../assets/css/style.css') ? filemtime(__DIR__ . '/../assets/css/style.css') : time();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo e($metaDescription); ?>">
    <title><?php echo e($metaTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="<?php echo e(base_url('assets/css/style.css?v=' . $styleVersion)); ?>">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
<div id="page-loader" class="page-loader">
    <div class="spinner-border text-primary" role="status"></div>
</div>
<div class="toast-container position-fixed top-0 end-0 p-3">
    <?php foreach (flashes() as $flash) { ?>
        <div class="toast align-items-center text-bg-<?php echo e($flash['type']); ?> border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body"><?php echo e($flash['message']); ?></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    <?php } ?>
</div>
