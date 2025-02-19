<?php
session_start();
require 'connect.php';

// ดึงข้อมูลบริการจากฐานข้อมูล
$sql = "SELECT * FROM sub_services WHERE service_category_id = '3'";
$result = $conn->query($sql);
$services = [];
while($row = $result->fetch_assoc()) {
    $services[] = $row;
}

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

<?php
// เพิ่มโค้ดส่วนการจัดการตะกร้าในส่วนบนของไฟล์หลังจาก require 'connect.php'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_to_cart') {
        $sub_service_id = $_POST['sub_service_id'];
        $user_id = $_SESSION['user_id'];

        // ตรวจสอบว่ามีในตะกร้าแล้วหรือไม่
        $check_sql = "SELECT request_id FROM requests 
                     WHERE user_id = ? AND sub_service_id = ? 
                     AND status = 'pending'";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("ii", $user_id, $sub_service_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'บริการนี้อยู่ในตะกร้าแล้ว'
            ]);
            exit();
        }

        // เพิ่มลงตะกร้า
        $insert_sql = "INSERT INTO requests (user_id, sub_service_id, status) 
                      VALUES (?, ?, 'pending')";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("ii", $user_id, $sub_service_id);

        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'เพิ่มลงตะกร้าสำเร็จ'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาด: ' . $conn->error
            ]);
        }
        exit();
    }
}
?>

<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บริการซ่อมแอร์ - Mendcare Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="air.css">
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

        <main>
            <section class="service-intro">
                <div class="container">
                    <h1>บริการซ่อมแอร์</h1>
                    <p>ให้บริการซ่อม ล้าง ติดตั้ง และบำรุงรักษาแอร์ทุกชนิด โดยทีมช่างผู้เชี่ยวชาญ</p>
                </div>
            </section>

            <section class="service-grid">
                <div class="container">
                    <div class="grid-row">
                        <?php foreach($services as $index => $service): ?>
                            <div class="grid-item" data-product="air<?php echo $index + 1; ?>">
                                <img src="image/<?php echo $service['image']; ?>" alt="<?php echo $service['name']; ?>">
                                <h3><?php echo $service['name']; ?></h3>
                                <p>เริ่มต้น <?php echo number_format($service['price']); ?> บาท</p>
                                <div class="button-group">
                                    <button class="cart-btn" onclick="addToCart(<?php echo $service['sub_services_id']; ?>)">
                                        <i class="fas fa-cart-plus"></i> เพิ่มลงตะกร้า
                                    </button>
                                    <button class="book-btn">
                                        <i class="fas fa-calendar-check"></i> จองเลย
                                    </button>
                                </div>
                            </div>
                            <?php if(($index + 1) % 4 == 0 && $index + 1 < count($services)): ?>
                                </div><div class="grid-row">
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        </main>

        <footer>
            <p>&copy; 2025 Mendcare Service. สงวนลิขสิทธิ์.</p>
        </footer>
    </div>

    <!-- Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="product-details">
                <div class="modal-image-container">
                    <img id="modalImage" src="" alt="">
                </div>
                <div class="product-info">
                    <h2 id="modalTitle"></h2>
                    <p id="modalPrice" class="price"></p>
                    <div class="description">
                        <h3>รายละเอียดบริการ</h3>
                        <ul id="modalDescription"></ul>
                    </div>
                    <div class="button-group">
                        <button class="cart-btn">
                            <i class="fas fa-cart-plus"></i> เพิ่มลงตะกร้า
                        </button>
                        <button class="book-btn">
                            <i class="fas fa-calendar-check"></i> จองเลย
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const services = <?php echo json_encode($services); ?>;

        document.addEventListener("DOMContentLoaded", () => {
            const modal = document.getElementById("productModal");
            const modalImage = document.getElementById("modalImage");
            const modalTitle = document.getElementById("modalTitle");
            const modalPrice = document.getElementById("modalPrice");
            const modalDescription = document.getElementById("modalDescription");
            const closeModal = document.querySelector(".modal .close");

            document.querySelectorAll(".grid-item").forEach(item => {
                item.addEventListener("click", () => {
                    const index = parseInt(item.getAttribute("data-product").replace("air", "")) - 1;
                    const service = services[index];
                    
                    modalImage.src = 'image/' + service.image;
                    modalTitle.textContent = service.name;
                    modalPrice.textContent = `เริ่มต้น ${service.price} บาท`;
                    modalDescription.innerHTML = service.description
                        .split("\n")
                        .map(desc => `<li>${desc}</li>`)
                        .join("");
                    
                    modal.style.display = "block";
                });
            });

            closeModal.addEventListener("click", () => {
                modal.style.display = "none";
            });

            window.addEventListener("click", (event) => {
                if (event.target === modal) {
                    modal.style.display = "none";
                }
            });
        });

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }

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
                        showConfirmButton: false,
                        timer: 1000
                    }).then(() => {
                        window.location.reload();
                    });
                }
            });
        }
        function addToCart(subServiceId) {
    const formData = new FormData();
    formData.append('action', 'add_to_cart');
    formData.append('sub_service_id', subServiceId);

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                title: 'เพิ่มลงตะกร้าสำเร็จ!',
                icon: 'success',
                timer: 1500
            });
        } else {
            Swal.fire({
                title: 'แจ้งเตือน',
                text: data.message,
                icon: 'info'
            });
        }
    });
}
    </script>
</body>
</html>
