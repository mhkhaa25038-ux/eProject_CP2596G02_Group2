<?php
require_once __DIR__ . '/includes/helpers.php';

$conn = db_connect();

$keyword = trim(get('keyword'));
$status = trim(get('status'));

$where = [];
$params = [];
$types = '';

if ($keyword !== '') {
    $where[] = "m.title LIKE ?";
    $params[] = '%' . $keyword . '%';
    $types .= 's';
}

if ($status === 'now_showing' || $status === 'coming_soon') {
    $where[] = "m.status = ?";
    $params[] = $status;
    $types .= 's';
}

$whereSql = '';
if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

$sql = "
    SELECT
        m.movie_id,
        m.title,
        m.synopsis,
        m.language,
        m.duration_min,
        m.poster_url,
        m.status,
        m.age_rating,
        m.release_date,
        COALESCE(AVG(r.rating), 0) AS avg_rating,
        GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ', ') AS genres
    FROM movies m
    LEFT JOIN reviews r ON m.movie_id = r.movie_id
    LEFT JOIN movie_genres mg ON m.movie_id = mg.movie_id
    LEFT JOIN genres g ON mg.genre_id = g.genre_id
    $whereSql
    GROUP BY m.movie_id
    ORDER BY m.created_at DESC
";

$stmt = $conn->prepare($sql);

if ($stmt && !empty($params)) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $moviesResult = $stmt->get_result();
} elseif ($stmt) {
    $stmt->execute();
    $moviesResult = $stmt->get_result();
} else {
    $moviesResult = false;
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="movies-page-wrap">
    <div class="movies-page-inner">
        <div class="movies-hero-top">
            <h1 class="movies-page-title">Movies</h1>
            <p class="movies-page-subtitle">
                Discover the latest movies playing in theaters now.
            </p>
        </div>

        <form method="GET" action="" class="movies-filter-bar">
            <div class="movies-search-box">
                <span class="movies-search-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
                        <path d="M20 20L16.65 16.65" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </span>

                <input
                    type="text"
                    name="keyword"
                    class="movies-search-input"
                    placeholder="Search movies..."
                    value="<?= e($keyword) ?>"
                >
            </div>

            <div class="movies-status-box">
                <select name="status" class="movies-status-select">
                    <option value="">All Status</option>
                    <option value="now_showing" <?= $status === 'now_showing' ? 'selected' : '' ?>>Now Showing</option>
                    <option value="coming_soon" <?= $status === 'coming_soon' ? 'selected' : '' ?>>Coming Soon</option>
                </select>
            </div>

            <button type="submit" class="movies-hidden-submit">Lọc</button>
        </form>

        <div class="movies-grid-dark">
            <?php if ($moviesResult && $moviesResult->num_rows > 0): ?>
                <?php while ($movie = $moviesResult->fetch_assoc()): ?>
                    <div class="movie-card-dark">
                        <a href="<?= BASE_URL ?>movie_detail.php?id=<?= (int) $movie['movie_id'] ?>" class="movie-thumb-wrap-dark">
                            <img
                                class="movie-thumb-dark"
                                src="<?= e($movie['poster_url'] ?: 'https://via.placeholder.com/400x600?text=Movie') ?>"
                                alt="<?= e($movie['title']) ?>"
                            >

                            <span class="movie-status-badge <?= $movie['status'] === 'coming_soon' ? 'coming' : 'showing' ?>">
                                <?= $movie['status'] === 'coming_soon' ? 'COMING SOON' : 'NOW SHOWING' ?>
                            </span>
                        </a>

                        <div class="movie-card-body-dark">
                            <h3 class="movie-title-dark"><?= e($movie['title']) ?></h3>

                            <p class="movie-meta-dark">
                                <?= e($movie['genres'] ?: 'Movie') ?> • <?= (int) $movie['duration_min'] ?>m
                            </p>

                            <div class="movie-card-bottom-dark">
                                <div class="movie-rating-dark">
                                    <span class="movie-star-dark">★</span>
                                    <span><?= number_format((float)$movie['avg_rating'], 1) ?></span>
                                </div>

                                <a class="movie-book-btn-dark" href="<?= BASE_URL ?>movie_detail.php?id=<?= (int) $movie['movie_id'] ?>">
                                    Book Now
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">Chưa có dữ liệu phim để hiển thị.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
if ($stmt) {
    $stmt->close();
}
require_once __DIR__ . '/includes/footer.php';
?>