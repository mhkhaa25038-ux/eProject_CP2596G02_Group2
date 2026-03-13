<?php
$pageTitle = 'Thêm phim';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();
$error = '';

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
        $error = 'Vui lòng nhập đầy đủ tên phim, ngôn ngữ và thời lượng.';
    } else {
        $synopsis = $synopsis !== '' ? $synopsis : null;
        $trailerUrl = $trailerUrl !== '' ? $trailerUrl : null;
        $posterUrl = $posterUrl !== '' ? $posterUrl : null;
        $ageRating = $ageRating !== '' ? $ageRating : null;
        $releaseDate = $releaseDate !== '' ? $releaseDate : null;
        $status = $status !== '' ? $status : 'coming_soon';

        $sql = "
            INSERT INTO movies (
                title, synopsis, language, duration_min, trailer_url,
                poster_url, status, age_rating, release_date
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $error = 'Lỗi prepare: ' . $conn->error;
        } else {
            $stmt->bind_param(
                'sssisssss',
                $title,
                $synopsis,
                $language,
                $durationMin,
                $trailerUrl,
                $posterUrl,
                $status,
                $ageRating,
                $releaseDate
            );

            if ($stmt->execute()) {
                $stmt->close();
                set_flash_message('success', 'Thêm phim thành công.');
                redirect('admin/movies/index.php');
            } else {
                $error = 'Lỗi thêm phim: ' . $stmt->error;
            }

            $stmt->close();
        }
    }
}
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Form thêm phim</h2>
        <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/movies/index.php">Quay lại</a>
    </div>

    <?php if ($error !== ''): ?>
        <div class="admin-flash"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="admin-form-grid">
            <div class="admin-form-group">
                <label class="admin-label">Tên phim</label>
                <input class="admin-input" type="text" name="title" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Ngôn ngữ</label>
                <input class="admin-input" type="text" name="language" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Thời lượng (phút)</label>
                <input class="admin-input" type="number" name="duration_min" min="1" required>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Độ tuổi</label>
                <input class="admin-input" type="text" name="age_rating" placeholder="P, C13, C16...">
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Trạng thái</label>
                <select class="admin-select" name="status">
                    <option value="coming_soon">coming_soon</option>
                    <option value="now_showing">now_showing</option>
                    <option value="inactive">inactive</option>
                </select>
            </div>

            <div class="admin-form-group">
                <label class="admin-label">Ngày khởi chiếu</label>
                <input class="admin-input" type="date" name="release_date">
            </div>

            <div class="admin-form-group full">
                <label class="admin-label">Poster URL</label>
                <input class="admin-input" type="text" name="poster_url">
            </div>

            <div class="admin-form-group full">
                <label class="admin-label">Trailer URL</label>
                <input class="admin-input" type="text" name="trailer_url">
            </div>

            <div class="admin-form-group full">
                <label class="admin-label">Mô tả</label>
                <textarea class="admin-textarea" name="synopsis"></textarea>
            </div>
        </div>

        <button class="admin-btn" type="submit">Lưu phim</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>