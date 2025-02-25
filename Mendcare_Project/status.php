<?php
session_start();
require_once 'connect.php';

// Get user's requests
$user_id = $_SESSION['user_id'];
$sql = "SELECT r.*, ss.name as service_name, t.name as technician_name 
        FROM requests r
        LEFT JOIN sub_services ss ON r.sub_service_id = ss.sub_services_id
        LEFT JOIN technicians t ON r.technician_id = t.technician_id
        WHERE r.user_id = ?
        ORDER BY r.request_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<?php
// ตรวจสอบการล็อกอิน
if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $name = $_SESSION['name'];
    $email = $_SESSION['email'];
    $profile_picture = $_SESSION['profile_picture'];
} else {
    header('Location: ind.php');
    exit();
}

// จัดการ logout
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'logout') {
    session_destroy();
    echo json_encode([
        'status' => 'success',
        'message' => 'ออกจากระบบสำเร็จ'
    ]);
    exit();
}
?>
<?php include 'sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ติดตามงานบริการ</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="ind.css">
</head>

<body>

<div id="main-content">
        <header>
            <div class="header-container">
                <div class="nav-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </div>
                <a href="/" class="logo">
                    <i class="fas fa-wrench"></i>
                    Mendcare Service
                </a>
                <nav>
                    <ul class="nav-menu">
                        <li class="search-container">
                            <input type="search" class="search-box" placeholder="ค้นหาบริการ...">
                            <i class="fas fa-search search-icon"></i>
                        </li>
                        <li><a href="/notifications"><i class="fas fa-bell"></i></a></li>
                        <li><a href="/cart"><i class="fas fa-shopping-cart"></i></a></li>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <li>
                                <span class="logo"><?php echo $_SESSION['name']; ?></span>
                            </li>
                            <li class="auth-links">
                                <a href="javascript:void(0);" onclick="handleLogout()" class="login-btn">
                                    <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="auth-links">
                                <a href="javascript:void(0);" class="login-btn" onclick="openLoginPopup()">
                                    <i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </header>

        <div class="container">
            <h1>ติดตามสถานะงานบริการ</h1>
            
            <?php if ($result->num_rows > 0): ?>
                <div class="service-requests">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="request-card">
                            <h3>บริการ: <?php echo $row['service_name']; ?></h3>
                            <p>วันที่แจ้ง: <?php echo date('d/m/Y H:i', strtotime($row['request_date'])); ?></p>
                            <p>สถานะ: 
                                <span class="status-<?php echo $row['status']; ?>">
                                    <?php
                                    $status_text = [
                                        'pending' => 'รอการยืนยัน',
                                        'accepted' => 'กำลังดำเนินการ',
                                        'completed' => 'เสร็จสิ้น',
                                        'canceled' => 'ยกเลิก'
                                    ];
                                    echo $status_text[$row['status']];
                                    ?>
                                </span>
                            </p>
                            <?php if ($row['technician_name']): ?>
                                <p>ช่างผู้รับผิดชอบ: <?php echo $row['technician_name']; ?></p>
                            <?php endif; ?>
                            <?php if ($row['notes']): ?>
                                <p>หมายเหตุ: <?php echo $row['notes']; ?></p>
                            <?php endif; ?>
                            <?php if ($row['amount']): ?>
                                <p>ค่าบริการ: <?php echo number_format($row['amount'], 2); ?> บาท</p>
                                <p>วิธีการชำระเงิน: <?php echo $row['payment_method']; ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>ไม่พบประวัติการใช้บริการ</p>
            <?php endif; ?>
        </div>
    </div>
    <script>
        // ฟังก์ชันจัดการ Logout
function handleLogout() {
    const formData = new FormData();
    formData.append('action', 'logout');

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                title: 'ออกจากระบบสำเร็จ!',
                text: 'ขอบคุณที่ใช้บริการ',
                icon: 'success',
                confirmButtonText: 'ตกลง'
            }).then((result) => {
                location.reload();
            });
        }
    });
}
    </script>
</body>
</html>
