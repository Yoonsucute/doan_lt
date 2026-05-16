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
 'Quan ly lop, sinh vien, diem va tim kiem.', 0, 0, 'asp-shoes-chatbot.svg', '014dfd0a83d16873a4a38023.zip', 0, 0, 1, 'basic', 0, 0, 0, 'pending'),
(2, 2, 'Website dat lich kham benh Laravel', 'website-dat-lich-kham-benh-laravel',
 'Website dat lich kham benh Laravel',
 'He thong dat lich kham, quan ly bac si, chuyen khoa, lich hen va thong bao email.',
 'Source Laravel phu hop lam do an tot nghiep nganh cong nghe thong tin voi day du chuc nang dat lich, xac nhan lich hen, phan quyen va dashboard thong ke.',
 'Laravel 10, MySQL, Bootstrap 5, Mailer', '1.1', '5.6 MB', '', '',
 'Chay composer install, import database, cau hinh .env, php artisan migrate --seed.',
 'Dat lich kham, quan ly bac si, lich lam viec, benh nhan, email thong bao, dashboard admin.',
 980, 214, 'asp-finance.svg', '014dfd0a83d16873a4a38023.zip', 250000, 199000, 0, 'premium', 1, 1, 214, 'approved'),
(2, 2, 'Website hoc truc tuyen Laravel LMS', 'website-hoc-truc-tuyen-laravel-lms',
 'Website hoc truc tuyen Laravel LMS',
 'Nen tang e-learning co khoa hoc, bai giang video, quiz, thanh toan va chung chi.',
 'Bo source LMS bang Laravel danh cho do an tot nghiep, co phan quyen hoc vien, giang vien, admin va luong quan ly noi dung khoa hoc.',
 'Laravel, MySQL, Bootstrap, JavaScript', '2.0', '8.4 MB', '', '',
 'Cai composer, import SQL, cau hinh .env, tao storage link va chay local server.',
 'Khoa hoc, bai giang, quiz, tien do hoc tap, binh luan, thanh toan, chung chi.',
 1450, 311, 'asp-shop-laptop.svg', 'f870c1d234719c2007e7bbe4.zip', 320000, 249000, 0, 'exclusive', 1, 0, 311, 'approved'),
(2, 3, 'REST API ban hang Spring Boot', 'rest-api-ban-hang-spring-boot',
 'REST API ban hang Spring Boot',
 'Backend API cho shop online voi JWT, san pham, gio hang, don hang va phan quyen.',
 'Do an Java Spring Boot thiet ke REST API chuan, co Spring Security JWT, JPA, MySQL va tai lieu endpoint de ket noi frontend.',
 'Java 17, Spring Boot, Spring Security, MySQL, JPA', '1.0', '4.9 MB', '', '',
 'Mo bang IntelliJ, tao database, sua application.properties, chay Maven spring-boot:run.',
 'JWT login, CRUD san pham, gio hang, don hang, role admin/user, API document.',
 760, 132, 'asp-shop-laptop.svg', '014dfd0a83d16873a4a38023.zip', 180000, 149000, 0, 'premium', 0, 1, 132, 'approved'),
(2, 3, 'Quan ly thu vien Spring Boot Thymeleaf', 'quan-ly-thu-vien-spring-boot-thymeleaf',
 'Quan ly thu vien Spring Boot Thymeleaf',
 'Website quan ly sach, doc gia, muon tra, phieu phat va thong ke thu vien.',
 'Source Spring Boot fullstack dung Thymeleaf, Bootstrap va MySQL, phu hop mon lap trinh Java web va co so du lieu.',
 'Spring Boot, Thymeleaf, MySQL, Bootstrap', '1.2', '3.8 MB', '', '',
 'Import project Maven, tao database, cap nhat cau hinh ket noi va chay ung dung.',
 'Quan ly sach, doc gia, muon tra, qua han, phieu phat, bao cao.',
 620, 105, 'asp-finance.svg', 'f870c1d234719c2007e7bbe4.zip', 120000, 99000, 0, 'premium', 0, 0, 105, 'approved'),
(2, 4, 'Phan mem quan ly quan cafe Java Swing', 'phan-mem-quan-ly-quan-cafe-java-swing',
 'Phan mem quan ly quan cafe Java Swing',
 'Ung dung desktop quan ly ban, order, menu, hoa don va doanh thu quan cafe.',
 'Source Java Swing ket noi MySQL cho bai tap lon lap trinh huong doi tuong, giao dien de demo va day du CRUD.',
 'Java Swing, MySQL, JDBC, JasperReport', '1.0', '2.2 MB', '', '',
 'Mo bang NetBeans, import database, them JDBC driver va chay Main.java.',
 'So do ban, goi mon, in hoa don, quan ly menu, nhan vien, thong ke doanh thu.',
 910, 260, 'asp-milktea.svg', '014dfd0a83d16873a4a38023.zip', 0, 0, 1, 'basic', 1, 1, 260, 'approved'),
(2, 4, 'Quan ly diem sinh vien Java Swing', 'quan-ly-diem-sinh-vien-java-swing',
 'Quan ly diem sinh vien Java Swing',
 'Do an desktop quan ly lop, sinh vien, mon hoc, diem va xep loai.',
 'Ung dung Java Swing don gian nhung day du nghiep vu cho mon lap trinh Java, su dung JDBC va MySQL.',
 'Java Swing, MySQL, JDBC', '1.0', '1.6 MB', '', '',
 'Import SQL, sua thong tin JDBC trong file config va chay project.',
 'Quan ly sinh vien, lop, mon hoc, diem, tim kiem, xuat danh sach.',
 520, 148, 'asp-shoes-chatbot.svg', 'f870c1d234719c2007e7bbe4.zip', 0, 0, 1, 'basic', 0, 0, 148, 'approved'),
(2, 5, 'Nhan dien khuon mat Python OpenCV', 'nhan-dien-khuon-mat-python-opencv',
 'Nhan dien khuon mat Python OpenCV',
 'Source Python nhan dien khuon mat bang webcam, luu dataset va diem danh tu dong.',
 'Do an Python ung dung OpenCV de nhan dien khuon mat, phu hop de tai AI co demo truc quan va bao cao de trinh bay.',
 'Python, OpenCV, SQLite, Tkinter', '1.0', '6.2 MB', '', '',
 'Cai pip install -r requirements.txt, chay train.py de train dataset, sau do chay main.py.',
 'Thu thap anh, train model, nhan dien webcam, diem danh, xuat file Excel.',
 1880, 490, 'asp-finance.svg', '014dfd0a83d16873a4a38023.zip', 220000, 179000, 0, 'exclusive', 1, 1, 490, 'approved'),
(2, 5, 'Chatbot tu van tuyen sinh Python Flask', 'chatbot-tu-van-tuyen-sinh-python-flask',
 'Chatbot tu van tuyen sinh Python Flask',
 'Website chatbot hoi dap tuyen sinh, tra cuu nganh hoc, diem chuan va lich nop ho so.',
 'Source Flask ket hop giao dien web va bo cau hoi mau, co the mo rong bang API AI hoac tap du lieu FAQ cua truong.',
 'Python, Flask, SQLite, Bootstrap, JavaScript', '1.1', '3.5 MB', '', '',
 'Tao virtualenv, cai requirements, import data FAQ va chay flask run.',
 'Hoi dap FAQ, goi y nganh hoc, quan ly cau hoi, lich su chat, dashboard admin.',
 1320, 337, 'asp-shoes-chatbot.svg', 'f870c1d234719c2007e7bbe4.zip', 0, 0, 1, 'basic', 1, 0, 337, 'approved'),
(2, 6, 'Website quan ly phong tro ASP.NET MVC', 'website-quan-ly-phong-tro-asp-net-mvc',
 'Website quan ly phong tro ASP.NET MVC',
 'He thong quan ly phong, hop dong, dien nuoc, hoa don va khach thue.',
 'Source ASP.NET MVC ket noi SQL Server, phu hop do an mon lap trinh web .NET voi nghiep vu phong tro thuc te.',
 'ASP.NET MVC, SQL Server, Entity Framework, Bootstrap', '1.0', '4.3 MB', '', '',
 'Restore NuGet, import SQL Server script, sua connection string va chay IIS Express.',
 'Phong tro, khach thue, hop dong, chi so dien nuoc, hoa don, thanh toan.',
 790, 176, 'asp-finance.svg', '014dfd0a83d16873a4a38023.zip', 160000, 129000, 0, 'premium', 0, 1, 176, 'approved'),
(2, 6, 'Website ban ve xem phim ASP.NET Core', 'website-ban-ve-xem-phim-asp-net-core',
 'Website ban ve xem phim ASP.NET Core',
 'Dat ve rap phim online voi lich chieu, ghe ngoi, combo, thanh toan va QR ve.',
 'Do an ASP.NET Core MVC co Entity Framework, SQL Server, phan quyen admin va giao dien dat ghe truc quan.',
 'ASP.NET Core, SQL Server, EF Core, Bootstrap', '1.0', '7.1 MB', '', '',
 'Restore package, update database, sua appsettings.json va chay dotnet run.',
 'Quan ly phim, phong chieu, lich chieu, dat ghe, combo bap nuoc, ve QR.',
 1110, 219, 'asp-shop-laptop.svg', 'f870c1d234719c2007e7bbe4.zip', 260000, 210000, 0, 'exclusive', 1, 0, 219, 'approved'),
(2, 7, 'Website dat do an React NodeJS', 'website-dat-do-an-react-nodejs',
 'Website dat do an React NodeJS',
 'Ung dung dat mon online co React frontend, NodeJS API, gio hang va quan ly don.',
 'Source fullstack React va Express cho do an web hien dai, co API rieng, UI responsive va dashboard admin.',
 'ReactJS, NodeJS, Express, MongoDB, Tailwind CSS', '1.0', '9.6 MB', '', '',
 'Chay npm install cho client/server, cau hinh .env, npm run dev.',
 'Dang nhap JWT, danh muc, san pham, gio hang, don hang, admin dashboard.',
 1680, 402, 'asp-milktea.svg', '014dfd0a83d16873a4a38023.zip', 300000, 239000, 0, 'exclusive', 1, 1, 402, 'approved'),
(2, 7, 'Dashboard quan ly cong viec React NodeJS', 'dashboard-quan-ly-cong-viec-react-nodejs',
 'Dashboard quan ly cong viec React NodeJS',
 'He thong task management co kanban, deadline, thanh vien, binh luan va thong bao.',
 'Bo source fullstack dung React, Express va MySQL, phu hop de tai quan ly du an nho trong doanh nghiep.',
 'ReactJS, NodeJS, Express, MySQL, Socket.IO', '1.0', '7.8 MB', '', '',
 'Import database, chay npm install, cau hinh env cho server va client.',
 'Kanban board, giao viec, deadline, file dinh kem, binh luan, thong bao realtime.',
 930, 187, 'asp-finance.svg', 'f870c1d234719c2007e7bbe4.zip', 210000, 169000, 0, 'premium', 0, 0, 187, 'approved'),
(2, 8, 'App ban hang Flutter Firebase', 'app-ban-hang-flutter-firebase',
 'App ban hang Flutter Firebase',
 'Ung dung mobile ban hang voi dang nhap, danh muc, gio hang, yeu thich va don hang.',
 'Source Flutter ket hop Firebase Auth va Firestore, giao dien dep, phu hop do an mobile va demo tren Android.',
 'Flutter, Dart, Firebase Auth, Firestore', '1.0', '10.4 MB', '', '',
 'Chay flutter pub get, cau hinh Firebase, ket noi thiet bi va flutter run.',
 'Dang nhap, danh muc, tim kiem, gio hang, dat hang, yeu thich, profile.',
 1530, 356, 'asp-shop-laptop.svg', '014dfd0a83d16873a4a38023.zip', 280000, 229000, 0, 'premium', 1, 1, 356, 'approved'),
(2, 8, 'App quan ly chi tieu Flutter SQLite', 'app-quan-ly-chi-tieu-flutter-sqlite',
 'App quan ly chi tieu Flutter SQLite',
 'App mobile theo doi thu chi, ngan sach, bieu do va lich su giao dich offline.',
 'Do an Flutter offline dung SQLite local, giao dien gon de demo va co bieu do thong ke theo thang.',
 'Flutter, Dart, SQLite, fl_chart', '1.0', '4.7 MB', '', '',
 'Chay flutter pub get, build tren Android Studio hoac VS Code.',
 'Thu chi, danh muc, ngan sach, bao cao bieu do, tim kiem giao dich.',
 870, 243, 'asp-finance.svg', 'f870c1d234719c2007e7bbe4.zip', 0, 0, 1, 'basic', 0, 0, 243, 'approved'),
(2, 9, 'App nghe nhac Android Kotlin', 'app-nghe-nhac-android-kotlin',
 'App nghe nhac Android Kotlin',
 'Ung dung nghe nhac offline/online co playlist, tim kiem, yeu thich va player mini.',
 'Source Android Kotlin dung Room database va MediaPlayer, phu hop mon lap trinh di dong.',
 'Android, Kotlin, Room, Retrofit', '1.0', '6.8 MB', '', '',
 'Mo bang Android Studio, sync Gradle, cap quyen storage va chay tren emulator.',
 'Playlist, player, yeu thich, tim kiem bai hat, lich su nghe, dark mode.',
 740, 151, 'asp-shoes-chatbot.svg', '014dfd0a83d16873a4a38023.zip', 130000, 99000, 0, 'premium', 0, 0, 151, 'approved'),
(2, 9, 'App dat xe cong nghe Android Java', 'app-dat-xe-cong-nghe-android-java',
 'App dat xe cong nghe Android Java',
 'Do an Android dat xe co ban do, tai xe, khach hang, cuoc xe va lich su thanh toan.',
 'Source Android Java mo phong ung dung dat xe, co backend API mau va luong dat cuoc co ban de demo.',
 'Android Java, Google Maps API, Firebase, REST API', '1.0', '12.2 MB', '', '',
 'Mo Android Studio, them Google Maps key, cau hinh Firebase va chay ung dung.',
 'Dang nhap, chon diem don/den, goi xe, theo doi tai xe, lich su cuoc xe.',
 1180, 205, 'asp-shop-laptop.svg', 'f870c1d234719c2007e7bbe4.zip', 350000, 279000, 0, 'exclusive', 1, 0, 205, 'approved'),
(2, 10, 'Website ban my pham PHP MySQL', 'website-ban-my-pham-php-mysql',
 'Website ban my pham PHP MySQL',
 'Shop my pham co san pham, danh muc, gio hang, voucher, binh luan va admin.',
 'Source PHP MySQL giao dien ban hang dep, de tuy bien san pham va phu hop do an web thuong mai dien tu.',
 'PHP, MySQL, Bootstrap, jQuery', '1.0', '3.6 MB', '', '',
 'Import database, cau hinh config.php, copy vao htdocs va chay localhost.',
 'San pham, danh muc, gio hang, voucher, don hang, binh luan, dashboard.',
 2100, 620, 'asp-shop-laptop.svg', '014dfd0a83d16873a4a38023.zip', 0, 0, 1, 'basic', 1, 1, 620, 'approved'),
(2, 10, 'Website ban giay sneaker PHP MySQL', 'website-ban-giay-sneaker-php-mysql',
 'Website ban giay sneaker PHP MySQL',
 'Website ban giay co size, mau sac, gio hang, thanh toan COD va quan ly kho.',
 'Do an PHP MySQL ve shop sneaker, co bien the san pham, anh chi tiet va quan ly don hang cho admin.',
 'PHP, MySQL, Bootstrap 5, Ajax', '1.1', '4.2 MB', '', '',
 'Giai nen source, import SQL, sua config database va dang nhap admin.',
 'Bien the size mau, gio hang, checkout, quan ly kho, don hang, doanh thu.',
 1740, 455, 'asp-shoes-chatbot.svg', 'f870c1d234719c2007e7bbe4.zip', 150000, 99000, 0, 'premium', 1, 0, 455, 'approved'),
(2, 12, 'Quan ly sinh vien PHP MySQL', 'quan-ly-sinh-vien-php-mysql',
 'Quan ly sinh vien PHP MySQL',
 'He thong quan ly sinh vien, lop, khoa, mon hoc, diem va hoc phi.',
 'Source web PHP thuan cho de tai quan ly sinh vien, phu hop mon co so du lieu va lap trinh web.',
 'PHP, MySQL, Bootstrap, DataTables', '1.0', '2.8 MB', '', '',
 'Import database, cau hinh ket noi, dang nhap tai khoan admin trong SQL.',
 'Sinh vien, lop, khoa, mon hoc, diem, hoc phi, tim kiem, xuat Excel.',
 1250, 332, 'asp-finance.svg', '014dfd0a83d16873a4a38023.zip', 0, 0, 1, 'basic', 0, 1, 332, 'approved'),
(2, 13, 'Quan ly kho vat tu Laravel', 'quan-ly-kho-vat-tu-laravel',
 'Quan ly kho vat tu Laravel',
 'He thong quan ly vat tu, nha cung cap, phieu nhap xuat, ton kho va canh bao sap het.',
 'Source Laravel cho nghiep vu kho vat tu, co phan quyen nhan vien, admin va bao cao ton kho chi tiet.',
 'Laravel, MySQL, Bootstrap, Chart.js', '1.0', '5.1 MB', '', '',
 'Cai composer, import database, cau hinh .env va chay php artisan serve.',
 'Vat tu, nha cung cap, nhap xuat kho, canh bao ton thap, bao cao thong ke.',
 1020, 267, 'asp-finance.svg', 'f870c1d234719c2007e7bbe4.zip', 190000, 149000, 0, 'premium', 0, 0, 267, 'approved'),
(2, 14, 'Quan ly nha hang PHP MySQL', 'quan-ly-nha-hang-php-mysql',
 'Quan ly nha hang PHP MySQL',
 'Website quan ly ban an, thuc don, order, bep, hoa don va thong ke doanh thu.',
 'Source quan ly nha hang bang PHP MySQL, co giao dien nhan vien order va trang admin quan ly nghiep vu.',
 'PHP, MySQL, Bootstrap, Ajax', '1.0', '3.3 MB', '', '',
 'Copy vao htdocs, import SQL, sua config va mo trinh duyet.',
 'Ban an, thuc don, goi mon, bep, hoa don, nhan vien, doanh thu.',
 940, 226, 'asp-milktea.svg', '014dfd0a83d16873a4a38023.zip', 170000, 135000, 0, 'premium', 0, 1, 226, 'approved'),
(2, 15, 'Do an tot nghiep san giao dich viec lam', 'do-an-tot-nghiep-san-giao-dich-viec-lam',
 'Do an tot nghiep san giao dich viec lam',
 'Website viec lam co nha tuyen dung, ung vien, CV, tin tuyen dung va ung tuyen online.',
 'Bo source do an tot nghiep quy mo lon, co day du nghiep vu san giao dich viec lam va phan quyen nhieu vai tro.',
 'Laravel, MySQL, Bootstrap, JavaScript', '1.0', '11.5 MB', '', '',
 'Cai composer, import database, cau hinh mail va storage, chay artisan serve.',
 'Dang tin viec, nop CV, quan ly ung vien, nha tuyen dung, goi dich vu, admin duyet tin.',
 2260, 510, 'asp-shop-laptop.svg', 'f870c1d234719c2007e7bbe4.zip', 450000, 349000, 0, 'exclusive', 1, 1, 510, 'approved'),
(2, 15, 'Do an tot nghiep quan ly benh vien', 'do-an-tot-nghiep-quan-ly-benh-vien',
 'Do an tot nghiep quan ly benh vien',
 'He thong quan ly benh vien voi tiep nhan, kham benh, don thuoc, vien phi va bao cao.',
 'Source do an tot nghiep phan tich nghiep vu benh vien, phu hop bao cao cuoi khoa va demo theo quy trinh.',
 'ASP.NET Core, SQL Server, Bootstrap, EF Core', '1.0', '13.8 MB', '', '',
 'Restore package, import database SQL Server, sua appsettings va chay ung dung.',
 'Benh nhan, bac si, lich kham, don thuoc, vien phi, bao cao, phan quyen.',
 1980, 420, 'asp-finance.svg', '014dfd0a83d16873a4a38023.zip', 500000, 399000, 0, 'exclusive', 1, 0, 420, 'approved'),
(2, 16, 'Code mien phi portfolio ca nhan HTML CSS JS', 'code-mien-phi-portfolio-ca-nhan-html-css-js',
 'Code mien phi portfolio ca nhan HTML CSS JS',
 'Template portfolio ca nhan responsive co gioi thieu, ky nang, du an va lien he.',
 'Source front-end mien phi de sinh vien lam CV online hoac bai tap HTML CSS JavaScript.',
 'HTML, CSS, JavaScript, Bootstrap', '1.0', '0.8 MB', '', '',
 'Mo file index.html bang trinh duyet hoac chay bang Live Server.',
 'Hero, gioi thieu, ky nang, du an, form lien he, responsive mobile.',
 3020, 1280, 'asp-shop-laptop.svg', 'f870c1d234719c2007e7bbe4.zip', 0, 0, 1, 'basic', 1, 1, 1280, 'approved'),
(2, 16, 'Code mien phi landing page khoa hoc online', 'code-mien-phi-landing-page-khoa-hoc-online',
 'Code mien phi landing page khoa hoc online',
 'Landing page gioi thieu khoa hoc co bang gia, giang vien, FAQ va form dang ky.',
 'Template HTML CSS JS mien phi, de tuy bien cho bai tap thiet ke web hoac trang gioi thieu san pham.',
 'HTML, CSS, JavaScript', '1.0', '0.6 MB', '', '',
 'Giai nen va mo index.html, sua noi dung trong file HTML.',
 'Hero, loi ich, chuong trinh hoc, bang gia, FAQ, form dang ky.',
 2410, 970, 'asp-milktea.svg', '014dfd0a83d16873a4a38023.zip', 0, 0, 1, 'basic', 0, 1, 970, 'approved'),
(2, 17, 'Code premium CRM quan ly khach hang', 'code-premium-crm-quan-ly-khach-hang',
 'Code premium CRM quan ly khach hang',
 'He thong CRM quan ly khach hang, lead, pipeline ban hang, hop dong va nhac viec.',
 'Source premium cho de tai quan ly quan he khach hang, co dashboard, bao cao va phan quyen theo nhan vien.',
 'Laravel, MySQL, Bootstrap, Chart.js', '2.1', '9.2 MB', '', '',
 'Cai composer, import SQL, cau hinh .env, tao storage link va chay server.',
 'Lead, khach hang, pipeline, hop dong, lich hen, nhac viec, bao cao doanh so.',
 1370, 288, 'asp-finance.svg', 'f870c1d234719c2007e7bbe4.zip', 380000, 299000, 0, 'premium', 1, 0, 288, 'approved'),
(2, 17, 'Code premium SaaS quan ly phong gym', 'code-premium-saas-quan-ly-phong-gym',
 'Code premium SaaS quan ly phong gym',
 'Website quan ly phong gym co goi tap, hoi vien, HLV, lich tap, diem danh va doanh thu.',
 'Bo source SaaS mini cho phong gym, co giao dien quan ly hien dai va cac module nghiep vu de bao cao.',
 'ReactJS, NodeJS, Express, MySQL', '1.0', '10.1 MB', '', '',
 'Cai npm install cho client/server, import SQL, cau hinh .env va chay dev.',
 'Hoi vien, goi tap, HLV, lich tap, diem danh QR, hoa don, dashboard doanh thu.',
 1590, 315, 'asp-shop-laptop.svg', '014dfd0a83d16873a4a38023.zip', 420000, 329000, 0, 'premium', 1, 1, 315, 'approved');

UPDATE projects
SET video_demo = CASE
    WHEN tech_stack LIKE '%Flutter%' THEN 'https://www.youtube.com/embed/1ukSR1GRtMU'
    WHEN tech_stack LIKE '%React%' OR tech_stack LIKE '%Node%' THEN 'https://www.youtube.com/embed/SqcY0GlETPk'
    WHEN tech_stack LIKE '%Laravel%' OR tech_stack LIKE '%PHP%' THEN 'https://www.youtube.com/embed/OK_JCtrrv-c'
    WHEN tech_stack LIKE '%Python%' THEN 'https://www.youtube.com/embed/rfscVS0vtbw'
    WHEN tech_stack LIKE '%Spring%' OR tech_stack LIKE '%Java%' THEN 'https://www.youtube.com/embed/eIrMbAQSU34'
    WHEN tech_stack LIKE '%ASP.NET%' THEN 'https://www.youtube.com/embed/BfEjDD8mWYg'
    ELSE 'https://www.youtube.com/embed/UB1O30fR-EE'
END
WHERE video_demo IS NULL OR video_demo = '';
