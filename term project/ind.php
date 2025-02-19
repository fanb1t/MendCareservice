<?php
session_start();
require_once 'connect.php';

// Get cart count
$cart_count = 0;
if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $cart_sql = "SELECT COUNT(*) as count FROM requests WHERE user_id = ? AND status = 'pending'";
    $stmt = $conn->prepare($cart_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_count = $result->fetch_assoc()['count'];
}

// Handle Login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'login') {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    $sql = "SELECT user_id, name, email, profile_picture FROM users WHERE email=? AND password=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION["user_id"] = $user["user_id"];
        $_SESSION["name"] = $user["name"];
        $_SESSION["email"] = $user["email"];
        $_SESSION["profile_picture"] = $user["profile_picture"];
        
        echo json_encode([
            'status' => 'success',
            'message' => 'ยินดีต้อนรับคุณ ' . $user["name"],
            'user' => [
                'name' => $user["name"],
                'email' => $user["email"],
                'profile_picture' => $user["profile_picture"]
            ]
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง'
        ]);
    }
    exit();
}

// Handle Register
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'register') {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $phone = $_POST["phone"];

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $password, $phone);

    if ($stmt->execute()) {
        $_SESSION["user_id"] = $conn->insert_id;
        $_SESSION["name"] = $name;
        $_SESSION["email"] = $email;
        
        echo json_encode(['status' => 'success', 'message' => 'สมัครสมาชิกสำเร็จ!']);
    } else {
        if ($conn->errno == 1062) {
            echo json_encode(['status' => 'error', 'message' => 'อีเมลนี้ถูกใช้งานแล้ว']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $stmt->error]);
        }
    }
    exit();
}

// Handle Logout
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'logout') {
    session_destroy();
    echo json_encode([
        'status' => 'success',
        'message' => 'ออกจากระบบสำเร็จ'
    ]);
    exit();
}

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'กรุณาเข้าสู่ระบบก่อนเพิ่มบริการลงตะกร้า'
        ]);
        exit();
    }

    $sub_service_id = $_POST['sub_service_id'];
    $user_id = $_SESSION['user_id'];

    // Check if already in cart
    $check_sql = "SELECT request_id FROM requests WHERE user_id = ? AND sub_service_id = ? AND status = 'pending'";
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

    // Add to cart
    $insert_sql = "INSERT INTO requests (user_id, sub_service_id, status) VALUES (?, ?, 'pending')";
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
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Mendcare Service - บริการซ่อมบำรุงครบวงจร โดยทีมช่างมืออาชีพ พร้อมรับประกันผลงาน">
    <title>Mendcare Service - ระบบช่างซ่อมมืออาชีพ</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="ind.css">
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    <!-- Main Content -->
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
                         <li>
                            <a href="CollectService.php" class="cart-icon">
                            <i class="fas fa-shopping-cart"></i>
                            <?php if($cart_count > 0): ?>
                            <span class="cart-count"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                            </a>
                         </li>
                             <?php if(isset($_SESSION['user_id'])): ?>
                        <li>
                            <span class="logo"> <?php echo $_SESSION['name']; ?></span>
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
                    <!-- Modal Login -->
                    <div id="loginPopup" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeLoginPopup()">&times;</span>
        <h1>เข้าสู่ระบบ / สมัครสมาชิก</h1>
        
        <div id="login-form" class="form-container active">
            <form onsubmit="handleLogin(event)">
                <label for="email">ชื่อผู้ใช้หรืออีเมล:</label>
                <input type="text" id="email" name="email" required>
                
                <label for="password">รหัสผ่าน:</label>
                <input type="password" id="password" name="password" required>
                
                <button type="submit">เข้าสู่ระบบ</button>
            </form>
            <div class="links">
                <p>ยังไม่มีบัญชีใช่ไหม? <a href="javascript:void(0);" onclick="showRegisterForm()">สมัครสมาชิก</a></p>
            </div>
        </div>

        <div id="register-form" class="form-container">
            <form onsubmit="handleRegister(event)">
                <label for="name-register">ชื่อผู้ใช้:</label>
                <input type="text" id="name" name="name" required>
                
                <label for="email">อีเมล:</label>
                <input type="email" id="email" name="email" required>
                
                <label for="password-register">รหัสผ่าน:</label>
                <input type="password" id="password-register" name="password" required>
                
                <label for="phone">เบอร์โทรศัพท์:</label>
                <input type="text" id="phone" name="phone" required>
                
                <button type="submit">สมัครสมาชิก</button>
            </form>
            <div class="links">
                <p>มีบัญชีอยู่แล้ว? <a href="javascript:void(0);" onclick="showLoginForm()">เข้าสู่ระบบ</a></p>
            </div>
        </div>
    </div>
</div>

        <main>
            <section class="hero">
                <h1>บริการซ่อมบำรุงมืออาชีพ</h1>
                <p>มั่นใจกับทีมช่างมืออาชีพที่ผ่านการรับรอง พร้อมรับประกันผลงาน</p>
                <button class="cta-button">จองบริการเลย <i class="fas fa-arrow-right"></i></button>
            </section>
            <section class="services">
                <h2 class="section-title">บริการของเรา</h2>
                <div class="services-grid" >
                    <div class="service-card" onclick="window.location.href='airservice.php';">
                        <i class="fas fa-wind service-icon"></i>
                        <h3>ระบบปรับอากาศ</h3>
                        <p>บริการซ่อม ล้าง และติดตั้งแอร์โดยช่างผู้เชี่ยวชาญ</p>
                    </div>
                    <div class="service-card">
                        <i class="fas fa-home service-icon"></i>
                        <h3>รีโนเวทและต่อเติม</h3>
                        <p>แก้ไขปัญหาหลังคารั่ว ซ่อมแซมและเปลี่ยนหลังคา</p>
                    </div>
                    <div class="service-card">
                        <i class="fas fa-faucet service-icon"></i>
                        <h3>ห้องน้ำ/ประปา</h3>
                        <p>แก้ไขปัญหาท่อรั่ว ซ่อมก๊อกน้ำ และระบบประปา</p>
                    </div>
                    <div class="service-card">
                        <i class="fas fa-bolt service-icon"></i>
                        <h3>งานไฟฟ้า/เครื่องใช้ไฟฟ้า</h3>
                        <p>ซ่อมแซมและติดตั้งระบบไฟฟ้าภายในบ้าน</p>
                    </div>
                    <div class="service-card">
                        <i class="fas fa-broom service-icon"></i>
                        <h3>ทำความสะอาด</h3>
                        <p>บริการทำความสะอาดบ้าน สำนักงาน และพื้นที่ต่างๆ</p>
                    </div>
                    <div class="service-card">
                        <i class="fas fa-tshirt service-icon"></i>
                        <h3>ซ่อมเครื่องซักผ้า</h3>
                        <p>ซ่อมแซมและบำรุงรักษาเครื่องซักผ้าทุกประเภท</p>
                    </div>
                    <div class="service-card">
                        <i class="fas fa-tree service-icon"></i>
                        <h3>งานภายนอกบ้าน</h3>
                        <p>บริการดูแลสวน ตัดแต่งต้นไม้ และปรับปรุงภูมิทัศน์</p>
                    </div>
                    <div class="service-card">
                        <i class="fas fa-tools service-icon"></i>
                        <h3>บริการอื่นๆ</h3>
                        <p>ซ่อมแซมทั่วไปและบริการอื่นๆ ตามความต้องการ</p>
                    </div>
                </div>
            </section>
            
            <!-- Add this after the services section -->
<section class="popular-services">
    <h2 class="section-title">บริการยอดนิยม</h2>
    <div class="popular-services-grid">
        <div class="popular-service-card">
            <img src="image/air.jpg" alt="ล้างแอร์">
            <h3>ล้างแอร์</h3>
            <p>เริ่มต้น 500 บาท</p>
            <span class="popular-badge">ยอดนิยม</span>
        </div>
        <div class="popular-service-card">
            <img src="image/water.jpg" alt="ซ่อมท่อน้ำรั่ว">
            <h3>ซ่อมท่อน้ำรั่ว</h3>
            <p>เริ่มต้น 350 บาท</p>
            <span class="popular-badge">ลดราคา</span>
        </div>
        <div class="popular-service-card">
            <img src="image/elc.jpg" alt="ติดตั้งสายไฟ">
            <h3>ติดตั้งสายไฟ</h3>
            <p>เริ่มต้น 1,000 บาท</p>
            <span class="popular-badge">แนะนำ</span>
        </div>
    </div>
</section>
<section class="portfolio">
    <h2 class="section-title">ผลงานของเรา</h2>
    <div class="portfolio-grid">
        <div class="portfolio-item">
            <img src="image/air2.jpg" alt="ผลงาน 1">
            <div class="portfolio-overlay">
                <h3>งานซ่อมแอร์คอนโด</h3>
                <p>ระยะเวลา: 2 ชั่วโมง</p>
            </div>
        </div>
        <div class="portfolio-item">
            <img src="image/md1.jpg" alt="ผลงาน 2">
            <div class="portfolio-overlay">
                <h3>งานซ่อมหลังคารั่ว</h3>
                <p>ระยะเวลา: 1 วัน</p>
            </div>
        </div>
        <div class="portfolio-item">
            <img src="image/elc2.jpg" alt="ผลงาน 3">
            <div class="portfolio-overlay">
                <h3>งานติดตั้งระบบไฟ</h3>
                <p>ระยะเวลา: 3 ชั่วโมง</p>
            </div>
        </div>
    </div>
</section>
<section class="why-choose-us">
    <h2 class="section-title">เหตุผลที่ต้องเลือกเรา</h2>
    <div class="reasons-grid">
        <div class="reason-card">
            <i class="fas fa-certificate"></i>
            <h3>รับประกันผลงาน</h3>
            <p>รับประกันงานซ่อมนาน 6 เดือน พร้อมบริการหลังการขาย</p>
        </div>
        <div class="reason-card">
            <i class="fas fa-clock"></i>
            <h3>บริการ 24/7</h3>
            <p>พร้อมให้บริการตลอด 24 ชั่วโมง ไม่มีวันหยุด</p>
        </div>
        <div class="reason-card">
            <i class="fas fa-user-tie"></i>
            <h3>ช่างมืออาชีพ</h3>
            <p>ทีมช่างผ่านการอบรมและได้รับการรับรองมาตรฐาน</p>
        </div>
        <div class="reason-card">
            <i class="fas fa-shield-alt"></i>
            <h3>ความปลอดภัยสูงสุด</h3>
            <p>มีประกันความเสียหายระหว่างการซ่อมสูงสุด 100,000 บาท</p>
        </div>
    </div>
</section>      
        </main>
        <footer>
            <div class="footer-content">
                <div class="footer-section">
                    <h3>เกี่ยวกับเรา</h3>
                    <p>Mendcare Service ให้บริการซ่อมบำรุงครบวงจร ด้วยทีมช่างมืออาชีพ</p>
                </div>
                <div class="footer-section">
                    <h3>ติดต่อเรา</h3>
                    <p>โทร: 02-xxx-xxxx</p>
                    <p>อีเมล: contact@mendcare.com</p>
                </div>
                <div class="footer-section">
                    <h3>ติดตามเรา</h3>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-line"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Mendcare Service. สงวนลิขสิทธิ์.</p>
            </div>
        </footer>
    </div>
    </div>

<script>
// ฟังก์ชันสำหรับตะกร้า
function addToCart(subServiceId) {
    if (!<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
        Swal.fire({
            title: 'กรุณาเข้าสู่ระบบ',
            text: 'คุณต้องเข้าสู่ระบบก่อนเพิ่มบริการลงตะกร้า',
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'เข้าสู่ระบบ',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                openLoginPopup();
            }
        });
        return;
    }

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
            }).then(() => {
                location.reload();
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

// ฟังก์ชันสำหรับ Modal Login
function openLoginPopup() {
    document.getElementById("loginPopup").style.display = "flex";
}
function closeLoginPopup() {
    document.getElementById("loginPopup").style.display = "none";
}
function showLoginForm() {
    document.getElementById("login-form").classList.add("active");
    document.getElementById("register-form").classList.remove("active");
}
function showRegisterForm() {
    document.getElementById("register-form").classList.add("active");
    document.getElementById("login-form").classList.remove("active");
}


// Update your existing JavaScript with these functions
function handleLogin(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    formData.append('action', 'login');

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            closeLoginPopup();
            location.reload(); // Refresh page to update UI
        } else {
            alert(data.message);
        }
    });
}

function handleRegister(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    formData.append('action', 'register');

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            closeLoginPopup();
            location.reload(); // Refresh page to update UI
        } else {
            alert(data.message);
        }
    });
}

function handleLogin(event) {
    event.preventDefault();
    
    const email = document.querySelector('#login-form input[name="email"]').value;
    const password = document.querySelector('#login-form input[name="password"]').value;
    
    const formData = new FormData();
    formData.append('action', 'login');
    formData.append('email', email);
    formData.append('password', password);

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            window.location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
    });
}
function openLoginPopup() {
    document.getElementById("loginPopup").style.display = "flex";
}

function closeLoginPopup() {
    document.getElementById("loginPopup").style.display = "none";
}

function showLoginForm() {
    document.getElementById("login-form").classList.add("active");
    document.getElementById("register-form").classList.remove("active");
}

function showRegisterForm() {
    document.getElementById("register-form").classList.add("active");
    document.getElementById("login-form").classList.remove("active");
}

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
}

// อัปเดตฟังก์ชัน handleLogin
function handleLogin(event) {
    event.preventDefault();
    
    const email = document.querySelector('#login-form input[name="email"]').value;
    const password = document.querySelector('#login-form input[name="password"]').value;
    
    const formData = new FormData();
    formData.append('action', 'login');
    formData.append('email', email);
    formData.append('password', password);

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            closeLoginPopup();
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
    });
}

// อัปเดตฟังก์ชัน handleRegister
function handleRegister(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    formData.append('action', 'register');

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            closeLoginPopup();
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
    });
}

// อัปเดตฟังก์ชัน handleLogout
// เพิ่มฟังก์ชัน handleLogout
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
</body>
</html>

