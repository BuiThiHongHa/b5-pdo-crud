# BÀI TẬP VỀ NHÀ 5: PHP–MYSQL BẰNG PDO VÀ XÂY DỰNG CRUD
> **Học phần:** Lập trình Web  
> **Chủ đề bài tập lớn:** CHỦ ĐỀ 7 — HỆ THỐNG BÁN HÀNG TRỰC TUYẾN MINI (Bếp Nhà Hà Store)  
> **Sinh viên thực hiện:** Bùi Thị Hồng Hà (Làm việc cá nhân độc lập 100%)  
> **Repository bài nộp:** [https://github.com/BuiThiHongHa/b5-pdo-crud](https://github.com/BuiThiHongHa/b5-pdo-crud)

---

## 📋 MỤC TIÊU VÀ ĐÁP ỨNG TIÊU CHÍ SLIDE BÀI 5

Căn cứ theo yêu cầu tại **Slide 15 (Thực hành CRUD thực thể chính)** và **Slide 16 (Bài tập về nhà 5)**:
- [x] **Công nghệ PDO chuẩn mực:** 100% kết nối và truy vấn CSDL sử dụng đối tượng `PDO`, cấu hình `PDO::ERRMODE_EXCEPTION` và `PDO::FETCH_ASSOC`.
- [x] **Cấu trúc MVC tách biệt:** Tuyệt đối **không có câu truy vấn SQL nào trong file giao diện View**.
- [x] **Prepared Statement toàn diện:** Toàn bộ truy vấn có đầu vào từ người dùng đều sử dụng `prepare()` và `execute()` hoặc `bindValue()`.
- [x] **Hành vi thay đổi dữ liệu bằng POST:** Thao tác **Xóa (`delete`)** và **Khóa / Mở bán (`toggle_status`)** bắt buộc thực thi qua phương thức `POST` (chặn 405 nếu dùng GET).
- [x] **Tìm kiếm & Phân trang an toàn (Slide 5.4 & 5.5):** Tìm kiếm theo từ khóa/danh mục; Phân trang bằng `LIMIT` và `OFFSET`, kiểm tra ép kiểu tham số `page` an toàn.
- [x] **Demo Transaction đặt hàng không vượt tồn kho (Slide 14 & 5.6):** Sử dụng `beginTransaction()`, khóa dòng `SELECT ... FOR UPDATE`, tự động trừ kho và `rollBack()` khi có lỗi.
- [x] **Ghi 5 Test case kiểm thử dữ liệu đúng / sai:** Có bảng kịch bản và kết quả chi tiết.

---

## I. KIẾN TRÚC THƯ MỤC DỰ ÁN

```text
b5-pdo-crud/
├── config/
│   └── database.php                # Cấu hình kết nối PDO chuẩn mực, báo lỗi Exception
├── models/
│   └── Product.php                 # Model (Repository) chứa các hàm PDO: findActiveProducts,
│                                   # countActiveProducts, findProductById, createProduct,
│                                   # updateProduct, deleteProduct, placeOrderWithTransaction
├── controllers/
│   └── ProductController.php       # Controller điều hướng xử lý các action: index, create, edit,
│                                   # toggle_status (POST), delete (POST), order_demo (POST)
├── views/
│   ├── product_list.php            # Giao diện danh sách món, thanh lọc, tìm kiếm và phân trang
│   └── product_form.php            # Form Thêm mới / Cập nhật món ăn có kiểm tra lỗi server
├── database/
│   ├── schema.sql                  # Cấu trúc CSDL 8 bảng chuẩn 3NF, Khóa ngoại InnoDB, UNIQUE, CHECK
│   ├── seed.sql                    # Dữ liệu mẫu tiếng Việt phong phú có sẵn món tồn kho 2 để demo
│   └── queries.sql                 # 4 câu truy vấn SELECT có JOIN & WHERE kiểm thử tiếng Việt
├── assets/
│   └── erd.png                     # Sơ đồ thực thể liên kết (ERD) hoàn chỉnh của hệ thống
├── index.php                       # Điểm đón Front Controller điều hướng
└── README.md                       # Tài liệu thuyết minh, danh sách route và bộ test cases
```

---

## II. DANH SÁCH ROUTE & CHỨC NĂNG (THEO YÊU CẦU SLIDE 16)

| STT | Phương thức (Method) | Route / URL | Chức năng nghiệp vụ | File thực thi tương ứng |
| :---: | :---: | :--- | :--- | :--- |
| **1** | `GET` | `index.php` hoặc `index.php?action=index` | Xem danh sách món ăn, lọc danh mục, tìm kiếm và phân trang | `controllers/ProductController.php` ➔ `views/product_list.php` |
| **2** | `GET` | `index.php?action=create` | Hiển thị form thêm mới món ăn / đồ uống | `views/product_form.php` |
| **3** | `POST` | `index.php?action=create` | Xác thực dữ liệu và tạo mới sản phẩm vào CSDL qua PDO | `models/Product.php` (`createProduct()`) |
| **4** | `GET` | `index.php?action=edit&id={id}` | Hiển thị form chỉnh sửa thông tin món ăn theo ID | `views/product_form.php` |
| **5** | `POST` | `index.php?action=edit&id={id}` | Xác thực dữ liệu và cập nhật sản phẩm vào CSDL qua PDO | `models/Product.php` (`updateProduct()`) |
| **6** | `POST` | `index.php?action=toggle_status` | **Khóa bán hoặc Mở bán món ăn** (Chặn 405 nếu dùng GET) | `models/Product.php` (`toggleProductStatus()`) |
| **7** | `POST` | `index.php?action=delete` | **Xóa món ăn khỏi CSDL** (Chặn 405 nếu dùng GET) | `models/Product.php` (`deleteProduct()`) |
| **8** | `POST` | `index.php?action=order_demo` | **Demo Transaction**: Đặt hàng trừ tồn kho không dở dang (Slide 14) | `models/Product.php` (`placeOrderWithTransaction()`) |

---

## III. BẢNG 5 TEST CASE NHẬP DỮ LIỆU ĐÚNG / SAI (THEO YÊU CẦU SLIDE 16)

| Test Case ID | Tình huống kiểm thử | Dữ liệu đầu vào (Input) | Kết quả mong đợi (Expected) | Kết quả thực tế | Trạng thái |
| :---: | :--- | :--- | :--- | :--- | :---: |
| **TC-01** | **Thêm sản phẩm hợp lệ (Dữ liệu ĐÚNG)** | - Tên món: `Trà Đào Cam Sả Tươi` <br>- Mã SKU: `TC-TRADAO-01` <br>- Danh mục: Trà Trái Cây <br>- Giá bán: `25.000` <br>- Tồn kho: `30` | Server validate thành công; Thực thi `INSERT` qua PDO Prepared Statement; Chuyển về danh sách kèm thông báo xanh *"Thêm mới món thành công!"*. | Món mới được lưu vào CSDL `mini_store_db`, xuất hiện ngay trên bảng danh sách. | **PASS** |
| **TC-02** | **Thêm sản phẩm bỏ trống tên (Dữ liệu SAI)** | - Tên món: `""` (Rỗng) <br>- Mã SKU: `TEST-01` <br>- Giá bán: `20.000` <br>- Tồn kho: `10` | Server chặn xử lý; Giữ nguyên form và hiển thị thông báo lỗi màu đỏ: *"Tên món ăn / sản phẩm không được để trống."*. | Form giữ lại dữ liệu cũ, hiển thị cảnh báo vi phạm phía trên form. | **PASS** |
| **TC-03** | **Nhập số lượng tồn kho âm (Dữ liệu SAI)** | - Tên món: `Bánh Tráng Trộn Sốt Me` <br>- Mã SKU: `AV-BANHTRANG-01` <br>- Giá bán: `15.000` <br>- Tồn kho: `-5` | Server chặn xử lý; Báo lỗi: *"Số lượng tồn kho phải là số nguyên không âm (>= 0)."*; Không sinh bản ghi CSDL. | Bị chặn cả ở Client (HTML5 `min="0"`) và Server-side validation PHP. | **PASS** |
| **TC-04** | **Trùng lặp mã sản phẩm SKU (Dữ liệu SAI)** | - Tên món: `Trà Sữa Thái Xanh Mới` <br>- Mã SKU: `TS-THAI-01` (Đã tồn tại) <br>- Giá bán: `22.000` <br>- Tồn kho: `20` | Bắt ngoại lệ PDOException vi phạm ràng buộc `UNIQUE`; Báo lỗi thân thiện: *"Mã sản phẩm 'TS-THAI-01' đã tồn tại trong CSDL."*. | Hệ thống không bị crash lỗi 500, hiển thị thông báo trùng lặp mã rõ ràng. | **PASS** |
| **TC-05** | **Xóa hoặc Khóa món bằng GET thay vì POST (Bảo mật SAI)** | Truy cập trực tiếp link từ trình duyệt: <br>`GET index.php?action=delete&id=1` | Server từ chối thực thi; Xuất thông báo lỗi HTTP 405 Method Not Allowed; Không xóa dữ liệu. | Chặn đứng hành vi CSRF hoặc vô tình click link xóa; Bắt buộc submit form POST. | **PASS** |

---

## IV. ĐẶC TẢ KỸ THUẬT NỔI BẬT

### 1. Phân trang an toàn & Tìm kiếm (Slide 5.4 & 5.5)
Áp dụng công thức tính toán an toàn:
```php
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 6;
$offset = ($page - 1) * $perPage;

// Ép kiểu INT tường minh cho LIMIT và OFFSET trong PDO
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
```

### 2. Demo Transaction: Tạo đơn hàng không vượt tồn kho (Slide 14 & 5.6)
Áp dụng cơ chế khóa bi quan (`SELECT ... FOR UPDATE`) và kiểm soát giao dịch nguyên tử (ACID):
```php
$pdo->beginTransaction();
try {
    // 1. Khóa sản phẩm cần mua và kiểm tra tồn kho
    $stmt = $pdo->prepare('SELECT id, name, sale_price, stock_quantity, is_published FROM products WHERE id = :id FOR UPDATE');
    $stmt->execute(['id' => $productId]);
    $product = $stmt->fetch();

    if (!$product || (int)$product['is_published'] !== 1) {
        throw new RuntimeException('Sản phẩm hiện đang tạm khóa hoặc ngừng bán.');
    }
    if ((int)$product['stock_quantity'] < $qty) {
        throw new RuntimeException("Số lượng đặt ($qty) vượt quá số tồn kho hiện có (" . $product['stock_quantity'] . ").");
    }

    // 2. Tạo đơn và chi tiết đơn
    // 3. Trừ số lượng tồn kho
    $stmtUpdate = $pdo->prepare('UPDATE products SET stock_quantity = stock_quantity - :qty WHERE id = :id');
    $stmtUpdate->execute(['qty' => $qty, 'id' => $productId]);

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack(); // Lỗi dở dang sẽ được hoàn tác hoàn toàn
}
```

---

## V. HƯỚNG DẪN CÀI ĐẶT VÀ CHẠY THỬ NGHIỆM

1. **Khởi động dịch vụ XAMPP:**
   ```bash
   sudo /opt/lampp/lampp start
   ```
2. **Import Cơ sở dữ liệu:**
   Mở terminal hoặc phpMyAdmin chạy các file script:
   ```bash
   mysql -u root -p < database/schema.sql
   mysql -u root -p < database/seed.sql
   ```
3. **Mở trình duyệt truy cập:**
   - Ứng dụng chạy trên cổng web local (Apache XAMPP hoặc PHP Built-in server):
   ```bash
   php -S 127.0.0.1:8000
   ```
   👉 Truy cập: `http://localhost:8000/index.php`
