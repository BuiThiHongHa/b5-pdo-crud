<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bếp Nhà Hà Store - Quản Lý Món Ăn & Tồn Kho (PDO CRUD Bài 5)</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; color: #1e293b; }
        .navbar-brand { font-weight: 700; color: #059669 !important; font-size: 1.25rem; }
        .card { border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
        .table th { background-color: #f1f5f9; color: #475569; font-weight: 600; font-size: 0.88rem; text-transform: uppercase; }
        .badge-stock { font-size: 0.85rem; padding: 5px 9px; font-weight: 600; }
        .btn-action { padding: 4px 10px; font-size: 0.85rem; border-radius: 6px; }
        .pagination .page-link { color: #059669; border-radius: 6px; margin: 0 2px; }
        .pagination .page-item.active .page-link { background-color: #059669; border-color: #059669; color: #fff; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-white bg-white border-bottom shadow-sm py-3">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <i class="fa-solid fa-store me-2 text-success"></i> Bếp Nhà Hà Store
        </a>
        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
            <i class="fa-solid fa-code me-1"></i> Module Bài 5: PHP PDO & CRUD Chuẩn Hóa
        </span>
    </div>
</nav>

<div class="container my-4">
    <!-- Flash Notification -->
    <?php if (!empty($flashMessage)): ?>
        <div class="alert alert-<?= htmlspecialchars($flashType) ?> alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa-solid fa-circle-info me-2"></i> <?= htmlspecialchars($flashMessage) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Header Actions -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1">Quản Lý Danh Sách Món Ăn & Tồn Kho</h3>
            <p class="text-muted small mb-0">Thực thể chính: <code>products</code> liên kết <code>categories</code> | Sinh viên: <strong>Bùi Thị Hồng Hà</strong></p>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php?action=create" class="btn btn-success fw-semibold shadow-sm">
                <i class="fa-solid fa-plus me-1"></i> Thêm Món Ăn Mới
            </a>
            <button type="button" class="btn btn-outline-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#transactionDemoModal">
                <i class="fa-solid fa-bolt me-1"></i> Demo Transaction (Slide 14)
            </button>
        </div>
    </div>

    <!-- Search & Filter Card (Slide 5.4) -->
    <div class="card mb-4">
        <div class="card-body p-3">
            <form action="index.php" method="GET" class="row g-2 align-items-center">
                <input type="hidden" name="action" value="index">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="keyword" class="form-control" placeholder="Tìm theo tên món hoặc mã SKU..." value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="category_id" class="form-select">
                        <option value="">-- Tất cả danh mục --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (isset($filters['category_id']) && (int)$filters['category_id'] === (int)$cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="is_published" class="form-select">
                        <option value="">-- Trạng thái mở bán --</option>
                        <option value="1" <?= (isset($filters['is_published']) && (string)$filters['is_published'] === '1') ? 'selected' : '' ?>>Đang mở bán</option>
                        <option value="0" <?= (isset($filters['is_published']) && (string)$filters['is_published'] === '0') ? 'selected' : '' ?>>Đã khóa bán</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-filter me-1"></i> Lọc</button>
                    <a href="index.php" class="btn btn-light border"><i class="fa-solid fa-rotate-right"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table (CRUD: Read) -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="fa-solid fa-list me-2 text-primary"></i> Danh Sách Món Ăn (Tìm thấy <?= $totalRecords ?> món)</span>
            <span class="text-muted small">Trang <?= $page ?> / <?= max(1, $totalPages) ?></span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 60px;">ID</th>
                        <th>Mã SKU</th>
                        <th>Tên Món Ăn / Đồ Uống</th>
                        <th>Danh Mục</th>
                        <th class="text-end">Giá Bán</th>
                        <th class="text-center">Tồn Kho</th>
                        <th class="text-center">Trạng Thái</th>
                        <th class="text-center" style="width: 220px;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fa-regular fa-folder-open fa-3x mb-3 text-secondary"></i>
                                <p class="mb-0">Không tìm thấy sản phẩm nào phù hợp với bộ lọc!</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td class="text-center fw-bold text-muted"><?= $p['id'] ?></td>
                                <td><code class="text-primary fw-semibold"><?= htmlspecialchars($p['product_code']) ?></code></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($p['name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars(mb_substr($p['description'] ?? '', 0, 45, 'UTF-8')) ?>...</small>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['category_name'] ?? 'Chưa phân loại') ?></span></td>
                                <td class="text-end fw-bold text-danger"><?= number_format($p['sale_price'], 0, ',', '.') ?> đ</td>
                                <td class="text-center">
                                    <?php if ((int)$p['stock_quantity'] > 5): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle badge-stock"><?= $p['stock_quantity'] ?></span>
                                    <?php elseif ((int)$p['stock_quantity'] > 0): ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle badge-stock">Còn <?= $p['stock_quantity'] ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle badge-stock">Hết hàng (0)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ((int)$p['is_published'] === 1): ?>
                                        <span class="badge bg-success">Đang bán</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Khóa bán</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <!-- Edit Link -->
                                        <a href="index.php?action=edit&id=<?= $p['id'] ?>" class="btn btn-outline-warning btn-action" title="Sửa thông tin món">
                                            <i class="fa-solid fa-pen-to-square"></i> Sửa
                                        </a>

                                        <!-- Toggle Status: BẮT BUỘC BẰNG POST (Slide 15) -->
                                        <form action="index.php?action=toggle_status" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn <?= (int)$p['is_published'] === 1 ? 'Khóa bán' : 'Mở bán' ?> món này?');">
                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                            <input type="hidden" name="status" value="<?= (int)$p['is_published'] === 1 ? '0' : '1' ?>">
                                            <?php if ((int)$p['is_published'] === 1): ?>
                                                <button type="submit" class="btn btn-outline-secondary btn-action" title="Khóa bán món này"><i class="fa-solid fa-lock"></i> Khóa</button>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-outline-success btn-action" title="Mở bán món này"><i class="fa-solid fa-lock-open"></i> Mở</button>
                                            <?php endif; ?>
                                        </form>

                                        <!-- Delete: BẮT BUỘC BẰNG POST (Slide 15) -->
                                        <form action="index.php?action=delete" method="POST" class="d-inline" onsubmit="return confirm('CẢNH BÁO: Bạn có chắc chắn muốn xóa món này? Thao tác không thể hoàn tác!');">
                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-action" title="Xóa món ăn"><i class="fa-solid fa-trash"></i> Xóa</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Safe Pagination Controls (Slide 5.5) -->
        <?php if ($totalPages > 1): ?>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
                <div class="small text-muted">
                    Hiển thị <?= count($products) ?> / <?= $totalRecords ?> món
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <!-- Prev -->
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="index.php?action=index&page=<?= $page - 1 ?>&keyword=<?= urlencode($filters['keyword']) ?>&category_id=<?= $filters['category_id'] ?>&is_published=<?= $filters['is_published'] ?>">« Trước</a>
                        </li>
                        <!-- Pages -->
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="index.php?action=index&page=<?= $i ?>&keyword=<?= urlencode($filters['keyword']) ?>&category_id=<?= $filters['category_id'] ?>&is_published=<?= $filters['is_published'] ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <!-- Next -->
                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="index.php?action=index&page=<?= $page + 1 ?>&keyword=<?= urlencode($filters['keyword']) ?>&category_id=<?= $filters['category_id'] ?>&is_published=<?= $filters['is_published'] ?>">Sau »</a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Demo Transaction Đặt Hàng Trừ Kho (Slide 14) -->
<div class="modal fade" id="transactionDemoModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="modalLabel"><i class="fa-solid fa-bolt text-primary me-2"></i> Kịch Bản Demo Transaction (Slide 14)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?action=order_demo" method="POST">
                <div class="modal-body">
                    <p class="small text-muted mb-3">
                        Kịch bản: Khóa sản phẩm bằng <code>SELECT ... FOR UPDATE</code> ➔ Kiểm tra tồn kho ➔ Tạo đơn và chi tiết ➔ Cập nhật trừ tồn kho trong 1 giao dịch nguyên tố (ACID). Nếu không đủ tồn kho, hệ thống tự động <code>rollBack()</code>.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn món để đặt thử nghiệm:</label>
                        <select name="product_id" class="form-select" required>
                            <?php foreach ($products as $pr): ?>
                                <option value="<?= $pr['id'] ?>">
                                    #<?= $pr['id'] ?> - <?= htmlspecialchars($pr['name']) ?> (Tồn kho: <?= $pr['stock_quantity'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Số lượng muốn mua:</label>
                        <input type="number" name="quantity" class="form-control" value="2" min="1" required>
                        <small class="text-muted">Thử nhập số lượng lớn hơn số tồn để quan sát Transaction Rollback!</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary fw-semibold"><i class="fa-solid fa-cart-shopping me-1"></i> Chạy Thử Giao Dịch</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
