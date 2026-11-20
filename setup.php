<?php
/**
 * Setup Script: สร้างฐานข้อมูลและนำเข้า Schema + ข้อมูลเริ่มต้น
 * สามารถเปิดผ่านเบราว์เซอร์ http://localhost/noodle/setup.php หรือรันผ่าน CLI
 */

define('APP_NAME', 'ติดตั้งระบบ - ร้านมันส์อร่อย ขาหมู นครปฐม');
require_once __DIR__ . '/config/staff_auth.php';
if (PHP_SAPI !== 'cli') {
    requireStaffPage('staff_login.php');
}
$host = 'localhost';
$port = '3306';
$user = 'root';
$pass = '';
$dbname = 'mans_aroy_db';

$messages = [];
$success = false;

try {
    // 1. เชื่อมต่อ MySQL Server
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $messages[] = "✅ เชื่อมต่อ MySQL Server สำเร็จ ($host:$port)";

    // 2. อ่านไฟล์ SQL Schema
    $sqlFile = __DIR__ . '/sql/schema.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("ไม่พบไฟล์ schema.sql ที่ตำแหน่ง $sqlFile");
    }

    $sqlContent = file_get_contents($sqlFile);
    
    // 3. รันคำสั่ง SQL
    // แยกคำสั่งด้วยเซมิโคลอน
    $pdo->exec($sqlContent);
    $messages[] = "✅ สร้างฐานข้อมูล `$dbname` และตารางทั้งหมดสำเร็จ";

    // 4. ตรวจสอบข้อมูลในตาราง
    $pdo->exec("USE `$dbname`");
    $menuCount = $pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();
    $tableCount = $pdo->query("SELECT COUNT(*) FROM tables")->fetchColumn();
    $categoryCount = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();

    $messages[] = "✅ บันทึกหมวดหมู่อาหารเริ่มต้น $categoryCount หมวด";
    $messages[] = "✅ บันทึกเมนูอาหารเริ่มต้น $menuCount รายการ (พร้อมรูปภาพจากร้านจริง)";
    $messages[] = "✅ สร้างข้อมูลโต๊ะรองรับ $tableCount โต๊ะ (โต๊ะ 1 - โต๊ะ 5)";
    $messages[] = "🎉 ติดตั้งฐานข้อมูลเสร็จสมบูรณ์ พร้อมใช้งานทันที!";
    $success = true;

} catch (Exception $e) {
    $messages[] = "❌ เกิดข้อผิดพลาด: " . $e->getMessage();
    $success = false;
}

// ถ้าเป็น CLI
if (php_sapi_name() === 'cli') {
    echo implode("\n", $messages) . "\n";
    exit($success ? 0 : 1);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ติดตั้งระบบฐานข้อมูล - ร้านมันส์อร่อย ขาหมู นครปฐม</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts: Prompt & Sarabun -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; }
    </style>
</head>
<body class="bg-amber-50/50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-xl w-full bg-white rounded-3xl shadow-xl border border-amber-100 p-8">
        <div class="text-center mb-6">
            <div class="w-20 h-20 mx-auto mb-4 bg-amber-500 text-white rounded-full flex items-center justify-center text-3xl shadow-lg shadow-amber-200">
                <i class="fa-solid fa-utensils"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">ร้านมันส์อร่อย ขาหมู นครปฐม</h1>
            <p class="text-gray-500 text-sm mt-1">ระบบติดตั้งฐานข้อมูลและเตรียมความพร้อม (Database Setup)</p>
        </div>

        <div class="space-y-3 mb-8">
            <?php foreach ($messages as $msg): ?>
                <div class="p-3.5 rounded-xl text-sm font-medium flex items-center gap-3 <?= str_starts_with($msg, '❌') ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-100' ?>">
                    <span><?= htmlspecialchars($msg) ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($success): ?>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <a href="views/cashier.php" class="flex flex-col items-center justify-center p-4 rounded-2xl bg-amber-500 hover:bg-amber-600 text-white font-medium transition shadow-md shadow-amber-200 text-center">
                    <i class="fa-solid fa-mobile-screen-button text-2xl mb-1"></i>
                    <span class="text-xs">เปิดรอบสั่งอาหารจากแคชเชียร์</span>
                </a>
                <a href="views/kds.php" class="flex flex-col items-center justify-center p-4 rounded-2xl bg-slate-800 hover:bg-slate-900 text-white font-medium transition shadow-md text-center">
                    <i class="fa-solid fa-kitchen-set text-2xl mb-1"></i>
                    <span class="text-xs">หน้าจอครัว (KDS)</span>
                </a>
                <a href="views/cashier.php" class="flex flex-col items-center justify-center p-4 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-medium transition shadow-md shadow-emerald-200 text-center">
                    <i class="fa-solid fa-cash-register text-2xl mb-1"></i>
                    <span class="text-xs">แคชเชียร์ & ใบเสร็จ</span>
                </a>
            </div>
            <div class="mt-4 text-center">
                <a href="index.php" class="text-sm text-gray-500 hover:text-amber-600 underline">
                    <i class="fa-solid fa-house mr-1"></i> กลับสู่หน้าหลัก Portal
                </a>
            </div>
        <?php else: ?>
            <div class="text-center">
                <button onclick="location.reload()" class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl transition">
                    <i class="fa-solid fa-rotate-right mr-2"></i> ลองใหม่อีกครั้ง
                </button>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
