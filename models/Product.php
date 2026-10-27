<?php
/**
 * ProductRepository (Model) - Thao tác CSDL bảng `products` hoàn toàn bằng PDO Prepared Statement
 * Tuân thủ quy tắc Slide Bài 5: Không có câu truy vấn SQL nào trong file View giao diện.
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Tìm kiếm và phân trang sản phẩm theo bộ lọc (Theo slide 5.4 và 5.5)
 */
function findActiveProducts(array $filters = [], int $page = 1, int $perPage = 6): array
{
    $pdo = getPDO();
    $page = max(1, $page);
    $offset = ($page - 1) * $perPage;

    $where = ['1=1'];
    $params = [];

    if (!empty($filters['keyword'])) {
        $where[] = '(p.name LIKE :kw OR p.product_code LIKE :kw)';
        $params['kw'] = '%' . trim($filters['keyword']) . '%';
    }

    if (!empty($filters['category_id'])) {
        $where[] = 'p.category_id = :cat_id';
        $params['cat_id'] = (int)$filters['category_id'];
    }

    if (isset($filters['is_published']) && $filters['is_published'] !== '') {
        $where[] = 'p.is_published = :pub';
        $params['pub'] = (int)$filters['is_published'];
    }

    $whereSql = implode(' AND ', $where);

    // SQL phân trang an toàn
    $sql = "SELECT p.*, c.name AS category_name 
            FROM `products` p
            LEFT JOIN `categories` c ON p.category_id = c.id
            WHERE $whereSql
            ORDER BY p.id DESC
            LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue(':' . $k, $v);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Đếm tổng số bản ghi sản phẩm để tính số trang (Slide 5.5)
 */
function countActiveProducts(array $filters = []): int
{
    $pdo = getPDO();
    $where = ['1=1'];
    $params = [];

    if (!empty($filters['keyword'])) {
        $where[] = '(p.name LIKE :kw OR p.product_code LIKE :kw)';
        $params['kw'] = '%' . trim($filters['keyword']) . '%';
    }

    if (!empty($filters['category_id'])) {
        $where[] = 'p.category_id = :cat_id';
        $params['cat_id'] = (int)$filters['category_id'];
    }

    if (isset($filters['is_published']) && $filters['is_published'] !== '') {
        $where[] = 'p.is_published = :pub';
        $params['pub'] = (int)$filters['is_published'];
    }

    $whereSql = implode(' AND ', $where);
    $sql = "SELECT COUNT(*) FROM `products` p WHERE $whereSql";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return (int)$stmt->fetchColumn();
}

/**
 * Tìm sản phẩm theo ID (Slide 5.4)
 */
function findProductById(int $id): ?array
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT p.*, c.name AS category_name FROM `products` p LEFT JOIN `categories` c ON p.category_id = c.id WHERE p.id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $prod = $stmt->fetch();
    return $prod ?: null;
}

/**
 * Lấy danh sách toàn bộ danh mục để đưa vào dropdown select
 */
function findAllCategories(): array
{
    $pdo = getPDO();
    $stmt = $pdo->query('SELECT id, name FROM `categories` WHERE is_active = 1 ORDER BY display_order ASC, name ASC');
    return $stmt->fetchAll();
}

/**
 * Tạo mới sản phẩm (Create - C trong CRUD)
 */
function createProduct(array $data): int
{
    $pdo = getPDO();
    $sql = "INSERT INTO `products` (category_id, product_code, name, slug, description, original_price, sale_price, stock_quantity, image_url, is_published)
            VALUES (:category_id, :product_code, :name, :slug, :description, :original_price, :sale_price, :stock_quantity, :image_url, :is_published)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'category_id'    => $data['category_id'],
        'product_code'   => $data['product_code'],
        'name'           => $data['name'],
        'slug'           => $data['slug'],
        'description'    => $data['description'] ?? null,
        'original_price' => $data['original_price'],
        'sale_price'     => $data['sale_price'],
        'stock_quantity' => $data['stock_quantity'],
        'image_url'      => $data['image_url'] ?? null,
        'is_published'   => $data['is_published'] ?? 1,
    ]);

    return (int)$pdo->lastInsertId();
}

/**
 * Cập nhật sản phẩm (Update - U trong CRUD)
 */
function updateProduct(int $id, array $data): bool
{
    $pdo = getPDO();
    $sql = "UPDATE `products` SET 
                category_id    = :category_id,
                product_code   = :product_code,
                name           = :name,
                slug           = :slug,
                description    = :description,
                original_price = :original_price,
                sale_price     = :sale_price,
                stock_quantity = :stock_quantity,
                image_url      = :image_url,
                is_published   = :is_published
            WHERE id = :id";
            
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        'id'             => $id,
        'category_id'    => $data['category_id'],
        'product_code'   => $data['product_code'],
        'name'           => $data['name'],
        'slug'           => $data['slug'],
        'description'    => $data['description'] ?? null,
        'original_price' => $data['original_price'],
        'sale_price'     => $data['sale_price'],
        'stock_quantity' => $data['stock_quantity'],
        'image_url'      => $data['image_url'] ?? null,
        'is_published'   => $data['is_published'] ?? 1,
    ]);
}

/**
 * Khóa hoặc Mở bán sản phẩm bằng phương thức POST (Theo tiêu chí Slide 15)
 */
function toggleProductStatus(int $id, int $newStatus): bool
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('UPDATE `products` SET is_published = :status WHERE id = :id');
    return $stmt->execute(['status' => $newStatus, 'id' => $id]);
}

/**
 * Xóa sản phẩm bằng phương thức POST (Theo tiêu chí Slide 15)
 */
function deleteProduct(int $id): bool
{
    $pdo = getPDO();
    // Bảng order_items có ràng buộc FK ON DELETE RESTRICT nên nếu sản phẩm đã phát sinh đơn thì không xóa để bảo toàn dữ liệu
    $stmt = $pdo->prepare('DELETE FROM `products` WHERE id = :id');
    return $stmt->execute(['id' => $id]);
}

/**
 * Đặt hàng an toàn sử dụng PDO Transaction & SELECT FOR UPDATE (Slide 5.6 & Slide 14)
 */
function placeOrderWithTransaction(int $customerId, int $productId, int $qty): array
{
    $pdo = getPDO();
    $pdo->beginTransaction();
    try {
        // 1. Khóa sản phẩm cần mua và kiểm tra tồn kho
        $stmt = $pdo->prepare('SELECT id, name, sale_price, stock_quantity, is_published FROM `products` WHERE id = :id FOR UPDATE');
        $stmt->execute(['id' => $productId]);
        $product = $stmt->fetch();

        if (!$product || (int)$product['is_published'] !== 1) {
            throw new RuntimeException('Sản phẩm hiện đang tạm khóa hoặc ngừng bán.');
        }

        if ((int)$product['stock_quantity'] < $qty) {
            throw new RuntimeException("Số lượng đặt ($qty) vượt quá số lượng tồn kho hiện có (" . $product['stock_quantity'] . ").");
        }

        $unitPrice = (float)$product['sale_price'];
        $totalAmount = $unitPrice * $qty;
        $orderCode = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        // 2. Tạo đơn hàng (bảng orders)
        $stmtOrder = $pdo->prepare("INSERT INTO `orders` (order_code, user_id, customer_name, customer_phone, shipping_address, total_amount, order_status)
                                    VALUES (:order_code, :user_id, :customer_name, :customer_phone, :shipping_address, :total_amount, 'pending')");
        $stmtOrder->execute([
            'order_code'       => $orderCode,
            'user_id'          => $customerId,
            'customer_name'    => 'Khách đặt trực tiếp (PDO Demo)',
            'customer_phone'   => '0901234567',
            'shipping_address' => 'Số 98 Dương Quảng Hàm, Cầu Giấy, Hà Nội',
            'total_amount'     => $totalAmount,
        ]);
        $orderId = (int)$pdo->lastInsertId();

        // 3. Tạo chi tiết đơn hàng (bảng order_items)
        $stmtItem = $pdo->prepare("INSERT INTO `order_items` (order_id, product_id, product_name, unit_price, quantity, subtotal)
                                   VALUES (:order_id, :product_id, :product_name, :unit_price, :quantity, :subtotal)");
        $stmtItem->execute([
            'order_id'     => $orderId,
            'product_id'   => $productId,
            'product_name' => $product['name'],
            'unit_price'   => $unitPrice,
            'quantity'     => $qty,
            'subtotal'     => $totalAmount,
        ]);

        // 4. Trừ số lượng tồn kho an toàn
        $stmtUpdateStock = $pdo->prepare('UPDATE `products` SET stock_quantity = stock_quantity - :qty WHERE id = :id');
        $stmtUpdateStock->execute(['qty' => $qty, 'id' => $productId]);

        // 5. Ghi nhật ký biến động kho (inventory_logs)
        $stmtLog = $pdo->prepare("INSERT INTO `inventory_logs` (product_id, order_id, change_type, quantity_change, stock_after, reason)
                                  VALUES (:pid, :oid, 'order_deduct', :qty_change, :stock_after, :reason)");
        $stmtLog->execute([
            'pid'          => $productId,
            'oid'          => $orderId,
            'qty_change'   => -$qty,
            'stock_after'  => (int)$product['stock_quantity'] - $qty,
            'reason'       => "Xuất kho tự động cho đơn $orderCode",
        ]);

        // Thành công: xác nhận thay đổi dữ liệu
        $pdo->commit();

        return [
            'success'      => true,
            'order_id'     => $orderId,
            'order_code'   => $orderCode,
            'total_amount' => $totalAmount,
            'new_stock'    => (int)$product['stock_quantity'] - $qty,
            'message'      => "Đặt hàng thành công! Mã đơn: $orderCode. Đã trừ $qty sản phẩm trong kho."
        ];
    } catch (Throwable $e) {
        $pdo->rollBack();
        return [
            'success' => false,
            'message' => 'Lỗi giao dịch đặt hàng: ' . $e->getMessage()
        ];
    }
}
