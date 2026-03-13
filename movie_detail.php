<?php
require_once __DIR__ . '/includes/helpers.php';

$conn = db_connect();
$movieId = (int) get('id');

if ($movieId <= 0) {
    set_flash_message('error', 'Phim không hợp lệ.');
    redirect('movies.php');
}

$movieSql = "
    SELECT
        m.movie_id,
        m.title,
        m.synopsis,
        m.language,
        m.duration_min,
        m.trailer_url,
        m.poster_url,
        m.status,
        m.age_rating,
        m.release_date,
        COALESCE(AVG(r.rating), 0) AS avg_rating,
        COUNT(DISTINCT r.review_id) AS total_reviews,
        GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ', ') AS genres
    FROM movies m
    LEFT JOIN reviews r ON m.movie_id = r.movie_id
    LEFT JOIN movie_genres mg ON m.movie_id = mg.movie_id
    LEFT JOIN genres g ON mg.genre_id = g.genre_id
    WHERE m.movie_id = ?
    GROUP BY m.movie_id
    LIMIT 1
";
$movieStmt = $conn->prepare($movieSql);
$movieStmt->bind_param('i', $movieId);
$movieStmt->execute();
$movieResult = $movieStmt->get_result();
$movie = $movieResult->fetch_assoc();
$movieStmt->close();

if (!$movie) {
    set_flash_message('error', 'Không tìm thấy phim.');
    redirect('movies.php');
}

$showtimeSql = "
    SELECT
        s.show_id,
        s.start_at,
        s.end_at,
        s.base_price,
        s.screen_format,
        s.subtitle_type,
        s.status,
        r.room_name,
        l.name AS location_name
    FROM showtimes s
    INNER JOIN rooms r ON s.room_id = r.room_id
    INNER JOIN locations l ON r.location_id = l.location_id
    WHERE s.movie_id = ?
      AND s.status = 'scheduled'
    ORDER BY s.start_at ASC
";
$showtimeStmt = $conn->prepare($showtimeSql);
$showtimeStmt->bind_param('i', $movieId);
$showtimeStmt->execute();
$showtimesResult = $showtimeStmt->get_result();

$reviewSql = "
    SELECT
        rv.review_id,
        rv.rating,
        rv.content,
        rv.created_at,
        u.name
    FROM reviews rv
    INNER JOIN users u ON rv.user_id = u.user_id
    WHERE rv.movie_id = ?
    ORDER BY rv.created_at DESC
    LIMIT 6
";
$reviewStmt = $conn->prepare($reviewSql);
$reviewStmt->bind_param('i', $movieId);
$reviewStmt->execute();
$reviewsResult = $reviewStmt->get_result();

require_once __DIR__ . '/includes/header.php';
?>

<section class="movie-detail-page">
    <div class="movie-detail-shell">
        <div class="movie-detail-main">
            <div class="movie-detail-poster">
                <img
                    src="<?= e($movie['poster_url'] ?: 'https://via.placeholder.com/500x750?text=Movie') ?>"
                    alt="<?= e($movie['title']) ?>"
                >
            </div>

            <div class="movie-detail-info">
                <div class="movie-detail-topline">
                    <span class="movie-status-badge <?= $movie['status'] === 'coming_soon' ? 'coming' : 'showing' ?>">
                        <?= $movie['status'] === 'coming_soon' ? 'COMING SOON' : 'NOW SHOWING' ?>
                    </span>
                </div>

                <h1 class="movie-detail-title"><?= e($movie['title']) ?></h1>

                <div class="movie-detail-meta">
                    <?php if (!empty($movie['genres'])): ?>
                        <span><?= e($movie['genres']) ?></span>
                    <?php endif; ?>

                    <span><?= (int) $movie['duration_min'] ?>m</span>
                    <span><?= e($movie['language']) ?></span>

                    <?php if (!empty($movie['age_rating'])): ?>
                        <span><?= e($movie['age_rating']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="movie-detail-rating-row">
                    <div class="movie-rating-dark">
                        <span class="movie-star-dark">★</span>
                        <span><?= number_format((float) $movie['avg_rating'], 1) ?></span>
                    </div>

                    <span class="movie-detail-review-count">
                        <?= (int) $movie['total_reviews'] ?> reviews
                    </span>
                </div>

                <p class="movie-detail-desc">
                    <?= e($movie['synopsis'] ?: 'Chưa có mô tả nội dung phim.') ?>
                </p>

                <div class="movie-detail-extra">
                    <div class="movie-detail-extra-item">
                        <span>Release date</span>
                        <strong>
                            <?= !empty($movie['release_date']) ? date('d/m/Y', strtotime($movie['release_date'])) : 'Updating' ?>
                        </strong>
                    </div>

                    <div class="movie-detail-extra-item">
                        <span>Trailer</span>
                        <?php if (!empty($movie['trailer_url'])): ?>
                            <a href="<?= e($movie['trailer_url']) ?>" target="_blank">Watch trailer</a>
                        <?php else: ?>
                            <strong>Updating</strong>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="movie-detail-actions">
                    <a class="movie-book-btn-dark" href="#showtimes">Book Now</a>
                    <a class="movie-detail-outline-btn" href="<?= BASE_URL ?>movies.php">Back to Movies</a>
                </div>
            </div>
        </div>

        <div class="movie-detail-block" id="showtimes">
            <div class="movie-detail-block-head">
                <h2>Showtimes</h2>
            </div>

            <?php if ($showtimesResult && $showtimesResult->num_rows > 0): ?>
                <div class="showtime-grid-dark">
                    <?php while ($show = $showtimesResult->fetch_assoc()): ?>
                        <a class="showtime-card-dark" href="<?= BASE_URL ?>select_seats.php?show_id=<?= (int) $show['show_id'] ?>">
                            <div class="showtime-card-time">
                                <?= date('d/m H:i', strtotime($show['start_at'])) ?>
                            </div>
                            <div class="showtime-card-location">
                                <?= e($show['location_name']) ?> - <?= e($show['room_name']) ?>
                            </div>
                            <div class="showtime-card-meta">
                                <?= e($show['screen_format'] ?: '2D') ?>
                                <?= !empty($show['subtitle_type']) ? ' • ' . e($show['subtitle_type']) : '' ?>
                            </div>
                            <div class="showtime-card-price">
                                <?= format_currency($show['base_price']) ?>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">Hiện chưa có suất chiếu cho phim này.</div>
            <?php endif; ?>
        </div>

        <div class="movie-detail-block">
            <div class="movie-detail-block-head">
                <h2>Reviews</h2>
            </div>

            <?php if ($reviewsResult && $reviewsResult->num_rows > 0): ?>
                <div class="review-list-dark">
                    <?php while ($review = $reviewsResult->fetch_assoc()): ?>
                        <div class="review-card-dark">
                            <div class="review-card-top">
                                <strong><?= e($review['name']) ?></strong>
                                <span><?= date('d/m/Y', strtotime($review['created_at'])) ?></span>
                            </div>

                            <div class="movie-rating-dark" style="margin-bottom:10px;">
                                <span class="movie-star-dark">★</span>
                                <span><?= (int) $review['rating'] ?>/5</span>
                            </div>

                            <p><?= e($review['content'] ?: '') ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">Chưa có đánh giá cho phim này.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
$showtimeStmt->close();
$reviewStmt->close();
require_once __DIR__ . '/includes/footer.php';
?>