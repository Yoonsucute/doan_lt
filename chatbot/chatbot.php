<?php
include '../config.php';

$message = trim($_POST['message'] ?? '');
if ($message === '') {
    echo 'Bạn cần tìm source về đề tài nào? Vi du: website ban hang PHP MySQL, quan ly khach san, Java Swing...';
    exit;
}

$lower = function_exists('mb_strtolower') ? mb_strtolower($message, 'UTF-8') : strtolower($message);

if (str_contains($lower, 'dang source') || str_contains($lower, 'upload')) {
    echo 'Để đăng source: đăng nhập -> bấm "Upload" -> nhập tên, mô tả, danh mục, tech stack, giá, ảnh demo và file zip/rar/7z. Source sẽ chờ admin duyệt trước khi hiển thị.';
    exit;
}

if (str_contains($lower, 'tai source') || str_contains($lower, 'download')) {
    echo 'Source miễn phí co the tai truc tiep. Source premium can them vao gio hang, checkout va cho admin xac nhan paid roi moi tai trong muc Đơn hàng.';
    exit;
}

$keywords = preg_split('/\s+/u', preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $lower));
$keywords = array_values(array_filter($keywords, fn($word) => mb_strlen($word, 'UTF-8') >= 2));
$important = [];
foreach ($keywords as $word) {
    if (!in_array($word, ['toi', 'can', 'source', 'code', 'web', 'website', 'do', 'an', 'tim', 'cho', 'xin'], true)) {
        $important[] = $word;
    }
}
$important = array_slice(array_unique($important), 0, 5);

$where = ["projects.status = 'approved'"];
$params = [];
$types = '';
foreach ($important as $word) {
    $like = '%' . $word . '%';
    $where[] = "(projects.title LIKE ? OR projects.short_description LIKE ? OR projects.description LIKE ? OR projects.tech_stack LIKE ? OR categories.name LIKE ?)";
    array_push($params, $like, $like, $like, $like, $like);
    $types .= 'sssss';
}

if (str_contains($lower, 'mien phi') || str_contains($lower, 'free')) {
    $where[] = "(projects.is_free = 1 OR (projects.price = 0 AND projects.sale_price = 0))";
}
if (str_contains($lower, 'premium') || str_contains($lower, 'tra phi')) {
    $where[] = "(projects.price > 0 OR projects.sale_price > 0 OR projects.tier IN ('premium','exclusive'))";
}

$whereSql = implode(' AND ', $where);
$matches = db_all(
    "SELECT projects.id, projects.title, projects.slug, projects.price, projects.sale_price, projects.is_free, categories.name AS category_name
     FROM projects
     JOIN categories ON categories.id = projects.category_id
     WHERE $whereSql
     ORDER BY projects.is_hot DESC, projects.is_featured DESC, projects.downloads_count DESC, projects.id DESC
     LIMIT 5",
    $params,
    $types
);

if (!$matches && $important) {
    $fallback = '%' . $important[0] . '%';
    $matches = db_all(
        "SELECT projects.id, projects.title, projects.slug, projects.price, projects.sale_price, projects.is_free, categories.name AS category_name
         FROM projects
         JOIN categories ON categories.id = projects.category_id
         WHERE projects.status = 'approved'
           AND (projects.title LIKE ? OR categories.name LIKE ?)
         ORDER BY projects.id DESC
         LIMIT 5",
        [$fallback, $fallback],
        'ss'
    );
}

if (!$matches) {
    $cats = db_all('SELECT name FROM categories ORDER BY name LIMIT 8');
    echo 'Chưa thấy source thật sự khớp. Bạn có thể thử các danh mục: ' . implode(', ', array_column($cats, 'name')) . '.';
    exit;
}

$lines = ['Toi tim thay source phù hợp:'];
foreach ($matches as $item) {
    $isFree = (int) $item['is_free'] === 1 || ((int) $item['price'] === 0 && (int) $item['sale_price'] === 0);
    $price = $isFree ? 'Miễn phí' : money(project_price($item));
    $lines[] = '- ' . $item['title'] . ' (' . $item['category_name'] . ', ' . $price . ') - ' . base_url('projects/detail.php?id=' . (int) $item['id'] . '&slug=' . $item['slug']);
}

echo implode("\n", $lines);
