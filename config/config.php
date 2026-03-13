<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Ho_Chi_Minh');

define('BASE_URL', 'http://localhost:1000/cinebook/');
define('DB_HOST', 'localhost');
define('DB_NAME', 'cinebook');
define('DB_USER', 'root');
define('DB_PASS', '');