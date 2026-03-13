<?php
$pageTitle = 'Quản lý phim';
require_once __DIR__ . '/../../includes/admin_layout_start.php';

$conn = db_connect();

$sql = "
    SELECT movie_id, title, language, duration_min, poster_url, status, age_rating, release_date
    FROM movies
    ORDER BY created_at DESC
";
$result = $conn->query($sql);
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Danh sách phim</h2>
        <a class="admin-btn" href="<?= BASE_URL ?>admin/movies/create.php">+ Thêm phim</a>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Poster</th>
                    <th>Tên phim</th>
                    <th>Ngôn ngữ</th>
                    <th>Thời lượng</th>
                    <th>Độ tuổi</th>
                    <th>Khởi chiếu</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($movie = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <img class="admin-thumb" src="<?= e($movie['poster_url'] ?: 'https://via.placeholder.com/120x160?text=Movie') ?>" alt="">
                        </td>
                        <td><?= e($movie['title']) ?></td>
                        <td><?= e($movie['language']) ?></td>
                        <td><?= (int) $movie['duration_min'] ?> phút</td>
                        <td><?= e($movie['age_rating'] ?: 'N/A') ?></td>
                        <td><?= !empty($movie['release_date']) ? date('d/m/Y', strtotime($movie['release_date'])) : 'N/A' ?></td>
                        <td>
                            <span class="admin-badge <?= $movie['status'] === 'now_showing' ? 'showing' : 'coming' ?>">
                                <?= e($movie['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="admin-actions">
                                <a class="admin-btn-outline" href="<?= BASE_URL ?>admin/movies/edit.php?id=<?= (int) $movie['movie_id'] ?>">Sửa</a>
                                <a class="admin-btn-danger" href="<?= BASE_URL ?>admin/movies/delete.php?id=<?= (int) $movie['movie_id'] ?>" onclick="return confirm('Xóa phim này?')">Xóa</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">
                        <div class="admin-empty">Chưa có phim nào.</div>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_end.php'; ?>