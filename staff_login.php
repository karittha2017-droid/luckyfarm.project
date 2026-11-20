<?php
require_once __DIR__ . '/config/staff_auth.php';
startAppSession();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$error = '';
$passwordHash = staffPasswordHash();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    $lockedUntil = (int)($_SESSION['staff_login_locked_until'] ?? 0);
    if ($lockedUntil > time()) {
        $error = 'ลองรหัสผิดหลายครั้ง กรุณารอ ' . ($lockedUntil - time()) . ' วินาที';
    } elseif (empty($_SESSION['staff_csrf_token']) || !hash_equals($_SESSION['staff_csrf_token'], $csrfToken)) {
        http_response_code(403);
        $error = 'เซสชันหมดอายุ กรุณาโหลดหน้าแล้วลองอีกครั้ง';
    } elseif (password_verify((string)($_POST['password'] ?? ''), $passwordHash)) {
        session_regenerate_id(true);
        $_SESSION['staff_authenticated'] = true;
        $_SESSION['staff_csrf_token'] = bin2hex(random_bytes(32));
        unset($_SESSION['staff_login_attempts'], $_SESSION['staff_login_locked_until']);
        header('Location: index.php');
        exit;
    } else {
        $_SESSION['staff_login_attempts'] = (int)($_SESSION['staff_login_attempts'] ?? 0) + 1;
        if ($_SESSION['staff_login_attempts'] >= 5) {
            $_SESSION['staff_login_attempts'] = 0;
            $_SESSION['staff_login_locked_until'] = time() + 300;
            $error = 'ลองรหัสผิดครบ 5 ครั้ง ระบบพักการเข้าสู่ระบบ 5 นาที';
        } else {
            $error = 'รหัสผ่านไม่ถูกต้อง (เหลือ ' . (5 - $_SESSION['staff_login_attempts']) . ' ครั้ง)';
        }
    }
}

$csrfToken = staffCsrfToken();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบพนักงาน</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Prompt', sans-serif; }</style>
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 flex items-center justify-center p-4">
    <main class="w-full max-w-sm bg-white rounded-2xl border border-slate-200 shadow-lg p-7">
        <div class="w-12 h-12 rounded-xl bg-orange-100 text-orange-700 flex items-center justify-center text-xl mb-5">
            <i class="fa-solid fa-user-lock"></i>
        </div>
        <h1 class="text-xl font-bold text-slate-900">เข้าสู่ระบบพนักงาน</h1>
        <p class="text-sm text-slate-500 mt-1">สำหรับแคชเชียร์ ครัว สต็อก และรายงาน</p>

        <?php if ($error !== ''): ?>
            <p class="mt-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form method="post" class="mt-5 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <label class="block text-sm font-semibold text-slate-700" for="staffPassword">รหัสผ่านพนักงาน</label>
            <input id="staffPassword" name="password" type="password" autocomplete="current-password" required class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-orange-500">
            <button type="submit" class="w-full px-4 py-2.5 rounded-lg bg-orange-600 hover:bg-orange-700 text-white font-semibold transition">
                เข้าสู่ระบบ
            </button>
        </form>
    </main>
</body>
</html>
