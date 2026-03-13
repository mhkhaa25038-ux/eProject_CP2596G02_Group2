<?php
require_once __DIR__ . '/includes/helpers.php';

$conn = db_connect();

$bannerSql = "SELECT * FROM banners WHERE status = 'active' ORDER BY created_at DESC LIMIT 1 ";
$bannerResult = $conn->query($bannerSql);
$heroBanner = ($bannerResult && $bannerResult->num_rows > 0) ? $bannerResult->fetch_assoc() : null;

$nowShowingSql = "
    SELECT movie_id, title, synopsis, language, duration_min, poster_url, age_rating, release_date, status
    FROM movies
    WHERE status = 'now_showing'
    ORDER BY created_at DESC
    LIMIT 8
";
$nowShowingResult = $conn->query($nowShowingSql);

require_once __DIR__ . '/includes/header.php';

$heroTitle = $heroBanner['title'] ?? 'Interstellar Journey';
$heroImage = $heroBanner['image_url'] ?? 'https://images.unsplash.com/photo-1446776811953-b23d57bd21aa?auto=format&fit=crop&w=1600&q=80';
?>

<section class="hero-section">
    <div class="hero-banner">
        <div class="hero-image" style="background-image: url('<?= e($heroImage) ?>');"></div>

        <div class="hero-overlay"></div>

        <div class="hero-content">
            <span class="hero-tag">NOW SHOWING</span>

            <h1 class="hero-title"><?= e($heroTitle) ?></h1>

           

            <div class="hero-actions">
                <a class="hero-btn-primary" href="<?= BASE_URL ?>movies.php?status=now_showing">Book Now</a>
                <a class="hero-btn-secondary" href="<?= BASE_URL ?>movies.php">
                    <span class="play-icon">▶</span>
                    Watch Trailer
                </a>
            </div>
        </div>
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
                        <a href="<?= BASE_URL ?>movie_detail.php?id=<?= (int)$movie['movie_id'] ?>">
                            <img class="movie-thumb" src="<?= e($movie['poster_url'] ?: 'https://via.placeholder.com/300x420?text=Movie') ?>" alt="<?= e($movie['title']) ?>">
                        </a>

                        <div class="movie-info">
                            <h3><?= e($movie['title']) ?></h3>
                            <p><?= (int)$movie['duration_min'] ?> min • <?= e($movie['language']) ?></p>
                            <a class="movie-link" href="<?= BASE_URL ?>movie_detail.php?id=<?= (int)$movie['movie_id'] ?>">View details</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">Chưa có dữ liệu phim đang chiếu.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>