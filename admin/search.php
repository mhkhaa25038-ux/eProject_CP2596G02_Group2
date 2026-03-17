<?php
$pageTitle = 'Tìm kiếm';
require_once __DIR__ . '/../includes/admin_layout_start.php';

$conn = db_connect();
$q = $_GET['q'] ?? '';
?>

<div class="admin-card">
    <div class="admin-card-head">
        <h2>Kết quả tìm kiếm: "<?= e($q) ?>"</h2>
    </div>
    <h3 style="margin-top:20px;">Banner</h3>
    <div class="admin-grid">
        <?php
        $sql1 = "SELECT * FROM banners WHERE title LIKE '%$q%'";
        $res1 = mysqli_query($conn, $sql1);

        if ($res1 && mysqli_num_rows($res1) > 0):
            while ($row = mysqli_fetch_assoc($res1)):

                $img = (!empty($row['image']))
                    ? BASE_URL . $row['image']
                    : BASE_URL . 'public/no-image.png';
        ?>
        <a href="<?= BASE_URL ?>admin/banners/edit.php?id=<?= $row['banner_id'] ?>" class="card-link">
            <div class="admin-card-item">
                <img src="<?= $img ?>" class="thumb">
                <h4><?= e($row['title']) ?></h4>
                <p><?= e($row['description'] ?? '') ?></p>
            </div>
        </a>
        <?php endwhile; else: ?>
            <div class="admin-empty">Không có banner</div>
        <?php endif; ?>
    </div>
    <h3 style="margin-top:20px;">Phim</h3>
    <div class="admin-grid">
        <?php
        $sql2 = "SELECT * FROM movies WHERE title LIKE '%$q%'";
        $res2 = mysqli_query($conn, $sql2);

        if ($res2 && mysqli_num_rows($res2) > 0):
            while ($row = mysqli_fetch_assoc($res2)):

                $img = (!empty($row['poster']))
                    ? BASE_URL . $row['poster']
                    : BASE_URL . 'public/no-image.png';
        ?>
        <a href="<?= BASE_URL ?>admin/movies/edit.php?id=<?= $row['movie_id'] ?>" class="card-link">
            <div class="admin-card-item">
                <img src="<?= $img ?>" class="thumb">
                <h4><?= e($row['title']) ?></h4>
                <p>Ngôn ngữ: <?= e($row['language'] ?? '') ?></p>
                <p>Thời lượng: <?= (int)($row['duration_min'] ?? 0) ?> phút</p>
                <p>Trạng thái: <?= e($row['status'] ?? '') ?></p>
            </div>
        </a>
        <?php endwhile; else: ?>
            <div class="admin-empty">Không có phim</div>
        <?php endif; ?>
    </div>
    <h3 style="margin-top:20px;">User</h3>
    <div class="admin-grid">
        <?php
        $sql3 = "SELECT * FROM users WHERE name LIKE '%$q%'";
        $res3 = mysqli_query($conn, $sql3);

        if ($res3 && mysqli_num_rows($res3) > 0):
            while ($row = mysqli_fetch_assoc($res3)):
        ?>
        <a href="<?= BASE_URL ?>admin/users/edit.php?id=<?= $row['user_id'] ?>" class="card-link">
            <div class="admin-card-item">
                <h4><?= e($row['name']) ?></h4>
                <p>Email: <?= e($row['email'] ?? '') ?></p>
                <p>Role: <?= e($row['role'] ?? '') ?></p>
            </div>
        </a>
        <?php endwhile; else: ?>
            <div class="admin-empty">Không có user</div>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/admin_layout_end.php'; ?>