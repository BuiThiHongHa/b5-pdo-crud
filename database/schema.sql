-- =============================================================================
-- BÀI TẬP LỚN LẬP TRÌNH WEB - CHỦ ĐỀ 7: HỆ THỐNG BÁN HÀNG TRỰC TUYẾN MINI
-- Sinh viên thực hiện: Bùi Thị Hồng Hà
-- Mã nguồn: https://github.com/BuiThiHongHa/online-shop-mini
-- Database: mini_store_db (Engine: InnoDB, Chuẩn hóa 3NF)
-- Thiết kế độc lập, không sao chép MiniShop, có đầy đủ PK, FK và UNIQUE
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `mini_store_db` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `mini_store_db`;

-- 1. BẢNG VAI TRÒ (ROLES)
DROP TABLE IF EXISTS `inventory_logs`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `cart_items`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `roles`;

CREATE TABLE `roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `role_name` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Tên vai trò (admin, staff, customer)',
    `display_name` VARCHAR(100) NOT NULL COMMENT 'Tên hiển thị tiếng Việt',
    `description` VARCHAR(255) NULL COMMENT 'Mô tả quyền hạn'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. BẢNG TÀI KHOẢN NGƯỜI DÙNG (USERS)
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `role_id` INT NOT NULL DEFAULT 3 COMMENT 'Liên kết vai trò',
    `username` VARCHAR(60) NOT NULL UNIQUE COMMENT 'Tên đăng nhập',
    `email` VARCHAR(120) NOT NULL UNIQUE COMMENT 'Email liên hệ',
    `password_hash` VARCHAR(255) NOT NULL COMMENT 'Mật khẩu băm chuẩn BCRYPT password_hash()',
    `full_name` VARCHAR(100) NOT NULL COMMENT 'Họ và tên người dùng',
    `phone` VARCHAR(20) NULL COMMENT 'Số điện thoại',
    `address` VARCHAR(255) NULL COMMENT 'Địa chỉ giao nhận',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1: Hoạt động, 0: Khóa',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_users_roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. BẢNG DANH MỤC SẢN PHẨM (CATEGORIES)
CREATE TABLE `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(120) NOT NULL COMMENT 'Tên danh mục món',
    `slug` VARCHAR(150) NOT NULL UNIQUE COMMENT 'Đường dẫn tĩnh phục vụ SEO',
    `description` TEXT NULL COMMENT 'Mô tả danh mục',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Trạng thái hiển thị',
    `display_order` INT NOT NULL DEFAULT 0 COMMENT 'Thứ tự ưu tiên hiển thị',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. BẢNG MÓN ĂN / SẢN PHẨM & TỒN KHO (PRODUCTS)
CREATE TABLE `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL COMMENT 'Thuộc danh mục',
    `product_code` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Mã SKU quản lý',
    `name` VARCHAR(200) NOT NULL COMMENT 'Tên món ăn / đồ uống',
    `slug` VARCHAR(250) NOT NULL UNIQUE COMMENT 'Đường dẫn SEO',
    `description` TEXT NULL COMMENT 'Mô tả chi tiết',
    `original_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Giá gốc',
    `sale_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Giá bán khuyến mãi',
    `stock_quantity` INT NOT NULL DEFAULT 0 COMMENT 'Số lượng tồn kho khả dụng',
    `image_url` VARCHAR(255) NULL COMMENT 'Đường dẫn ảnh món',
    `is_published` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1: Mở bán, 0: Tạm khóa bán',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_products_categories` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `chk_stock_positive` CHECK (`stock_quantity` >= 0),
    CONSTRAINT `chk_price_positive` CHECK (`sale_price` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. BẢNG GIỎ HÀNG (CART_ITEMS)
CREATE TABLE `cart_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL COMMENT 'Khách hàng sở hữu giỏ',
    `product_id` INT NOT NULL COMMENT 'Món ăn chọn mua',
    `quantity` INT NOT NULL DEFAULT 1 COMMENT 'Số lượng đặt mua',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_cart_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_cart_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `uq_user_product` UNIQUE (`user_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. BẢNG ĐƠN HÀNG (ORDERS)
CREATE TABLE `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_code` VARCHAR(30) NOT NULL UNIQUE COMMENT 'Mã đơn dạng ORD-YYYYMMDD-XXXX',
    `user_id` INT NULL COMMENT 'ID tài khoản đặt (cho phép NULL nếu khách vãng lai)',
    `customer_name` VARCHAR(100) NOT NULL COMMENT 'Tên người nhận hàng',
    `customer_phone` VARCHAR(20) NOT NULL COMMENT 'Số điện thoại nhận hàng',
    `customer_email` VARCHAR(120) NULL COMMENT 'Email nhận thông báo',
    `shipping_address` VARCHAR(255) NOT NULL COMMENT 'Địa chỉ giao hàng chi tiết',
    `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Tổng tiền tính lại ở Server',
    `order_status` ENUM('pending', 'processing', 'completed', 'cancelled') NOT NULL DEFAULT 'pending' COMMENT 'Trạng thái xử lý',
    `payment_status` ENUM('unpaid', 'paid') NOT NULL DEFAULT 'unpaid' COMMENT 'Trạng thái thanh toán mô phỏng',
    `customer_notes` TEXT NULL COMMENT 'Ghi chú cho quán (ít đường, nhiều đá...)',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_orders_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. BẢNG CHI TIẾT ĐƠN HÀNG (ORDER_ITEMS)
CREATE TABLE `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL COMMENT 'Thuộc đơn hàng',
    `product_id` INT NOT NULL COMMENT 'Mã sản phẩm',
    `product_name` VARCHAR(200) NOT NULL COMMENT 'Tên món lưu snapshot tại thời điểm mua',
    `unit_price` DECIMAL(12,2) NOT NULL COMMENT 'Đơn giá snapshot tại thời điểm mua',
    `quantity` INT NOT NULL DEFAULT 1 COMMENT 'Số lượng đặt mua',
    `subtotal` DECIMAL(12,2) NOT NULL COMMENT 'Thành tiền = unit_price * quantity',
    CONSTRAINT `fk_order_items_orders` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_order_items_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `uq_order_product` UNIQUE (`order_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. BẢNG NHẬT KÝ BIẾN ĐỘNG KHO (INVENTORY_LOGS)
CREATE TABLE `inventory_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL COMMENT 'Sản phẩm biến động',
    `order_id` INT NULL COMMENT 'Liên kết đơn hàng nếu xuất kho',
    `change_type` ENUM('order_deduct', 'order_restore', 'manual_import', 'manual_adjust') NOT NULL COMMENT 'Loại biến động',
    `quantity_change` INT NOT NULL COMMENT 'Số lượng thay đổi (+ hoặc -)',
    `stock_after` INT NOT NULL COMMENT 'Số lượng tồn kho sau khi đổi',
    `reason` VARCHAR(255) NULL COMMENT 'Lý do biến động',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_inv_logs_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_inv_logs_orders` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
