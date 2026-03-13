<?php
$pageTitle = 'Thêm phòng';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();
$error = '';

$locations = $conn->query("SELECT location_id, name FROM locations ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $locationId = (int) post('location_id');
    $roomName = trim(post('room_name'));
    $capacity = (int) post('capacity');
    $status = trim(post('status'));

    if ($locationId <= 0 || $roomName === '' || $capacity <= 0) {
        $error = 'Vui lòng nhập đầy đủ dữ liệu bắt buộc.';
    } else {
        $sql = "INSERT INTO rooms (location_id, room_name, capacity, status) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('isis', $locationId, $roomName, $capacity, $status);

        if ($stmt->execute()) {
            $stmt->close();
            set_flash_message('success', 'Thêm phòng thành công.');
            redirect('admin/rooms/index.php');
        } else {
            $error = 'Không thể thêm phòng. Có thể tên phòng đã tồn tại trong cùng rạp.';
        }

        $stmt->close();
    }
}
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Form thêm phòng</h2>
        <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/rooms/index.php">Quay lại</a>
    </div>

    <?php if ($error !== ''): ?>
        <div class="admin-flash"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="admin-form-grid">
            <div class="admin-form-group">
                <label class="admin-label">Rạp / Chi nhánh</label>
                <select class="admin-select" name="location_id" required>
                    <option value="">-- Chọn rạp --</option>
                    <?php if ($locations && $locations->num_rows > 0): ?>
                        <?php while ($location = $locations->fetch_assoc()): ?>
                            <option value="<?= (int) $location['location_id'] ?>"><?= e($location['name']) ?></option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Tên phòng</label>
                <input class="admin-input" type="text" name="room_name" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Sức chứa</label>
                <input class="admin-input" type="number" name="capacity" min="1" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Trạng thái</label>
                <select class="admin-select" name="status">
                    <option value="active">active</option>
                    <option value="inactive">inactive</option>
                </select>
            </div>
        </div>

        <button class="admin-btn" type="submit">Lưu phòng</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>