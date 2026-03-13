<?php
require_once __DIR__ . '/includes/helpers.php';

if (is_logged_in()) {
    redirect('');
}

$conn = db_connect();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = post('email');
    $password = post('password');

    if ($email === '' || $password === '') {
        $error = 'Vui lòng nhập đầy đủ email và mật khẩu.';
    } else {
        $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if (!$user) {
                $error = 'Email không tồn tại.';
            } elseif (($user['status'] ?? 'active') !== 'active') {
                $error = 'Tài khoản đã bị khóa hoặc ngưng hoạt động.';
            } elseif (!password_verify($password, $user['password_hash'])) {
                $error = 'Mật khẩu không đúng.';
            } else {
                $_SESSION['user_id'] = (int)$user['user_id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['user_name'] = $user['name'];

                set_flash_message('success', 'Đăng nhập thành công.');

                if ($user['role'] === 'admin') {
                    redirect('admin/index.php');
                }

                redirect('');
            }
        } else {
            $error = 'Không thể xử lý đăng nhập.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-page">
    <div class="auth-split">
        <div class="auth-visual" style="background-image: url('https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=1400&q=80');">
            <div class="auth-visual-overlay"></div>
            <div class="auth-visual-content">
                <h1>Experience movies like never before</h1>
            </div>
        </div>

        <div class="auth-panel">
            <div class="auth-card">
                <h2 class="auth-title">Login</h2>

                <?php if ($error !== ''): ?>
                    <div class="auth-error">
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
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
                        <label class="form-label">Password</label>
                        <input
                            class="form-control"
                            type="password"
                            name="password"
                            placeholder="Enter your password"
                            required
                        >
                    </div>

                  <div class="auth-row">
                     <a href="javascript:void(0)" class="forgot-link" onclick="alert('Vui lòng liên hệ quản trị để được đặt lại mật khẩu.')">
                         Forgot password?
                     </a>
                 </div>

                    <button class="auth-submit" type="submit">Login</button>
                </form>

                <p class="auth-bottom-text">
                    Don't have an account?
                    <a href="<?= BASE_URL ?>register.php">Sign up</a>
                </p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>