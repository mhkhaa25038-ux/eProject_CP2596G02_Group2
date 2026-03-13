</main>

<footer class="site-footer-pro">
    <div class="footer-top">
        <div class="footer-brand">
            <a class="footer-logo" href="<?= BASE_URL ?>">CINEBOOK</a>
            <p class="footer-desc">
                Nền tảng đặt vé xem phim trực tuyến với giao diện hiện đại, hỗ trợ tra cứu phim,
                chọn ghế và quản lý lịch sử đặt vé nhanh chóng.
            </p>
        </div>

        <div class="footer-links-col">
            <h4>Khám phá</h4>
            <ul>
                <li><a href="<?= BASE_URL ?>">Trang chủ</a></li>
                <li><a href="<?= BASE_URL ?>movies.php">Phim</a></li>
                <li><a href="<?= BASE_URL ?>login.php">Đăng nhập</a></li>
                <li><a href="<?= BASE_URL ?>register.php">Đăng ký</a></li>
            </ul>
        </div>

        <div class="footer-links-col">
            <h4>Tài khoản</h4>
            <ul>
                <li><a href="<?= BASE_URL ?>account.php">Tài khoản cá nhân</a></li>
                <li><a href="<?= BASE_URL ?>movies.php?status=now_showing">Đang chiếu</a></li>
                <li><a href="<?= BASE_URL ?>movies.php?status=coming_soon">Sắp chiếu</a></li>
                <li><a href="<?= BASE_URL ?>admin/login.php">Admin</a></li>
            </ul>
        </div>

        <div class="footer-links-col">
            <h4>Liên hệ</h4>
            <ul class="footer-contact-list">
                <li>Email: support@cinebook.vn</li>
                <li>Hotline: 1900 1234</li>
                <li>Địa chỉ: Ninh Kiều, Cần Thơ</li>
            </ul>

            <div class="footer-socials">
                <a href="#">Facebook</a>
                <a href="#">Instagram</a>
                <a href="#">YouTube</a>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p>© 2026 CineBook. All rights reserved.</p>
        <p>Developed with PHP thuần & MySQL.</p>
    </div>
</footer>

<script src="<?= BASE_URL ?>public/assets/js/app.js"></script>
</body>
</html>