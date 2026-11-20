<?php
/**
 * Main Portal & Landing Page
 * ร้านมันส์อร่อย ขาหมู นครปฐม
 */
require_once __DIR__ . '/config/staff_auth.php';
requireStaffPage('staff_login.php');
require_once __DIR__ . '/config/db.php';

$pageTitle = 'หน้าหลักระบบจัดการร้านอาหาร';
$restaurant = getRestaurantInfo();

try {
    $pdo = getDbConnection();
    $tableCounts = $pdo->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
        SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) as occupied,
        SUM(CASE WHEN status = 'payment_pending' THEN 1 ELSE 0 END) as pending
        FROM tables")->fetch();

    $activeOrdersCount = $pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('pending', 'cooking')")->fetchColumn();
    $lowStockCount = $pdo->query("SELECT COUNT(*) FROM inventory_items WHERE quantity <= min_threshold")->fetchColumn();
} catch (Exception $e) {
    // กรณีที่ยังไม่ได้ติดตั้ง DB
    header('Location: setup.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($restaurant['name']) ?> - ระบบจัดการร้านอาหาร</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c',
                            800: '#9a3412',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Font: Prompt -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">

    <!-- Top Navigation -->
    <nav class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-brand-600 to-amber-500 flex items-center justify-center text-white shadow-md shadow-brand-500/20">
                    <i class="fa-solid fa-bowl-food text-lg"></i>
                </div>
                <div>
                    <span class="font-bold text-slate-900 text-lg block leading-tight"><?= htmlspecialchars($restaurant['name']) ?></span>
                    <span class="text-xs text-brand-600 font-medium">นครปฐม • สูตรเด็ดอร่อยต้องปรุง</span>
                </div>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <form action="staff_logout.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(staffCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-red-50 text-slate-700 hover:text-red-700 font-medium transition flex items-center gap-1.5">
                        <i class="fa-solid fa-right-from-bracket"></i> ออกจากระบบ
                    </button>
                </form>
                <a href="setup.php" class="px-3 py-1.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-600 font-medium transition flex items-center gap-1.5">
                    <i class="fa-solid fa-database text-amber-500"></i> ติดตั้ง / รีเซ็ตฐานข้อมูล
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1">
        
        <!-- Hero Banner -->
        <div class="relative rounded-3xl overflow-hidden bg-gradient-to-r from-brand-800 via-orange-700 to-amber-600 text-white p-8 sm:p-12 mb-10 shadow-xl shadow-brand-700/20">
            <div class="relative z-10 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-sm text-xs font-semibold text-white mb-3">
                    <i class="fa-solid fa-store"></i> ระบบจัดการร้านอาหารและสั่งอาหารผ่าน QR Code
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight">
                    <?= htmlspecialchars($restaurant['name']) ?>
                </h1>
                <p class="text-sm sm:text-base text-orange-100 mt-3 leading-relaxed">
                    ขาหมูสูตรโบราณตุ๋นเข้าเนื้อ ก๋วยเตี๋ยวต้มยำโบราณ และต้มเลือดหมูใบตำลึงสด พร้อมระบบสั่งอาหารอัจฉริยะที่ส่งตรงเข้าห้องครัวและแคชเชียร์แบบเรียลไทม์
                </p>

                <!-- Restaurant Details Grid -->
                <div class="mt-6 pt-6 border-t border-white/20 grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs text-orange-100">
                    <div>
                        <span class="text-white/60 block mb-0.5">ที่อยู่ร้าน:</span>
                        <span class="font-medium text-white"><?= htmlspecialchars($restaurant['address']) ?></span>
                    </div>
                    <div>
                        <span class="text-white/60 block mb-0.5">เบอร์ติดต่อ / พร้อมเพย์:</span>
                        <span class="font-bold text-white text-sm"><i class="fa-solid fa-phone mr-1"></i><?= htmlspecialchars($restaurant['phone']) ?></span>
                    </div>
                    <div>
                        <span class="text-white/60 block mb-0.5">เวลาเปิดทำการ:</span>
                        <span class="font-medium text-white"><i class="fa-solid fa-clock mr-1"></i><?= htmlspecialchars($restaurant['open_hours']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Background subtle pattern -->
            <div class="absolute -right-10 -bottom-10 opacity-10 text-white text-[280px] pointer-events-none">
                <i class="fa-solid fa-bowl-rice"></i>
            </div>
        </div>

        <!-- Live Status Bar -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 mb-8">
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-chair"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-500 block">โต๊ะว่าง</span>
                    <span class="text-xl font-bold text-emerald-600"><?= $tableCounts['available'] ?? 5 ?> / 5</span>
                </div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-500 block">โต๊ะกำลังทาน</span>
                    <span class="text-xl font-bold text-brand-600"><?= $tableCounts['occupied'] ?? 0 ?> โต๊ะ</span>
                </div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-500 block">รอชำระเงิน</span>
                    <span class="text-xl font-bold text-amber-600"><?= $tableCounts['pending'] ?? 0 ?> โต๊ะ</span>
                </div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-fire-burner"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-500 block">ออเดอร์ในครัว</span>
                    <span class="text-xl font-bold text-purple-600"><?= $activeOrdersCount ?? 0 ?> คิว</span>
                </div>
            </div>
            <div class="bg-white p-4 rounded-2xl border <?= ($lowStockCount ?? 0) > 0 ? 'border-amber-300 bg-amber-50/50' : 'border-slate-200' ?> shadow-sm flex items-center gap-3 col-span-2 sm:col-span-1">
                <div class="w-10 h-10 rounded-xl <?= ($lowStockCount ?? 0) > 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600' ?> flex items-center justify-center text-lg">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <div>
                    <span class="text-xs text-slate-500 block">วัตถุดิบใกล้หมด</span>
                    <span class="text-xl font-bold <?= ($lowStockCount ?? 0) > 0 ? 'text-amber-700' : 'text-slate-800' ?>"><?= $lowStockCount ?? 0 ?> รายการ</span>
                </div>
            </div>
        </div>

        <!-- 4 Role Portals Grid -->
        <h2 class="text-xl font-extrabold text-slate-900 mb-5 flex items-center gap-2">
            <i class="fa-solid fa-network-wired text-brand-600"></i> เลือกเข้าสู่บทบาทการใช้งาน (Roles & Subsystems)
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-5 mb-8">
            
            <!-- 1. Customer Ordering Card -->
            <div class="bg-white rounded-3xl p-5 border-2 border-slate-200 hover:border-brand-500 shadow-sm hover:shadow-xl transition flex flex-col justify-between group">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center text-xl mb-3.5 group-hover:scale-110 transition-transform shadow-sm">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-brand-600">Role 1: ลูกค้า</span>
                    <h3 class="text-lg font-bold text-slate-900 mt-1 mb-1.5">หน้าสั่งอาหารผ่าน QR</h3>
                    <p class="text-xs text-slate-500 leading-relaxed mb-4">
                        พนักงานออก QR ประจำรอบจากหน้าแคชเชียร์ก่อน ลูกค้าจึงสแกนเลือกเมนูและส่งออเดอร์ได้
                    </p>
                </div>
                
                <div class="space-y-2 pt-3 border-t border-slate-100">
                    <a href="views/cashier.php" class="w-full py-2.5 px-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-xs shadow-md shadow-brand-500/20 transition flex items-center justify-center gap-1.5">
                        <span>ไปหน้าแคชเชียร์เพื่อออก QR</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                    <div class="flex items-center justify-center gap-1 pt-0.5 text-[10px] text-slate-500">
                        <span>QR เดิมหมดอายุเมื่อยืนยันชำระเงิน</span>
                    </div>
                </div>
            </div>

            <!-- 2. Kitchen Display System Card -->
            <div class="bg-white rounded-3xl p-5 border-2 border-slate-200 hover:border-slate-800 shadow-sm hover:shadow-xl transition flex flex-col justify-between group">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-800 flex items-center justify-center text-xl mb-3.5 group-hover:scale-110 transition-transform shadow-sm">
                        <i class="fa-solid fa-kitchen-set"></i>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Role 2: พนักงานครัว</span>
                    <h3 class="text-lg font-bold text-slate-900 mt-1 mb-1.5">จอแสดงผลครัว (KDS)</h3>
                    <p class="text-xs text-slate-500 leading-relaxed mb-4">
                        แสดงออเดอร์เรียลไทม์ พร้อม Badge เตือนประเภทเส้น 🍜 และข้อจำกัดการแพ้อาหาร ⚠️ เสียงเตือนออเดอร์เข้า
                    </p>
                </div>
                
                <div class="pt-3 border-t border-slate-100">
                    <a href="views/kds.php" class="w-full py-2.5 px-3 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs shadow-md transition flex items-center justify-center gap-1.5">
                        <span>เปิดจอครัว KDS</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- 3. Cashier & POS Dashboard Card -->
            <div class="bg-white rounded-3xl p-5 border-2 border-slate-200 hover:border-emerald-600 shadow-sm hover:shadow-xl transition flex flex-col justify-between group">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl mb-3.5 group-hover:scale-110 transition-transform shadow-sm">
                        <i class="fa-solid fa-cash-register"></i>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Role 3: แคชเชียร์</span>
                    <h3 class="text-lg font-bold text-slate-900 mt-1 mb-1.5">แคชเชียร์ POS</h3>
                    <p class="text-xs text-slate-500 leading-relaxed mb-4">
                        เช็คบิล ชำระเงินสด / PromptPay QR คำนวณเงินทอนอัตโนมัติ และพิมพ์สลิปพร้อม QR โอนเงินทันที
                    </p>
                </div>
                
                <div class="pt-3 border-t border-slate-100">
                    <a href="views/cashier.php" class="w-full py-2.5 px-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md shadow-emerald-500/20 transition flex items-center justify-center gap-1.5">
                        <span>เปิดระบบแคชเชียร์</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- 4. Inventory & Stock Management Card -->
            <div class="bg-white rounded-3xl p-5 border-2 border-slate-200 hover:border-amber-500 shadow-sm hover:shadow-xl transition flex flex-col justify-between group">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl mb-3.5 group-hover:scale-110 transition-transform shadow-sm">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Role 4: จัดการสต็อก</span>
                    <h3 class="text-lg font-bold text-slate-900 mt-1 mb-1.5">คลังสต็อก & วัตถุดิบ</h3>
                    <p class="text-xs text-slate-500 leading-relaxed mb-4">
                        ระบบตัดสต็อกอัตโนมัติตามสูตรอาหาร (Recipe) เตือนสต็อกต่ำ เติมสต็อก ปรับยอดของเสีย และดูประวัติเคลื่อนไหว
                    </p>
                </div>
                
                <div class="pt-3 border-t border-slate-100">
                    <a href="views/inventory.php" class="w-full py-2.5 px-3 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs shadow-md shadow-amber-500/20 transition flex items-center justify-center gap-1.5">
                        <span>จัดการสต็อกวัตถุดิบ</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- Menu Management Card -->
            <div class="bg-white rounded-3xl p-5 border-2 border-slate-200 hover:border-orange-500 shadow-sm hover:shadow-xl transition flex flex-col justify-between group">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center text-xl mb-3.5 group-hover:scale-110 transition-transform shadow-sm">
                        <i class="fa-solid fa-utensils"></i>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-orange-700">จัดการรายการขาย</span>
                    <h3 class="text-lg font-bold text-slate-900 mt-1 mb-1.5">จัดการเมนูอาหาร</h3>
                    <p class="text-xs text-slate-500 leading-relaxed mb-4">
                        เพิ่มและแก้ไขเมนู ราคา รูปภาพ ตัวเลือกเส้น และสถานะการขาย
                    </p>
                </div>
                <div class="pt-3 border-t border-slate-100">
                    <a href="views/menu_management.php" class="w-full py-2.5 px-3 bg-orange-600 hover:bg-orange-700 text-white font-bold rounded-xl text-xs shadow-md transition flex items-center justify-center gap-1.5">
                        <span>เปิดหน้าจัดการเมนู</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- 5. Analytics Dashboard Card -->
            <div class="bg-white rounded-3xl p-5 border-2 border-slate-200 hover:border-purple-600 shadow-sm hover:shadow-xl transition flex flex-col justify-between group">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl mb-3.5 group-hover:scale-110 transition-transform shadow-sm">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-purple-700">Role 5: เจ้าของร้าน</span>
                    <h3 class="text-lg font-bold text-slate-900 mt-1 mb-1.5">แดชบอร์ดวิเคราะห์ร้าน</h3>
                    <p class="text-xs text-slate-500 leading-relaxed mb-4">
                        วิเคราะห์ยอดขาย กราฟแนวโน้ม ช่วงเวลาเร่งด่วน เมนูขายดี สถิติการเลือกเส้น และประสิทธิภาพโต๊ะ 1-5
                    </p>
                </div>
                
                <div class="pt-3 border-t border-slate-100">
                    <a href="views/analytics.php" class="w-full py-2.5 px-3 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl text-xs shadow-md shadow-purple-500/20 transition flex items-center justify-center gap-1.5">
                        <span>เปิดแดชบอร์ดวิเคราะห์</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

        </div>

        <!-- 4. QR Code Generator Banner -->
        <div class="bg-slate-900 text-white rounded-3xl p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4 text-center sm:text-left">
                <div class="w-12 h-12 rounded-2xl bg-brand-500 text-white flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-qrcode"></i>
                </div>
                <div>
                    <h4 class="font-bold text-base">ระบบจำลองและพิมพ์ QR Code ประจำโต๊ะ (Table 1 - 5)</h4>
                    <p class="text-xs text-slate-400">สำหรับปรินต์ป้ายตั้งโต๊ะ หรือสลับทดสอบการทำงานของแต่ละโต๊ะ</p>
                </div>
            </div>
            <a href="views/qr_generator.php" class="px-5 py-2.5 bg-white hover:bg-slate-100 text-slate-900 font-bold rounded-xl text-xs shadow-sm transition whitespace-nowrap">
                <i class="fa-solid fa-external-link mr-1.5"></i> ดู QR Code ทั้ง 5 โต๊ะ
            </a>
        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500">
        <p>© <?= date('Y') ?> <?= htmlspecialchars($restaurant['name']) ?> • ระบบสั่งอาหารและบริหารจัดการร้านอาหาร</p>
    </footer>

</body>
</html>
