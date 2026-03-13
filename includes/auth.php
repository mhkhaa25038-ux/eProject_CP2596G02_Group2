<?php
require_once __DIR__ . '/helpers.php';

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash_message('error', 'Vui lòng đăng nhập để tiếp tục.');
        redirect('login.php');
    }
}

function require_admin(): void
{
    if (!is_logged_in()) {
        set_flash_message('error', 'Vui lòng đăng nhập admin.');
        redirect('admin/login.php');
    }

    if (!is_admin()) {
        set_flash_message('error', 'Bạn không có quyền truy cập trang quản trị.');
        redirect('');
    }
}