<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'project_share';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die('Khong ket noi duoc database.');
}

mysqli_set_charset($conn, 'utf8mb4');

if (session_status() === PHP_SESSION_NONE) {
    $sessionPath = __DIR__ . '/storage/sessions';
    if (!is_dir($sessionPath)) {
        mkdir($sessionPath, 0777, true);
    }
    session_save_path($sessionPath);
    session_start();
}

date_default_timezone_set('Asia/Ho_Chi_Minh');

function base_url(string $path = ''): string
{
    $documentRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? __DIR__) ?: __DIR__);
    $appRoot = str_replace('\\', '/', __DIR__);
    $base = '/' . trim(str_replace($documentRoot, '', $appRoot), '/');
    $base = $base === '/' ? '' : $base;
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

function db_query(string $sql, array $params = [], string $types = '')
{
    global $conn;

    if (!$params) {
        return mysqli_query($conn, $sql);
    }

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        die('SQL error: ' . mysqli_error($conn));
    }

    if ($types === '') {
        foreach ($params as $param) {
            $types .= is_int($param) ? 'i' : (is_float($param) ? 'd' : 's');
        }
    }

    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt) ?: $stmt;
}

function db_all(string $sql, array $params = [], string $types = ''): array
{
    $result = db_query($sql, $params, $types);
    return $result instanceof mysqli_result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

function db_one(string $sql, array $params = [], string $types = ''): ?array
{
    $result = db_query($sql, $params, $types);
    if (!$result instanceof mysqli_result) {
        return null;
    }
    $row = mysqli_fetch_assoc($result);
    return $row ?: null;
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function money($value): string
{
    $amount = (int) $value;
    return $amount > 0 ? number_format($amount, 0, ',', '.') . ' VND' : 'Mien phi';
}

function discount_percent($price, $salePrice): int
{
    $price = (int) $price;
    $salePrice = (int) $salePrice;
    if ($price <= 0 || $salePrice <= 0 || $salePrice >= $price) {
        return 0;
    }
    return (int) round((($price - $salePrice) / $price) * 100);
}

function project_price(array $project): int
{
    $salePrice = (int) ($project['sale_price'] ?? 0);
    $price = (int) ($project['price'] ?? 0);
    return $salePrice > 0 ? $salePrice : $price;
}

function cart_items(): array
{
    return $_SESSION['cart'] ?? [];
}

function cart_count(): int
{
    return array_sum(array_map('intval', cart_items()));
}

function cart_total(): int
{
    $cart = cart_items();
    if (!$cart) {
        return 0;
    }

    $ids = array_map('intval', array_keys($cart));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $projects = db_all(
        "SELECT id, price, sale_price FROM projects WHERE status = 'approved' AND id IN ($placeholders)",
        $ids,
        str_repeat('i', count($ids))
    );

    $total = 0;
    foreach ($projects as $project) {
        $total += project_price($project) * (int) ($cart[$project['id']] ?? 0);
    }
    return $total;
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_admin(): bool
{
    return isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'admin';
}

function require_login(): void
{
    if (!isset($_SESSION['user'])) {
        flash('Vui long dang nhap de tiep tuc.', 'warning');
        redirect(base_url('auth/login.php'));
    }
}

function require_admin(): void
{
    if (!is_admin()) {
        flash('Ban khong co quyen truy cap khu vuc admin.', 'danger');
        redirect('../index.php');
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        die('CSRF token khong hop le.');
    }
}

function flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'][] = ['message' => $message, 'type' => $type];
}

function flashes(): array
{
    $items = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $items;
}

function slugify(string $text): string
{
    $text = function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);
    $text = trim($text);
    $text = preg_replace('/[^\p{L}\p{N}]+/u', '-', $text);
    $text = trim($text, '-');
    return $text !== '' ? $text : uniqid('project-');
}

function ensure_unique_slug(string $title, int $ignoreId = 0): string
{
    $base = slugify($title);
    $slug = $base;
    $i = 2;

    while (true) {
        $exists = db_one(
            'SELECT id FROM projects WHERE slug = ? AND id <> ? LIMIT 1',
            [$slug, $ignoreId],
            'si'
        );
        if (!$exists) {
            return $slug;
        }
        $slug = $base . '-' . $i++;
    }
}

function safe_filename(string $original, array $allowedExt, int $maxSize, string $tmpPath, int $size): string
{
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        throw new RuntimeException('Dinh dang file khong hop le.');
    }
    if ($size <= 0 || $size > $maxSize) {
        throw new RuntimeException('Dung luong file vuot gioi han.');
    }
    if (!is_uploaded_file($tmpPath)) {
        throw new RuntimeException('File upload khong hop le.');
    }
    return bin2hex(random_bytes(12)) . '.' . $ext;
}

function ensure_schema(): void
{
    global $conn;

    $columns = [];
    $result = mysqli_query($conn, "SHOW COLUMNS FROM projects");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $columns[$row['Field']] = true;
        }
    }

    if (!isset($columns['slug'])) {
        mysqli_query($conn, "ALTER TABLE projects ADD slug VARCHAR(255) NULL AFTER title");
        mysqli_query($conn, "CREATE INDEX idx_projects_slug ON projects(slug)");
    }
    if (!isset($columns['meta_title'])) {
        mysqli_query($conn, "ALTER TABLE projects ADD meta_title VARCHAR(255) NULL AFTER slug");
    }
    if (!isset($columns['price'])) {
        mysqli_query($conn, "ALTER TABLE projects ADD price INT DEFAULT 0 AFTER source_file");
    }
    if (!isset($columns['sale_price'])) {
        mysqli_query($conn, "ALTER TABLE projects ADD sale_price INT DEFAULT 0 AFTER price");
    }

    $missingSlugs = mysqli_query($conn, "SELECT id, title FROM projects WHERE slug IS NULL OR slug = ''");
    if ($missingSlugs) {
        while ($project = mysqli_fetch_assoc($missingSlugs)) {
            $slug = ensure_unique_slug($project['title'], (int) $project['id']);
            db_query('UPDATE projects SET slug = ?, meta_title = COALESCE(meta_title, title) WHERE id = ?', [$slug, (int) $project['id']], 'si');
        }
    }

    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS bookmarks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        project_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_bookmark (user_id, project_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS follows (
        id INT AUTO_INCREMENT PRIMARY KEY,
        follower_id INT NOT NULL,
        following_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_follow (follower_id, following_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS reports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        project_id INT NOT NULL,
        user_id INT NOT NULL,
        reason TEXT NOT NULL,
        status ENUM('open','resolved') DEFAULT 'open',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS comment_votes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        comment_id INT NOT NULL,
        user_id INT NOT NULL,
        vote TINYINT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_comment_vote (comment_id, user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        message VARCHAR(255) NOT NULL,
        link VARCHAR(255) DEFAULT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        customer_name VARCHAR(120) NOT NULL,
        customer_email VARCHAR(120) NOT NULL,
        customer_phone VARCHAR(30) DEFAULT NULL,
        note TEXT DEFAULT NULL,
        total INT DEFAULT 0,
        status ENUM('pending','completed','cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        project_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        price INT DEFAULT 0,
        quantity INT DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

ensure_schema();
