<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $action === 'create' ? 'Thêm Món Ăn Mới' : 'Cập Nhật Món Ăn' ?> - Bếp Nhà Hà Store</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; color: #1e293b; }
        .card { border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .form-label { font-weight: 600; font-size: 0.9rem; color: #334155; }
        .required::after { content: " *"; color: #dc2626; }
    </style>
</head>
<body>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Back Button -->
            <a href="index.php" class="btn btn-link text-decoration-none text-muted mb-3 p-0">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách sản phẩm
            </a>

            <!-- Form Card -->
            <div class="card">
                <div class="card-header bg-white py-3 border-bottom">
                    <h4 class="fw-bold mb-0 text-success">
                        <i class="fa-solid <?= $action === 'create' ? 'fa-circle-plus' : 'fa-pen-to-square' ?> me-2"></i>
                        <?= $action === 'create' ? 'Thêm Món Ăn Mới Vào Thực Đơn' : 'Cập Nhật Thông Tin Món Ăn #' . $data['id'] ?>
                    </h4>
                </div>
                <div class="card-body p-4">

                    <!-- Server-side Validation Errors -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger shadow-sm">
                            <h6 class="fw-bold mb-2"><i class="fa-solid fa-triangle-exclamation me-1"></i> Dữ liệu không hợp lệ, vui lòng kiểm tra lại:</h6>
                            <ul class="mb-0 ps-3">
                                <?php foreach ($errors as $err): ?>
                                    <li><?= htmlspecialchars($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="index.php?action=<?= $action ?><?= $action === 'edit' ? '&id=' . $data['id'] : '' ?>" method="POST">
                        <div class="row g-3">
                            <!-- Tên món ăn -->
                            <div class="col-md-8">
                                <label class="form-label required">Tên món ăn / Đồ uống:</label>
                                <input type="text" name="name" class="form-control" placeholder="VD: Trà Sữa Thái Xanh Trân Châu" value="<?= htmlspecialchars($data['name'] ?? '') ?>" required>
                            </div>

                            <!-- Mã SKU -->
                            <div class="col-md-4">
                                <label class="form-label required">Mã SKU sản phẩm:</label>
                                <input type="text" name="product_code" class="form-control text-uppercase" placeholder="VD: TS-THAI-01" value="<?= htmlspecialchars($data['product_code'] ?? '') ?>" required>
                            </div>

                            <!-- Danh mục -->
                            <div class="col-md-12">
                                <label class="form-label required">Thuộc danh mục:</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">-- Chọn danh mục món --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= ((int)($data['category_id'] ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Giá gốc -->
                            <div class="col-md-4">
                                <label class="form-label">Giá gốc (VNĐ):</label>
                                <input type="number" step="1000" min="0" name="original_price" class="form-control" placeholder="0" value="<?= htmlspecialchars((string)($data['original_price'] ?? '')) ?>">
                                <small class="text-muted">Giá niêm yết ban đầu</small>
                            </div>

                            <!-- Giá bán khuyến mãi -->
                            <div class="col-md-4">
                                <label class="form-label required">Giá bán thực tế (VNĐ):</label>
                                <input type="number" step="1000" min="0" name="sale_price" class="form-control fw-bold text-danger" placeholder="0" value="<?= htmlspecialchars((string)($data['sale_price'] ?? '')) ?>" required>
                                <small class="text-muted">Giá khách trả khi mua</small>
                            </div>

                            <!-- Số lượng tồn kho -->
                            <div class="col-md-4">
                                <label class="form-label required text-success">Số lượng tồn kho (Stock):</label>
                                <input type="number" min="0" name="stock_quantity" class="form-control fw-bold border-success text-success" placeholder="50" value="<?= htmlspecialchars((string)($data['stock_quantity'] ?? '')) ?>" required>
                                <small class="text-muted">Số lượng hàng khả dụng</small>
                            </div>

                            <!-- Mô tả món -->
                            <div class="col-md-12">
                                <label class="form-label">Mô tả món ăn:</label>
                                <textarea name="description" rows="3" class="form-control" placeholder="Thành phần nguyên liệu, hương vị đặc sắc..."><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
                            </div>

                            <!-- Trạng thái mở bán -->
                            <div class="col-md-12">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="switchPublish" name="is_published" value="1" <?= (int)($data['is_published'] ?? 1) === 1 ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-semibold" for="switchPublish">Kích hoạt mở bán món này ngay trên website</label>
                                </div>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="index.php" class="btn btn-light border px-4">Hủy bỏ</a>
                            <button type="submit" class="btn btn-success fw-semibold px-4 shadow-sm">
                                <i class="fa-solid fa-floppy-disk me-1"></i> <?= $action === 'create' ? 'Lưu Món Mới' : 'Cập Nhật Thay Đổi' ?>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
