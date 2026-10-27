<?php
/**
 * ProductController - Điều hướng và xử lý nghiệp vụ CRUD Sản phẩm
 * Tách biệt hoàn toàn tầng Controller, Model và View
 */

require_once __DIR__ . '/../models/Product.php';

session_start();

// Helper tạo slug tự động từ tên tiếng Việt
function createSlug(string $str): string
{
    $str = mb_strtolower($str, 'UTF-8');
    $str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/u', 'a', $str);
    $str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/u', 'e', $str);
    $str = preg_replace('/(ì|í|ị|ỉ|ĩ)/u', 'i', $str);
    $str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/u', 'o', $str);
    $str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/u', 'u', $str);
    $str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/u', 'y', $str);
    $str = preg_replace('/(đ)/u', 'd', $str);
    $str = preg_replace('/[^a-z0-9\s-]/u', '', $str);
    $str = preg_replace('/[\s-]+/', '-', $str);
    return trim($str, '-');
}

$action = $_GET['action'] ?? 'index';

switch ($action) {
    // -------------------------------------------------------------------------
    // 1. READ & FILTER & PAGINATION: Hiển thị danh sách, tìm kiếm, phân trang
    // -------------------------------------------------------------------------
    case 'index':
        $keyword = trim($_GET['keyword'] ?? '');
        $categoryId = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int)$_GET['category_id'] : null;
        $status = isset($_GET['is_published']) && $_GET['is_published'] !== '' ? (int)$_GET['is_published'] : null;
        
        $filters = [
            'keyword'      => $keyword,
            'category_id'  => $categoryId,
            'is_published' => $status
        ];

        // Phân trang an toàn (Slide 5.5)
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $page = max(1, $page);
        $perPage = 6;

        $totalRecords = countActiveProducts($filters);
        $totalPages = (int)ceil($totalRecords / $perPage);
        if ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
        }

        $products = findActiveProducts($filters, $page, $perPage);
        $categories = findAllCategories();

        $flashMessage = $_SESSION['flash_message'] ?? null;
        $flashType = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);

        require __DIR__ . '/../views/product_list.php';
        break;

    // -------------------------------------------------------------------------
    // 2. CREATE: Form thêm mới và xử lý POST tạo sản phẩm
    // -------------------------------------------------------------------------
    case 'create':
        $categories = findAllCategories();
        $errors = [];
        $data = [
            'name'           => '',
            'product_code'   => '',
            'category_id'    => '',
            'original_price' => '',
            'sale_price'     => '',
            'stock_quantity' => '',
            'description'    => '',
            'is_published'   => 1
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data['name']           = trim($_POST['name'] ?? '');
            $data['product_code']   = strtoupper(trim($_POST['product_code'] ?? ''));
            $data['category_id']    = (int)($_POST['category_id'] ?? 0);
            $data['original_price'] = trim($_POST['original_price'] ?? '');
            $data['sale_price']     = trim($_POST['sale_price'] ?? '');
            $data['stock_quantity'] = trim($_POST['stock_quantity'] ?? '');
            $data['description']    = trim($_POST['description'] ?? '');
            $data['is_published']   = isset($_POST['is_published']) ? 1 : 0;

            // Server-side Validation nghiêm ngặt
            if ($data['name'] === '') {
                $errors[] = 'Tên món ăn / sản phẩm không được để trống.';
            }
            if ($data['product_code'] === '') {
                $errors[] = 'Mã sản phẩm (SKU) không được để trống.';
            }
            if ($data['category_id'] <= 0) {
                $errors[] = 'Vui lòng chọn danh mục hợp lệ.';
            }
            if (!is_numeric($data['sale_price']) || (float)$data['sale_price'] < 0) {
                $errors[] = 'Giá bán phải là số hợp lệ và lớn hơn hoặc bằng 0.';
            }
            if (!is_numeric($data['original_price']) || (float)$data['original_price'] < 0) {
                $data['original_price'] = $data['sale_price'];
            }
            if (!filter_var($data['stock_quantity'], FILTER_VALIDATE_INT) || (int)$data['stock_quantity'] < 0) {
                $errors[] = 'Số lượng tồn kho phải là số nguyên không âm (>= 0).';
            }

            if (empty($errors)) {
                $data['slug'] = createSlug($data['name']) . '-' . strtolower($data['product_code']);
                $data['original_price'] = (float)$data['original_price'];
                $data['sale_price']     = (float)$data['sale_price'];
                $data['stock_quantity'] = (int)$data['stock_quantity'];
                $data['image_url']      = 'public/upload/products/sample.jpg';

                try {
                    $newId = createProduct($data);
                    $_SESSION['flash_message'] = "Thêm mới món '{$data['name']}' thành công (ID: $newId)!";
                    $_SESSION['flash_type'] = 'success';
                    header('Location: index.php');
                    exit();
                } catch (PDOException $e) {
                    if (str_contains($e->getMessage(), 'Duplicate entry') || $e->getCode() == 23000) {
                        $errors[] = "Mã sản phẩm '{$data['product_code']}' hoặc tên món đã tồn tại trong CSDL.";
                    } else {
                        $errors[] = "Lỗi CSDL: " . $e->getMessage();
                    }
                }
            }
        }

        require __DIR__ . '/../views/product_form.php';
        break;

    // -------------------------------------------------------------------------
    // 3. UPDATE: Form chỉnh sửa và xử lý POST cập nhật sản phẩm
    // -------------------------------------------------------------------------
    case 'edit':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $product = findProductById($id);

        if (!$product) {
            die("<div style='color:red;padding:30px;font-family:sans-serif;'>Lỗi 404: Không tìm thấy sản phẩm có ID #$id! <a href='index.php'>Quay lại</a></div>");
        }

        $categories = findAllCategories();
        $errors = [];
        $data = $product;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data['name']           = trim($_POST['name'] ?? '');
            $data['product_code']   = strtoupper(trim($_POST['product_code'] ?? ''));
            $data['category_id']    = (int)($_POST['category_id'] ?? 0);
            $data['original_price'] = trim($_POST['original_price'] ?? '');
            $data['sale_price']     = trim($_POST['sale_price'] ?? '');
            $data['stock_quantity'] = trim($_POST['stock_quantity'] ?? '');
            $data['description']    = trim($_POST['description'] ?? '');
            $data['is_published']   = isset($_POST['is_published']) ? 1 : 0;

            // Server-side Validation
            if ($data['name'] === '') {
                $errors[] = 'Tên món ăn / sản phẩm không được để trống.';
            }
            if ($data['product_code'] === '') {
                $errors[] = 'Mã sản phẩm (SKU) không được để trống.';
            }
            if ($data['category_id'] <= 0) {
                $errors[] = 'Vui lòng chọn danh mục hợp lệ.';
            }
            if (!is_numeric($data['sale_price']) || (float)$data['sale_price'] < 0) {
                $errors[] = 'Giá bán phải là số hợp lệ và lớn hơn hoặc bằng 0.';
            }
            if (!filter_var($data['stock_quantity'], FILTER_VALIDATE_INT) || (int)$data['stock_quantity'] < 0) {
                $errors[] = 'Số lượng tồn kho phải là số nguyên không âm (>= 0).';
            }

            if (empty($errors)) {
                $data['slug'] = createSlug($data['name']) . '-' . strtolower($data['product_code']);
                $data['original_price'] = (float)$data['original_price'];
                $data['sale_price']     = (float)$data['sale_price'];
                $data['stock_quantity'] = (int)$data['stock_quantity'];

                try {
                    updateProduct($id, $data);
                    $_SESSION['flash_message'] = "Cập nhật sản phẩm #$id ('{$data['name']}') thành công!";
                    $_SESSION['flash_type'] = 'success';
                    header('Location: index.php');
                    exit();
                } catch (PDOException $e) {
                    $errors[] = "Lỗi CSDL khi cập nhật: " . $e->getMessage();
                }
            }
        }

        require __DIR__ . '/../views/product_form.php';
        break;

    // -------------------------------------------------------------------------
    // 4. TOGGLE STATUS: Khóa / Mở bán sản phẩm BẮT BUỘC BẰNG POST (Slide 15)
    // -------------------------------------------------------------------------
    case 'toggle_status':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die("Lỗi 405 Method Not Allowed: Hành vi thay đổi trạng thái bắt buộc phải dùng phương thức POST theo chuẩn Slide 15!");
        }

        $id = (int)($_POST['id'] ?? 0);
        $newStatus = (int)($_POST['status'] ?? 0);

        if ($id > 0) {
            toggleProductStatus($id, $newStatus);
            $stText = $newStatus === 1 ? 'Mở bán' : 'Khóa bán';
            $_SESSION['flash_message'] = "Đã chuyển trạng thái sản phẩm #$id thành: $stText thành công!";
            $_SESSION['flash_type'] = 'info';
        }
        header('Location: index.php');
        exit();

    // -------------------------------------------------------------------------
    // 5. DELETE: Xóa sản phẩm BẮT BUỘC BẰNG POST (Slide 15)
    // -------------------------------------------------------------------------
    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die("Lỗi 405 Method Not Allowed: Hành vi xóa sản phẩm bắt buộc phải dùng phương thức POST theo chuẩn Slide 15!");
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                deleteProduct($id);
                $_SESSION['flash_message'] = "Đã xóa sản phẩm #$id thành công!";
                $_SESSION['flash_type'] = 'success';
            } catch (PDOException $e) {
                // Ràng buộc khóa ngoại ON DELETE RESTRICT bảo vệ đơn hàng cũ
                $_SESSION['flash_message'] = "Không thể xóa sản phẩm #$id vì sản phẩm này đã phát sinh đơn hàng trong quá khứ (Ràng buộc toàn vẹn Foreign Key InnoDB)!";
                $_SESSION['flash_type'] = 'danger';
            }
        }
        header('Location: index.php');
        exit();

    // -------------------------------------------------------------------------
    // 6. DEMO TRANSACTION: Đặt hàng trừ tồn kho không dở dang (Slide 14 & 5.6)
    // -------------------------------------------------------------------------
    case 'order_demo':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php');
            exit();
        }

        $productId = (int)($_POST['product_id'] ?? 0);
        $qty = (int)($_POST['quantity'] ?? 1);
        $customerId = 3; // Trần Văn Minh (User mẫu)

        $result = placeOrderWithTransaction($customerId, $productId, $qty);

        $_SESSION['flash_message'] = $result['message'];
        $_SESSION['flash_type'] = $result['success'] ? 'success' : 'danger';

        header('Location: index.php');
        exit();

    default:
        header('Location: index.php');
        exit();
}
