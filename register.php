<?php
require_once __DIR__ . '/includes/helpers.php';

if (is_logged_in()) {
    redirect('');
}

$conn = db_connect();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = post('name');
    $email = post('email');
    $phone = post('phone');
    $password = post('password');
    $confirmPassword = post('confirm_password');

    if ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {
        $error = 'Vui lòng nhập đầy đủ thông tin bắt buộc.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ.';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Xác nhận mật khẩu không khớp.';
    } else {
        $checkSql = "SELECT user_id FROM users WHERE email = ? LIMIT 1";
        $checkStmt = $conn->prepare($checkSql);

        if ($checkStmt) {
            $checkStmt->bind_param('s', $email);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $existingUser = $checkResult->fetch_assoc();
            $checkStmt->close();

            if ($existingUser) {
                $error = 'Email đã tồn tại.';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                $sql = "INSERT INTO users (name, email, phone, password_hash, preferred_language, status, role, loyalty_points, member_tier, email_verified)
                        VALUES (?, ?, ?, ?, 'vi', 'active', 'user', 0, 'Standard', 0)";
                $stmt = $conn->prepare($sql);

                if ($stmt) {
                    $stmt->bind_param('ssss', $name, $email, $phone, $passwordHash);

                    if ($stmt->execute()) {
                        $stmt->close();
                        set_flash_message('success', 'Đăng ký thành công. Vui lòng đăng nhập.');
                        redirect('login.php');
                    } else {
                        $error = 'Đăng ký thất bại.';
                    }

                    $stmt->close();
                } else {
                    $error = 'Không thể xử lý đăng ký.';
                }
            }
        } else {
            $error = 'Không thể kiểm tra email.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-page">
    <div class="auth-split">
        <div class="auth-visual" style="background-image: url('https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?auto=format&fit=crop&w=1400&q=80');">
            <div class="auth-visual-overlay"></div>
            <div class="auth-visual-content">
                <h1>Create your account and start booking instantly</h1>
            </div>
        </div>

        <div class="auth-panel">
            <div class="auth-card">
                <h2 class="auth-title">Register</h2>

                <?php if ($error !== ''): ?>
                    <div class="auth-error">
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label">Full name</label>
                        <input
                            class="form-control"
                            type="text"
                            name="name"
                            placeholder="Enter your full name"
                            value="<?= e(old('name')) ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input
                            class="form-control"
                            type="email"
                            name="email"
                            placeholder="Enter your email"
                            value="<?= e(old('email')) ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input
                            class="form-control"
                            type="text"
                            name="phone"
                            placeholder="Enter your phone number"
                            value="<?= e(old('phone')) ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input
                            class="form-control"
                            type="password"
                            name="password"
                            placeholder="Create your password"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm password</label>
                        <input
                            class="form-control"
                            type="password"
                            name="confirm_password"
                            placeholder="Re-enter your password"
                            required
                        >
                    </div>

                    <button class="auth-submit" type="submit">Register</button>
                </form>

                <p class="auth-bottom-text">
                    Already have an account?
                    <a href="<?= BASE_URL ?>login.php">Login</a>
                </p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>