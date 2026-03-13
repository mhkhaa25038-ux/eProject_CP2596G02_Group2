<?php
$pageTitle = 'Sửa phòng';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();
$id = (int) get('id');
$error = '';

if ($id <= 0) {
    set_flash_message('error', 'Phòng không hợp lệ.');
    redirect('admin/rooms/index.php');
}

$stmt = $conn->prepare("SELECT * FROM rooms WHERE room_id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$room = $result->fetch_assoc();
$stmt->close();

if (!$room) {
    set_flash_message('error', 'Không tìm thấy phòng.');
    redirect('admin/rooms/index.php');
}

$locations = $conn->query("SELECT location_id, name FROM locations ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $locationId = (int) post('location_id');
    $roomName = trim(post('room_name'));
    $capacity = (int) post('capacity');
    $status = trim(post('status'));

    if ($locationId <= 0 || $roomName === '' || $capacity <= 0) {
        $error = 'Vui lòng nhập đầy đủ dữ liệu bắt buộc.';
    } else {
        $sql = "UPDATE rooms SET location_id = ?, room_name = ?, capacity = ?, status = ? WHERE room_id = ?";
        $updateStmt = $conn->prepare($sql);
        $updateStmt->bind_param('isisi', $locationId, $roomName, $capacity, $status, $id);

        if ($updateStmt->execute()) {
            $updateStmt->close();
            set_flash_message('success', 'Cập nhật phòng thành công.');
            redirect('admin/rooms/index.php');
        } else {
            $error = 'Không thể cập nhật phòng.';
        }

        $updateStmt->close();
    }
}
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Form sửa phòng</h2>
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
                    <?php if ($locations && $locations->num_rows > 0): ?>
                        <?php while ($location = $locations->fetch_assoc()): ?>
                            <option value="<?= (int) $location['location_id'] ?>" <?= (int)$room['location_id'] === (int)$location['location_id'] ? 'selected' : '' ?>>
                                <?= e($location['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Tên phòng</label>
                <input class="admin-input" type="text" name="room_name" value="<?= e($room['room_name']) ?>" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Sức chứa</label>
                <input class="admin-input" type="number" name="capacity" min="1" value="<?= (int) $room['capacity'] ?>" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Trạng thái</label>
                <select class="admin-select" name="status">
                    <option value="active" <?= $room['status'] === 'active' ? 'selected' : '' ?>>active</option>
                    <option value="inactive" <?= $room['status'] === 'inactive' ? 'selected' : '' ?>>inactive</option>
                </select>
            </div>
        </div>

        <button class="admin-btn" type="submit">Cập nhật phòng</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>