-- =============================================================================
-- BÀI TẬP LỚN LẬP TRÌNH WEB - CHỦ ĐỀ 7: HỆ THỐNG BÁN HÀNG TRỰC TUYẾN MINI
-- Sinh viên thực hiện: Bùi Thị Hồng Hà
-- Mã nguồn: https://github.com/BuiThiHongHa/online-shop-mini
-- File: database/queries.sql
-- Danh sách truy vấn SELECT có JOIN & WHERE, chạy thử với dữ liệu tiếng Việt
-- =============================================================================

USE `mini_store_db`;

-- -----------------------------------------------------------------------------
-- TRUY VẤN 1: Lấy danh sách sản phẩm theo danh mục và lọc theo khoảng giá
-- Ý nghĩa nghiệp vụ: Khách hàng tìm món ăn vặt hoặc trà sữa trong khoảng 15.000đ - 30.000đ
-- Kỹ thuật: INNER JOIN giữa products và categories, WHERE khoảng giá và trạng thái còn hàng
-- -----------------------------------------------------------------------------
SELECT 
    p.product_code AS 'Mã SKU',
    p.name AS 'Tên Món Ăn / Thức Uống',
    c.name AS 'Danh Mục',
    p.sale_price AS 'Giá Bán (VNĐ)',
    p.stock_quantity AS 'Số Lượng Tồn Kho',
    CASE 
        WHEN p.stock_quantity > 0 THEN 'Còn Hàng'
        ELSE 'Hết Hàng'
    END AS 'Tình Trạng Kho'
FROM `products` p
INNER JOIN `categories` c ON p.category_id = c.id
WHERE c.is_active = 1 
  AND p.is_published = 1
  AND p.sale_price BETWEEN 15000.00 AND 30000.00
ORDER BY p.sale_price ASC;

-- -----------------------------------------------------------------------------
-- TRUY VẤN 2: Lấy chi tiết lịch sử mua hàng của khách hàng 'Trần Văn Minh'
-- Ý nghĩa nghiệp vụ: Khách hàng hoặc CSKH xem chi tiết các món trong đơn hàng đã đặt
-- Kỹ thuật: INNER JOIN 4 bảng: users, orders, order_items, products
-- -----------------------------------------------------------------------------
SELECT 
    o.order_code AS 'Mã Đơn Hàng',
    u.full_name AS 'Khách Hàng',
    u.phone AS 'SĐT Liên Hệ',
    oi.product_name AS 'Món Đã Đặt',
    oi.quantity AS 'Số Lượng',
    oi.unit_price AS 'Đơn Giá (VNĐ)',
    oi.subtotal AS 'Thành Tiền',
    o.total_amount AS 'Tổng Tiền Đơn',
    o.order_status AS 'Trạng Thái Đơn',
    o.created_at AS 'Thời Gian Đặt'
FROM `orders` o
INNER JOIN `users` u ON o.user_id = u.id
INNER JOIN `order_items` oi ON o.id = oi.order_id
INNER JOIN `products` p ON oi.product_id = p.id
WHERE u.email = 'minh.nguyen@example.com'
ORDER BY o.created_at DESC;

-- -----------------------------------------------------------------------------
-- TRUY VẤN 3: Báo cáo thống kê sản lượng bán ra và tổng doanh thu theo danh mục
-- Ý nghĩa nghiệp vụ: Chủ quán xem báo cáo danh mục nào bán chạy nhất, mang lại doanh thu cao nhất
-- Kỹ thuật: LEFT JOIN categories -> products -> order_items -> orders; GROUP BY; SUM; COUNT
-- -----------------------------------------------------------------------------
SELECT 
    c.name AS 'Tên Danh Mục',
    COUNT(DISTINCT p.id) AS 'Tổng Số Món',
    IFNULL(SUM(oi.quantity), 0) AS 'Tổng Số Lượng Đã Bán',
    IFNULL(SUM(oi.subtotal), 0) AS 'Doanh Thu (VNĐ)'
FROM `categories` c
LEFT JOIN `products` p ON c.id = p.category_id
LEFT JOIN `order_items` oi ON p.id = oi.product_id
LEFT JOIN `orders` o ON oi.order_id = o.id AND o.order_status != 'cancelled'
GROUP BY c.id, c.name
ORDER BY IFNULL(SUM(oi.subtotal), 0) DESC;

-- -----------------------------------------------------------------------------
-- TRUY VẤN 4: Cảnh báo các mặt hàng sắp hết hàng (tồn kho <= 5) phục vụ quản lý kho
-- Ý nghĩa nghiệp vụ: Giúp nhân viên nhận biết món nào sắp hết để chuẩn bị nguyên liệu kịp thời
-- -----------------------------------------------------------------------------
SELECT 
    p.product_code AS 'Mã SKU',
    p.name AS 'Tên Món Ăn',
    c.name AS 'Danh Mục',
    p.stock_quantity AS 'Tồn Hiện Tại',
    'CẢNH BÁO SẮP HẾT HÀNG' AS 'Trạng Thái'
FROM `products` p
INNER JOIN `categories` c ON p.category_id = c.id
WHERE p.stock_quantity <= 5 AND p.is_published = 1
ORDER BY p.stock_quantity ASC;
