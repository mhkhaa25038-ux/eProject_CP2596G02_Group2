<?php
require_once __DIR__ . '/includes/helpers.php';

$conn = db_connect();
$nowShowingSql = "
    SELECT movie_id, title, synopsis, language, duration_min, poster_url, age_rating, release_date, status
    FROM movies
    WHERE status = 'now_showing'
    ORDER BY created_at DESC
    LIMIT 8
";
$nowShowingResult = $conn->query($nowShowingSql);
require_once __DIR__ . '/includes/header.php';
$sql = "SELECT * FROM banners
        WHERE status='active'
        ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
$banners = [];
while ($row = mysqli_fetch_assoc($result)) {
    $banners[] = $row;
}
?>
<section class="hero-section">
    <div class="hero-banner">
        <?php foreach ($banners as $index => $banner): ?>
            <div class="banner-item" style="<?= $index === 0 ? '' : 'display:none;' ?>">
                <a href="<?= BASE_URL ?>movie_detail.php?id=<?= (int) $banner['movie_id'] ?>">
                    <img class="hero-image" src="public/uploads/banners/<?= $banner['image_url'] ?>"
                        alt="<?= $banner['title'] ?>">
                </a>
            </div>
        <?php endforeach; ?>
        <button class="banner-btn prev" onclick="prevSlide()">❮</button>
        <button class="banner-btn next" onclick="nextSlide()">❯</button>
    </div>
</section>
<section class="movies-section">
    <div class="section-inner">
        <div class="section-head">
            <h2>Now Showing</h2>
            <a href="<?= BASE_URL ?>movies.php?status=now_showing">View all</a>
        </div>

        <div class="movie-grid">
            <?php if ($nowShowingResult && $nowShowingResult->num_rows > 0): ?>
                <?php while ($movie = $nowShowingResult->fetch_assoc()): ?>
                    <div class="movie-card">
                        <a href="<?= BASE_URL ?>movie_detail.php?id=<?= (int) $movie['movie_id'] ?>">
                            <img class="movie-thumb"
                                src="<?= e($movie['poster_url'] ?: 'https://via.placeholder.com/300x420?text=Movie') ?>"
                                alt="<?= e($movie['title']) ?>">
                        </a>

                        <div class="movie-info">
                            <h3><?= e($movie['title']) ?></h3>
                            <p><?= (int) $movie['duration_min'] ?> min • <?= e($movie['language']) ?></p>
                            <a class="movie-link" href="<?= BASE_URL ?>movie_detail.php?id=<?= (int) $movie['movie_id'] ?>">View
                                details</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">Chưa có dữ liệu phim đang chiếu.</div>
            <?php endif; ?>
        </div>
    </div>
</section>
<script>
    let banners = document.querySelectorAll(".banner-item");
    let current = 0;
    function showSlide(index) {
        banners.forEach(b => b.style.display = "none");
        banners[index].style.display = "block";
    }
    function nextSlide() {
        current++;
        if (current >= banners.length) current = 0;
        showSlide(current);
    }
    function prevSlide() {
        current--;
        if (current < 0) current = banners.length - 1;
        showSlide(current);
    }
    setInterval(nextSlide, 4000);
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>