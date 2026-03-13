<?php
require_once __DIR__ . '/helpers.php';

$currentUser = get_current_user_data();
$flash = get_flash_message();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineBook</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css?v=5">
</head>
<body>
<header class="site-header">
    <div class="nav-wrap">
        <div class="nav-left">
            <a class="logo" href="<?= BASE_URL ?>">CINEBOOK</a>

            <nav class="menu">
                <a class="active" href="<?= BASE_URL ?>">Home</a>
                <a href="<?= BASE_URL ?>movies.php">Movies</a>
                <a class="nav-link" href="<?= BASE_URL ?>account.php">Account</a>

            </nav>
        </div>

        <div class="nav-right">
            <a class="search-btn" href="<?= BASE_URL ?>movies.php" aria-label="Search">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                    <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
                    <path d="M20 20L16.65 16.65" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </a>

            <?php if ($currentUser): ?>
                <a class="nav-link" ><?= e($currentUser['name']) ?></a>

                <?php if (($currentUser['role'] ?? 'user') === 'admin'): ?>
                    <a class="btn-register" href="<?= BASE_URL ?>admin/index.php">Admin</a>
                <?php endif; ?>

                <a class="nav-link" href="<?= BASE_URL ?>logout.php">Logout</a>
            <?php else: ?>
                <a class="nav-link" href="<?= BASE_URL ?>login.php">Login</a>
                <a class="btn-register" href="<?= BASE_URL ?>register.php">Register</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<?php if ($flash): ?>
    <div class="flash-wrap">
        <div class="flash-message flash-<?= e($flash['type'] ?? 'info') ?>">
            <?= e($flash['message']) ?>
        </div>
    </div>
<?php endif; ?>

<main class="main-content">