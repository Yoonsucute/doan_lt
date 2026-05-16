# Cau truc du an CodeDoAn

Thu muc goc chi giu cac file cau hinh va trang vao chinh:

```text
doan_lt/
├── .htaccess
├── index.php
├── config.php
├── sitemap.php
├── STRUCTURE.md
├── auth/
├── projects/
├── shop/
├── actions/
├── admin/
├── chatbot/
├── includes/
├── assets/
├── database/
├── uploads/
└── storage/
```

## Nhom file chinh

- `index.php`: trang chủ CodeDoAn.
- `config.php`: ket noi database, session, helper, CSRF, helper upload.
- `.htaccess`: rewrite link dep va link cu ve dung module.
- `sitemap.php`: tao sitemap XML.

## Thu muc chuc nang

- `auth/`: `login.php`, `register.php`, `logout.php`, `profile.php`.
- `projects/`: `search.php`, `category.php`, `detail.php`, `demo.php`, `upload.php`, `edit.php`, `delete.php`, `download.php`.
- `shop/`: `cart.php`, `cart_action.php`, `checkout.php`, `orders.php`.
- `actions/`: `bookmark.php`, `follow.php`, `rating.php`, `save_comment.php`, `comment_vote.php`, `report_project.php`.
- `admin/`: `dashboard.php`, `projects.php`, `project_status.php`, `orders.php`, `order_status.php`, `categories.php`, `comments.php`, `reports.php`, `users.php`.
- `chatbot/`: `widget.php`, `chatbot.php`.
- `includes/`: `header.php`, `navbar.php`, `footer.php`.
- `assets/`: CSS, JavaScript, hinh anh tinh.
- `database/`: file SQL khoi tao database.
- `uploads/`: anh preview va file source do user upload.
- `storage/`: session/file tam khi chay local.

## Link cu

Nhung file wrapper o thu muc goc nhu `login.php`, `project_detail.php`, `cart.php` da duoc xoa de thu muc goc gon hon. Neu nguoi dung truy cap link cu, `.htaccess` se rewrite ve module moi, vi du:

- `/login.php` -> `/auth/login.php`
- `/project_detail.php?id=1` -> `/projects/detail.php?id=1`
- `/cart.php` -> `/shop/cart.php`
