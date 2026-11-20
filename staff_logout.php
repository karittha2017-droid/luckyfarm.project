<?php
require_once __DIR__ . '/config/staff_auth.php';
startAppSession();

$csrfToken = $_POST['csrf_token'] ?? '';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['staff_csrf_token']) || !hash_equals($_SESSION['staff_csrf_token'], $csrfToken)) {
    http_response_code(403);
    exit('คำขอไม่ผ่านการตรวจสอบความปลอดภัย');
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();
header('Location: staff_login.php');
exit;
