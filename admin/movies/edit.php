<?php
$pageTitle = 'Sửa phim';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();
$id = (int) get('id');
$error = '';

if ($id <= 0) {
    set_flash_message('error', 'Phim không hợp lệ.');
    redirect('admin/movies/index.php');
}

$stmt = $conn->prepare("SELECT * FROM movies WHERE movie_id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$movie = $result->fetch_assoc();
$stmt->close();

if (!$movie) {
    set_flash_message('error', 'Không tìm thấy phim.');
    redirect('admin/movies/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim(post('title'));
    $synopsis = trim(post('synopsis'));
    $language = trim(post('language'));
    $durationMin = (int) post('duration_min');
    $trailerUrl = trim(post('trailer_url'));
    $posterUrl = trim(post('poster_url'));
    $status = trim(post('status'));
    $ageRating = trim(post('age_rating'));
    $releaseDate = trim(post('release_date'));

    if ($title === '' || $language === '' || $durationMin <= 0) {
        $error = 'Vui lòng nhập đầy đủ dữ liệu bắt buộc.';
    } else {
        $sql = "
            UPDATE movies
            SET title = ?, synopsis = ?, language = ?, duration_min = ?,
                trailer_url = ?, poster_url = ?, status = ?, age_rating = ?, release_date = ?
            WHERE movie_id = ?
        ";
        $updateStmt = $conn->prepare($sql);
        $updateStmt->bind_param(
            'sssisssssi',
            $title,
            $synopsis,
            $language,
            $durationMin,
            $trailerUrl,
            $posterUrl,
            $status,
            $ageRating,
            $releaseDate,
            $id
        );

        if ($updateStmt->execute()) {
            $updateStmt->close();
            set_flash_message('success', 'Cập nhật phim thành công.');
            redirect('admin/movies/index.php');
        } else {
            $error = 'Không thể cập nhật phim.';
        }

        $updateStmt->close();
    }
}
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Form sửa phim</h2>
        <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/movies/index.php">Quay lại</a>
    </div>

    <?php if ($error !== ''): ?>
        <div class="admin-flash"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="admin-form-grid">
            <div class="admin-form-group">
                <label class="admin-label">Tên phim</label>
                <input class="admin-input" type="text" name="title" value="<?= e($movie['title']) ?>" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Ngôn ngữ</label>
                <input class="admin-input" type="text" name="language" value="<?= e($movie['language']) ?>" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Thời lượng (phút)</label>
                <input class="admin-input" type="number" name="duration_min" min="1" value="<?= (int) $movie['duration_min'] ?>" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Độ tuổi</label>
                <input class="admin-input" type="text" name="age_rating" value="<?= e($movie['age_rating']) ?>">
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Trạng thái</label>
                <select class="admin-select" name="status">
                    <option value="coming_soon" <?= $movie['status'] === 'coming_soon' ? 'selected' : '' ?>>coming_soon</option>
                    <option value="now_showing" <?= $movie['status'] === 'now_showing' ? 'selected' : '' ?>>now_showing</option>
                    <option value="inactive" <?= $movie['status'] === 'inactive' ? 'selected' : '' ?>>inactive</option>
                </select>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Ngày khởi chiếu</label>
                <input class="admin-input" type="date" name="release_date" value="<?= e($movie['release_date']) ?>">
            </div>

            <div class="admin-form-group full">
                <label class="admin-label">Poster URL</label>
                <input class="admin-input" type="text" name="poster_url" value="<?= e($movie['poster_url']) ?>">
            </div>

            <div class="admin-form-group full">
                <label class="admin-label">Trailer URL</label>
                <input class="admin-input" type="text" name="trailer_url" value="<?= e($movie['trailer_url']) ?>">
            </div>

            <div class="admin-form-group full">
                <label class="admin-label">Mô tả</label>
                <textarea class="admin-textarea" name="synopsis"><?= e($movie['synopsis']) ?></textarea>
            </div>
        </div>

        <button class="admin-btn" type="submit">Cập nhật phim</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>