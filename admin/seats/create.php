<?php
$pageTitle = 'Thêm ghế';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();
$error = '';

$rooms = $conn->query("
    SELECT r.room_id, r.room_name, l.name AS location_name
    FROM rooms r
    INNER JOIN locations l ON r.location_id = l.location_id
    ORDER BY l.name ASC, r.room_name ASC
");

$categories = $conn->query("
    SELECT category_id, name
    FROM seat_categories
    ORDER BY name ASC
");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roomId = (int) post('room_id');
    $categoryId = (int) post('category_id');
    $seatNumber = trim(post('seat_number'));
    $seatRow = trim(post('seat_row'));
    $isAisle = isset($_POST['is_aisle']) ? 1 : 0;
    $isAccessible = isset($_POST['is_accessible']) ? 1 : 0;

    if ($roomId <= 0 || $categoryId <= 0 || $seatNumber === '' || $seatRow === '') {
        $error = 'Vui lòng nhập đầy đủ dữ liệu bắt buộc.';
    } else {
        $sql = "
            INSERT INTO seats (
                room_id, category_id, seat_number, seat_row, is_aisle, is_accessible
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $error = 'Lỗi prepare: ' . $conn->error;
        } else {
            $stmt->bind_param(
                'iissii',
                $roomId,
                $categoryId,
                $seatNumber,
                $seatRow,
                $isAisle,
                $isAccessible
            );

            if ($stmt->execute()) {
                $stmt->close();
                set_flash_message('success', 'Thêm ghế thành công.');
                redirect('admin/seats/index.php');
            } else {
                $error = 'Lỗi thêm ghế: ' . $stmt->error;
            }

            $stmt->close();
        }
    }
}
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Form thêm ghế</h2>
        <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/seats/index.php">Quay lại</a>
    </div>

    <?php if ($error !== ''): ?>
        <div class="admin-flash"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if (!$rooms || $rooms->num_rows === 0): ?>
        <div class="admin-flash">Chưa có phòng. Hãy thêm phòng trước khi thêm ghế.</div>
    <?php endif; ?>

    <?php if (!$categories || $categories->num_rows === 0): ?>
        <div class="admin-flash">Chưa có loại ghế. Hãy thêm dữ liệu vào bảng seat_categories trước.</div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="admin-form-grid">
            <div class="admin-form-group">
                <label class="admin-label">Phòng</label>
                <select class="admin-select" name="room_id" required>
                    <option value="">-- Chọn phòng --</option>
                    <?php if ($rooms && $rooms->num_rows > 0): ?>
                        <?php while ($room = $rooms->fetch_assoc()): ?>
                            <option value="<?= (int) $room['room_id'] ?>">
                                <?= e($room['location_name'] . ' - ' . $room['room_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Loại ghế</label>
                <select class="admin-select" name="category_id" required>
                    <option value="">-- Chọn loại ghế --</option>
                    <?php if ($categories && $categories->num_rows > 0): ?>
                        <?php while ($category = $categories->fetch_assoc()): ?>
                            <option value="<?= (int) $category['category_id'] ?>">
                                <?= e($category['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Hàng ghế</label>
                <input class="admin-input" type="text" name="seat_row" placeholder="A, B, C..." required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Số ghế</label>
                <input class="admin-input" type="text" name="seat_number" placeholder="1, 2, 3..." required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">
                    <input type="checkbox" name="is_aisle"> Ghế gần lối đi
                </label>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">
                    <input type="checkbox" name="is_accessible"> Ghế hỗ trợ người khuyết tật
                </label>
            </div>
        </div>

        <button class="admin-btn" type="submit">Lưu ghế</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>