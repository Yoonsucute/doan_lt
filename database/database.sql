CREATE DATABASE project_share;
USE project_share;

-- USERS
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    avatar VARCHAR(255) DEFAULT 'default.png',
    role ENUM('user','admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- CATEGORIES
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    slug VARCHAR(120)
);

-- PROJECTS
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    category_id INT,
    title VARCHAR(255),
    slug VARCHAR(255),
    meta_title VARCHAR(255),
    short_description VARCHAR(255),
    description TEXT,
    tech_stack VARCHAR(255),
    version VARCHAR(50),
    file_size VARCHAR(50),
    demo_link VARCHAR(255),
    video_demo VARCHAR(255),
    install_guide TEXT,
    main_features TEXT,
    views INT DEFAULT 0,
    downloads_count INT DEFAULT 0,
    image VARCHAR(255),
    source_file VARCHAR(255),
    price INT DEFAULT 0,
    sale_price INT DEFAULT 0,
    is_free TINYINT(1) DEFAULT 0,
    tier ENUM('basic','premium','exclusive') DEFAULT 'basic',
    is_featured TINYINT(1) DEFAULT 0,
    is_hot TINYINT(1) DEFAULT 0,
    downloads INT DEFAULT 0,
    status ENUM('pending','approved','rejected','hidden') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- COMMENTS
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    user_id INT,
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- RATINGS
CREATE TABLE ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    user_id INT,
    star INT
);

CREATE TABLE bookmarks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    project_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_bookmark (user_id, project_id)
);

CREATE TABLE follows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    follower_id INT NOT NULL,
    following_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_follow (follower_id, following_id)
);

CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    user_id INT NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('open','resolved') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE comment_votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    comment_id INT NOT NULL,
    user_id INT NOT NULL,
    vote TINYINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_comment_vote (comment_id, user_id)
);

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message VARCHAR(255) NOT NULL,
    link VARCHAR(255),
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    customer_name VARCHAR(120) NOT NULL,
    customer_email VARCHAR(120) NOT NULL,
    customer_phone VARCHAR(30),
    note TEXT,
    total INT DEFAULT 0,
    status ENUM('pending','paid','completed','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    project_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    price INT DEFAULT 0,
    quantity INT DEFAULT 1
);

CREATE TABLE downloads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    project_id INT NOT NULL,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    project_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_cart_item (user_id, project_id)
);

INSERT INTO categories(name, slug)
VALUES
('PHP & MySQL','php-mysql'),
('Laravel','laravel'),
('Java Spring','java-spring'),
('Java Swing','java-swing'),
('Python','python'),
('ASP.NET','asp-net'),
('ReactJS / NodeJS','react-node'),
('Flutter','flutter'),
('Android','android'),
('Website ban hang','website-ban-hang'),
('Quan ly khach san','quan-ly-khach-san'),
('Quan ly sinh vien','quan-ly-sinh-vien'),
('Quan ly kho','quan-ly-kho'),
('Quan ly nha hang','quan-ly-nha-hang'),
('Do an tot nghiep','do-an-tot-nghiep'),
('Code miễn phí','code-mien-phi'),
('Code premium','code-premium');

INSERT INTO users(name, email, password, role)
VALUES
('Quan tri CodeDoAn', 'admin@codedoan.test', '$2y$10$j9AMzCuNJjPl.M3mmOcTMu4/H0YMe7tqLj0.aMsyIijL8/u7vtbNC', 'admin'),
('Nguyen Van An', 'user@codedoan.test', '$2y$10$A7Y99sYqjwz4JmmW89HtnOhvc4DX8EgiD1lSEDMRAI3GGQquSyql.', 'user');

INSERT INTO projects(
    user_id, category_id, title, slug, meta_title, short_description, description,
    tech_stack, version, file_size, demo_link, video_demo, install_guide, main_features,
    views, downloads_count, image, source_file, price, sale_price, is_free, tier,
    is_featured, is_hot, downloads, status
)
VALUES
(2, 1, 'Website ban hang PHP MySQL full chuc nang', 'website-ban-hang-php-mysql-full-chuc-nang',
 'Website ban hang PHP MySQL full chuc nang',
 'Source website ban hang PHP MySQL co gio hang, checkout, admin quan ly san pham va don hang.',
 'Bộ source phù hợp làm đồ án môn lập trình web: quản lý sản phẩm, danh mục, giỏ hàng, đặt hàng, đăng nhập và trang quản trị.',
 'PHP, MySQL, Bootstrap 5', '1.0', '2.4 MB', '', '',
 'Giái nen source, import file SQL, cau hinh config.php, chay tren XAMPP.',
 'Đăng nhập, quan ly san pham, gio hang, checkout, quan ly don hang, dashboard admin.',
 1200, 350, 'asp-shop-laptop.svg', '014dfd0a83d16873a4a38023.zip', 0, 0, 1, 'basic', 1, 1, 350, 'approved'),
(2, 10, 'Source quan ly kho hang PHP MySQL', 'source-quan-ly-kho-hang-php-mysql',
 'Source quan ly kho hang PHP MySQL',
 'Đồ án quản lý kho có nhập xuất tồn, nhà cung cấp, báo cáo và phân quyền.',
 'Source quản lý kho hàng dùng PHP thuần và MySQL, phù hợp báo cáo môn cơ sở dữ liệu và lập trình web.',
 'PHP, MySQL, Bootstrap 5, Chart.js', '1.2', '3.1 MB', '', '',
 'Import database, sua thong tin ket noi, dang nhap bang tai khoan admin duoc tao trong SQL.',
 'Nhập kho, xuất kho, tồn kho, sản phẩm, nhà cung cấp, báo cáo doanh thu.',
 860, 120, 'asp-finance.svg', 'f870c1d234719c2007e7bbe4.zip', 150000, 120000, 0, 'premium', 1, 0, 120, 'approved'),
(2, 11, 'Do an quan ly khach san Java Swing', 'do-an-quan-ly-khach-san-java-swing',
 'Do an quan ly khach san Java Swing',
 'Source Java Swing quan ly phong, dat phong, khach hang va hoa don.',
 'Do an Java Swing ket noi MySQL, co giao dien desktop va cac chuc nang CRUD co ban cho nghiep vu khach san.',
 'Java Swing, MySQL, JDBC', '1.0', '1.8 MB', '', '',
 'Mo project bang NetBeans hoac IntelliJ, import database MySQL, sua chuoi JDBC.',
 'Quan ly phong, dat phong, tra phong, khach hang, hoa don.',
 430, 76, 'asp-milktea.svg', 'f870c1d234719c2007e7bbe4.zip', 99000, 0, 0, 'exclusive', 0, 1, 76, 'approved'),
(2, 5, 'Source Python quản lý sinh viên đang chờ duyệt', 'source-python-quan-ly-sinh-vien-pending',
 'Source Python quản lý sinh viên đang chờ duyệt',
 'Source mẫu ở trạng thái pending để admin demo thao tác duyệt bài.',
 'Đây là source mẫu dùng cho luồng demo admin duyệt source. Sau khi admin chuyển approved, bài sẽ hiển thị ngoài trang chủ và danh sách.',
 'Python, SQLite, Tkinter', '1.0', '1.2 MB', '', '',
 'Chay file main.py bang Python 3.',
 'Quan ly lop, sinh vien, diem va tim kiem.', 0, 0, 'asp-shoes-chatbot.svg', '014dfd0a83d16873a4a38023.zip', 0, 0, 1, 'basic', 0, 0, 0, 'pending');
