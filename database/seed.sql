-- =============================================================================
-- BÀI TẬP LỚN LẬP TRÌNH WEB - CHỦ ĐỀ 7: HỆ THỐNG BÁN HÀNG TRỰC TUYẾN MINI
-- Sinh viên thực hiện: Bùi Thị Hồng Hà
-- Mã nguồn: https://github.com/BuiThiHongHa/online-shop-mini
-- Database Seed Data: Dữ liệu mẫu tiếng Việt chân thực & kịch bản demo
-- =============================================================================

USE `mini_store_db`;

-- 1. SEED ROLES
INSERT INTO `roles` (`id`, `role_name`, `display_name`, `description`) VALUES
(1, 'admin', 'Quản trị viên', 'Toàn quyền cấu hình hệ thống, quản lý tài khoản, sản phẩm và kho hàng'),
(2, 'staff', 'Nhân viên bán hàng', 'Xác nhận đơn, cập nhật trạng thái đơn, chuẩn bị hàng hóa'),
(3, 'customer', 'Khách hàng', 'Xem sản phẩm, thêm giỏ hàng, đặt mua và theo dõi đơn hàng');

-- 2. SEED USERS
-- password_hash cho '1234567890': $2y$10$xMUlpLICm1fPZryMgfgkn.hS.xnCYlQBtVVsYrXXch39UKobBjoIO
INSERT INTO `users` (`id`, `role_id`, `username`, `email`, `password_hash`, `full_name`, `phone`, `address`, `is_active`) VALUES
(1, 1, 'admin', 'admin@example.com', '$2y$10$xMUlpLICm1fPZryMgfgkn.hS.xnCYlQBtVVsYrXXch39UKobBjoIO', 'Hà Bùi (Quản trị)', '0901234567', 'Số 98 phố Dương Quảng Hàm, Cầu Giấy, Hà Nội', 1),
(2, 2, 'staff_lan', 'staff@example.com', '$2y$10$xMUlpLICm1fPZryMgfgkn.hS.xnCYlQBtVVsYrXXch39UKobBjoIO', 'Nguyễn Thị Lan (Thu ngân)', '0912345678', 'Số 12 Chùa Láng, Đống Đa, Hà Nội', 1),
(3, 3, 'customer_minh', 'minh.nguyen@example.com', '$2y$10$xMUlpLICm1fPZryMgfgkn.hS.xnCYlQBtVVsYrXXch39UKobBjoIO', 'Trần Văn Minh', '0987654321', 'Số 45 Trần Đại Nghĩa, Hai Bà Trưng, Hà Nội', 1),
(4, 3, 'customer_hoa', 'hoa.pham@example.com', '$2y$10$xMUlpLICm1fPZryMgfgkn.hS.xnCYlQBtVVsYrXXch39UKobBjoIO', 'Phạm Quỳnh Hoa', '0978112233', 'Ký túc xá Đại học Thủ đô Hà Nội', 1);

-- 3. SEED CATEGORIES
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `is_active`, `display_order`) VALUES
(1, 'Trà Sữa & Topping', 'tra-sua-topping', 'Trà sữa nấu thủ công đậm vị trà, kem béo ngậy cùng đa dạng loại topping', 1, 1),
(2, 'Đồ Ăn Vặt Nóng Hổi', 'do-an-vat-nong-hoi', 'Các món chiên rán giòn tan thơm ngon, sốt chấm đậm đà', 1, 2),
(3, 'Bánh Mặn & Bánh Ngọt', 'banh-man-banh-ngot', 'Bánh xèo truyền thống, bánh flan caramel béo mịn tự làm', 1, 3),
(4, 'Trà Trái Cây Thanh Nhiệt', 'tra-trai-cay-thanh-nhiet', 'Nước giải khát hoa quả tươi mát, giải độc thanh lọc cơ thể', 1, 4);

-- 4. SEED PRODUCTS (Món số 3 tồn kho = 2 dùng để demo kịch bản Mục H trong đề cương)
INSERT INTO `products` (`id`, `category_id`, `product_code`, `name`, `slug`, `description`, `original_price`, `sale_price`, `stock_quantity`, `image_url`, `is_published`) VALUES
(1, 1, 'TS-THAI-01', 'Trà Sữa Thái Xanh Trân Châu Hoàng Kim', 'tra-sua-thai-xanh-tran-chau', 'Vị trà Thái thơm nồng kết hợp sữa tươi thanh béo và trân châu dai giòn sần sật', 25000.00, 20000.00, 50, 'public/upload/products/tra-sua-thai-xanh.jpg', 1),
(2, 1, 'TS-SOCOLA-02', 'Trà Sữa Socola Đậm Vị Pudding Trứng', 'tra-sua-socola-dam-vi-pudding', 'Cacao nguyên chất hòa quyện cùng cốt trà đen, thêm lớp pudding mềm mịn tan trong miệng', 30000.00, 25000.00, 35, 'public/upload/products/tra-sua-socola.jpg', 1),
(3, 1, 'TS-PUDDING-DEMO', 'Pudding Thạch Trái Cây Đủ Vị (Món Demo Kịch Bản)', 'pudding-thach-trai-cay-demo', 'Thạch pudding handmade dẻo mát, dùng làm sản phẩm mẫu kiểm thử giới hạn tồn kho', 15000.00, 12000.00, 2, 'public/upload/products/pudding-thach.jpg', 1),
(4, 2, 'AV-CAVIEN-01', 'Cá Viên Chiên Mắm Tỏi Giòn Cay', 'ca-vien-chien-mam-toi', 'Cá viên chiên phồng đảo sốt mắm tỏi ớt kẹo sền sệt, ăn kèm dưa leo rau răm', 25000.00, 22000.00, 40, 'public/upload/products/ca-vien-chien.jpg', 1),
(5, 2, 'AV-BOVIEN-02', 'Bò Viên Chiên Sốt Me Chua Cay', 'bo-vien-chien-sot-me', 'Bò viên loại 1 dai giòn sừn sựt, quyện cùng sốt me chua ngọt đậm đà', 30000.00, 28000.00, 25, 'public/upload/products/bo-vien-chien.jpg', 1),
(6, 3, 'BM-BANH-XEO-01', 'Bánh Xèo Chảo Tôm Nhảy Rau Rừng', 'banh-xeo-chao-tom-nhay', 'Vỏ bánh mỏng giòn rụm màu nghệ vàng ươm, nhân tôm thịt tươi roi rói', 45000.00, 40000.00, 15, 'public/upload/products/banh-xeo.jpg', 1),
(7, 3, 'BN-FLAN-02', 'Bánh Flan Caramel Cà Phê Cốt Dừa', 'banh-flan-caramel-cot-dua', 'Hộp 5 chiếc bánh flan cốt trứng sữa tươi ngậy béo, thơm cà phê phin', 35000.00, 30000.00, 20, 'public/upload/products/banh-flan.jpg', 1),
(8, 4, 'TC-TRATAC-01', 'Trà Tắc Mật Ong Nha Đam Thanh Mát', 'tra-tac-mat-ong-nha-dam', 'Trà nhài ủ lạnh lắc cùng tắc tươi và mật ong hoa rừng nguyên chất', 18000.00, 15000.00, 60, 'public/upload/products/tra-tac.jpg', 1);

-- 5. SEED CART_ITEMS
INSERT INTO `cart_items` (`user_id`, `product_id`, `quantity`) VALUES
(3, 1, 2),
(3, 4, 1),
(4, 8, 3);

-- 6. SEED ORDERS
INSERT INTO `orders` (`id`, `order_code`, `user_id`, `customer_name`, `customer_phone`, `customer_email`, `shipping_address`, `total_amount`, `order_status`, `payment_status`, `customer_notes`, `created_at`) VALUES
(1, 'ORD-20261024-001', 3, 'Trần Văn Minh', '0987654321', 'minh.nguyen@example.com', 'Số 45 Trần Đại Nghĩa, Hai Bà Trưng, Hà Nội', 62000.00, 'completed', 'paid', 'Giao lúc 11h trưa, trà sữa ít đá 50% đường', '2026-10-24 10:15:00'),
(2, 'ORD-20261025-002', 4, 'Phạm Quỳnh Hoa', '0978112233', 'hoa.pham@example.com', 'Phòng 402 Ký túc xá Đại học Thủ đô Hà Nội', 45000.00, 'processing', 'unpaid', 'Giao tại cổng trường, gọi trước 5 phút', '2026-10-25 08:30:00'),
(3, 'ORD-20261025-003', NULL, 'Lê Hoàng Nam (Khách vãng lai)', '0933445566', 'nam.le@example.com', 'Số 98 phố Dương Quảng Hàm, Cầu Giấy, Hà Nội', 40000.00, 'pending', 'unpaid', 'Giao gấp trong 30 phút', '2026-10-25 09:45:00');

-- 7. SEED ORDER_ITEMS
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `unit_price`, `quantity`, `subtotal`) VALUES
(1, 1, 1, 'Trà Sữa Thái Xanh Trân Châu Hoàng Kim', 20000.00, 2, 40000.00),
(2, 1, 4, 'Cá Viên Chiên Mắm Tỏi Giòn Cay', 22000.00, 1, 22000.00),
(3, 2, 8, 'Trà Tắc Mật Ong Nha Đam Thanh Mát', 15000.00, 3, 45000.00),
(4, 3, 6, 'Bánh Xèo Chảo Tôm Nhảy Rau Rừng', 40000.00, 1, 40000.00);

-- 8. SEED INVENTORY_LOGS
INSERT INTO `inventory_logs` (`product_id`, `order_id`, `change_type`, `quantity_change`, `stock_after`, `reason`, `created_at`) VALUES
(1, 1, 'order_deduct', -2, 50, 'Khách đặt đơn #ORD-20261024-001', '2026-10-24 10:15:00'),
(4, 1, 'order_deduct', -1, 40, 'Khách đặt đơn #ORD-20261024-001', '2026-10-24 10:15:00'),
(8, 2, 'order_deduct', -3, 60, 'Khách đặt đơn #ORD-20261025-002', '2026-10-25 08:30:00'),
(6, 3, 'order_deduct', -1, 15, 'Khách đặt đơn #ORD-20261025-003', '2026-10-25 09:45:00');
