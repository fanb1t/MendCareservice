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
// Handle Search
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'search') {
    if(empty($_POST['term'])) {
        echo json_encode([]);
        exit();
    }
    
    $searchTerm = '%' . filter_var($_POST['term'], FILTER_SANITIZE_STRING) . '%';
    
    $sql = "SELECT ss.*, sc.name as category_name 
            FROM sub_services ss 
            JOIN service_categories sc ON ss.service_category_id = sc.service_category_id 
            WHERE ss.name LIKE ? OR ss.description LIKE ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $services = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($services);
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
    <style>

    :root {
    --primary-color: #d00000;
    --secondary-color: #2c2828;
    --accent-color: #cdff2a;
    --background-color: #f4f4f4;
    --text-color: #333;
    --white: #ffffff;
    --gray: #666;
    --light-gray: #eee;
    --z-sidebar: 1001;
    --z-overlay: 1000;
    --z-modal: 1002;
    --z-form: 1003;
    * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    }

    body {
    font-family: 'Kanit', Arial, sans-serif;
    background-color: var(--background-color);
    color: var(--text-color);
    line-height: 1.6;
    }

    /* Header Styles */
    header {
    background-color: var(--primary-color);
    padding: 1rem;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    /* ส่วนค้นหา */
    .search-container {
    position: relative;
    display: flex;
    align-items: center;
    }

    .search-icon {
    position: absolute;
    right: 10px;
    color: #666;
    cursor: pointer;
    }

    }
    .header-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    }

.logo {
    color: var(--white);
    font-size: 1.5rem;
    font-weight: bold;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* ส่วนผลลัพธ์การค้นหา */
.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    width: 300px;
    max-height: 400px;
    overflow-y: auto;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    z-index: 1000;
    display: none;
}

.search-result-item {
    padding: 10px 15px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
}

.search-result-item:hover {
    background: #f5f5f5;
}
.search-result-item h4 {
    margin: 0;
    color: #333;
}

.search-result-item p {
    margin: 5px 0 0;
    font-size: 14px;
    color: #666;
}

.no-results {
    padding: 15px;
    text-align: center;
    color: #666;
}

/* ส่วนตะกร้าสินค้า */
.cart-icon {
    position: relative;
}

.cart-count {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #ff4444;
    color: white;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 12px;
    font-weight: bold;
}

/* ปุ่มเปิด-ปิด Sidebar */
.nav-toggle {
    padding: 10px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.nav-toggle i {
    font-size: 1.5rem;
    color: white;
}

.nav-toggle:hover {
    transform: scale(1.05);
}
/* Sidebar หลัก */
.sidebar {
    height: 100%;
    width: 0;
    position: fixed;
    z-index: 1001;
    top: 0;
    left: 0;
    background-color: var(--primary-color);
    overflow-x: hidden;
    transition: 0.3s;
    padding-top: 60px;
}

.sidebar.active {
    width: 280px;
}

/* การจัดลำดับชั้น z-index */
.modal {
    z-index: 1000;
    position: fixed;
}

.modal-content {
    z-index: 1001;
    position: relative;
}

.form-container {
    z-index: 1002;
}

input, textarea {
    position: relative;
    z-index: 1003;
}

/* Overlay */
.overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
}

.overlay.active {
    display: block;
}
.logo i {
    font-size: 1.8rem;
}

/* เมนูนำทาง */
.nav-menu {
    display: flex;
    gap: 2rem;
    align-items: center;
    list-style: none;
}

.nav-menu a {
    color: var(--white);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: color 0.3s ease;
}

.nav-menu a:hover {
    color: var(--secondary-color);
}

.search-box {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    border: none;
    width: 250px;
    font-size: 0.9rem;
}

/* สไตล์ Sidebar */
.sidebar-menu {
    padding: 0;
    list-style: none;
}

.sidebar-menu li {
    margin: 5px 0;
}

.sidebar-menu a {
    padding: 12px 25px;
    text-decoration: none;
    font-size: 1.1rem;
    color: var(--white);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s ease;
}
.sidebar-menu a:hover {
    background-color: rgba(255,255,255,0.1);
    padding-left: 35px;
}

.sidebar .close-btn {
    position: absolute;
    top: 15px;
    right: 25px;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--white);
    transition: transform 0.3s ease;
}

.sidebar .close-btn:hover {
    transform: rotate(90deg);
}

/* ส่วนการยืนยันตัวตน */
.auth-links {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.login-btn, .register-btn {
    padding: 0.5rem 1rem;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.login-btn {
    color: #333;
    background-color: transparent;
    border: 1px solid #ddd;
}

.login-btn:hover {
    background-color: #f5f5f5;
}
.register-btn {
    color: white;
    background-color: #007bff;
    border: 1px solid #007bff;
}

.register-btn:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}

/* Modal และ Form Styles */
.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    justify-content: center;
    align-items: center;
    z-index: 999999;
}

.modal-content {
    background: linear-gradient(to bottom right, #ffffff, #f8f9fa);
    padding: 2.5rem;
    border-radius: 20px;
    width: 90%;
    max-width: 400px;
    position: relative;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

/* Animation */
.modal.active {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
.modal.active {
    display: flex;
}

.modal-content {
    background: #fff;
    padding: 2rem;
    border-radius: 12px;
    width: min(400px, 90%);
    position: relative;
    z-index: 100000;
    transform: translateY(0);
    opacity: 1;
}
/* ปรับแต่ง Modal Header */
.modal-content h1 {
    color: var(--primary-color);
    font-size: 2rem;
    margin-bottom: 1.5rem;
    text-align: center;
}

/* ปรับแต่ง Modal Footer */
.modal-footer {
    margin-top: 1.5rem;
    text-align: center;
    color: #666;
}

.modal-footer a {
    color: var(--primary-color);
    font-weight: 600;
    text-decoration: none;
    transition: color 0.3s ease;
}

.modal-footer a:hover {
    color: #ff4d4d;
}
.form-container {
    position: relative;
    z-index: 100001;
    width: 100%;
}

/* ปรับแต่งฟอร์ม */
.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #2d3436;
}

input[type="email"],
input[type="password"],
input[type="text"],
input[type="tel"] {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e1e1e1;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(208, 0, 0, 0.1);
}

/* ปรับแต่งปุ่ม */
.btn-primary {
    background: linear-gradient(45deg, var(--primary-color), #ff4d4d);
    color: white;
    padding: 12px 20px;
    border-radius: 10px;
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: transform 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(208, 0, 0, 0.2);
}


.close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    font-size: 1.5rem;
    cursor: pointer;
    color: #333;
    transition: color 0.3s ease;
}
.close:hover {
    color: var(--primary-color);
}

.overlay {
    z-index: 99998;
}

input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
}

button {
    width: 100%;
    padding: 12px;
    margin-top: 15px;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 18px;
    cursor: pointer;
    transition: background 0.3s;
}

button:hover {
    background: #564aa3;
}

.links {
    margin-top: 15px;
}

.links a {
    color: #667eea;
    text-decoration: none;
    font-weight: bold;
}

.links a:hover {
    text-decoration: underline;
}
/* Animations */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideIn {
    from {
        transform: translateY(-100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-content {
    animation: slideIn 0.3s ease-out;
}

/* Media Queries */
@media (max-width: 768px) {
    .nav-menu {
        display: none;
    }
    
    .sidebar.active {
        width: 100%;
    }

    .auth-links {
        flex-direction: column;
        gap: 0.5rem;
    }

    .login-btn, .register-btn {
        width: 100%;
        text-align: center;
    }
}

:root {
    --transition-speed: 0.3s;
    --transition-timing: ease;
}
/* เพิ่ม Animation */
@keyframes modalFadeIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-content {
    animation: modalFadeIn 0.4s ease-out;
}

</style>
</head>
<body>

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
<!-- Sidebar Navigation -->
<div id="sidebar" class="sidebar">
    <span class="close-btn" onclick="toggleSidebar()">
        <i class="fas fa-times"></i>
    </span>
    <ul class="sidebar-menu">
        <li><a href="index.php"><i class="fas fa-home"></i> หน้าแรก</a></li>
        <li><a href="services.php"><i class="fas fa-tools"></i> บริการทั้งหมด</a></li>
        <li><a href="booking.php"><i class="fas fa-calendar-alt"></i> จองบริการ</a></li>
        <li><a href="tracking.php"><i class="fas fa-map-marker-alt"></i> ติดตามงาน</a></li>
        <li><a href="history.php"><i class="fas fa-history"></i> ประวัติการใช้บริการ</a></li>
        <li><a href="promotions.php"><i class="fas fa-gift"></i> โปรโมชั่น</a></li>
        <li><a href="reviews.php"><i class="fas fa-star"></i> รีวิวจากลูกค้า</a></li>
        <li><a href="faq.php"><i class="fas fa-question-circle"></i> คำถามที่พบบ่อย</a></li>
        <li><a href="contact.php"><i class="fas fa-phone"></i> ติดต่อเรา</a></li>
    </ul>
</div>

<!-- Header -->
<header>
    <div class="header-container">
        <div class="nav-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </div>
        <a href="index.php" class="logo">
            <i class="fas fa-wrench"></i>
            Mendcare Service
        </a>
        <nav>
            <ul class="nav-menu">
                <li class="search-container">
                    <input type="search" 
                           class="search-box" 
                           placeholder="ค้นหาบริการ..." 
                           oninput="searchServices(this.value)">
                    <i class="fas fa-search search-icon"></i>
                    <div id="searchResults" class="search-results"></div>
                </li>
                <li><a href="notifications.php"><i class="fas fa-bell"></i></a></li>
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
                        <span class="user-name"><?php echo $_SESSION['name']; ?></span>
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

<div id="overlay" class="overlay"></div>
<script>
// Modal and Form Management
function openLoginPopup() {
    document.getElementById('loginPopup').style.display = 'flex';
    document.getElementById('overlay').classList.add('active');
}

function closeLoginPopup() {
    document.getElementById('loginPopup').style.display = 'none';
    document.getElementById('overlay').classList.remove('active');
}

function showLoginForm() {
    document.getElementById('login-form').classList.add('active');
    document.getElementById('register-form').classList.remove('active');
}

function showRegisterForm() {
    document.getElementById('login-form').classList.remove('active');
    document.getElementById('register-form').classList.add('active');
}

// Authentication Handlers
function handleLogin(event) {
    event.preventDefault();
    
    const formData = new FormData();
    formData.append('action', 'login');
    formData.append('email', event.target.email.value);
    formData.append('password', event.target.password.value);

    fetch('sidebar.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: data.message,
                timer: 1500
            }).then(() => {
                window.location.reload();
            });
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'ผิดพลาด',
            text: error.message || 'เกิดข้อผิดพลาดในการเข้าสู่ระบบ'
        });
    });
}

function handleRegister(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    formData.append('action', 'register');

    fetch('sidebar.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'สมัครสมาชิกสำเร็จ!',
                text: data.message,
                timer: 1500
            }).then(() => {
                window.location.reload();
            });
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'ผิดพลาด',
            text: error.message || 'เกิดข้อผิดพลาดในการสมัครสมาชิก'
        });
    });
}

function handleLogout() {
    const formData = new FormData();
    formData.append('action', 'logout');

    fetch('sidebar.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'ออกจากระบบสำเร็จ',
                text: data.message,
                timer: 1500
            }).then(() => {
                window.location.reload();
            });
        }
    });
}

// Search Functionality
function searchServices(searchTerm) {
    const searchResults = document.getElementById('searchResults');
    
    if (!searchTerm) {
        searchResults.style.display = 'none';
        return;
    }

    const formData = new FormData();
    formData.append('action', 'search');
    formData.append('term', searchTerm);

    fetch('sidebar.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        searchResults.innerHTML = '';
        
        if (data.length > 0) {
            data.forEach(service => {
                const div = document.createElement('div');
                div.className = 'search-result-item';
                div.innerHTML = `
                    <h4>${service.name}</h4>
                    <p>${service.description}</p>
                    <small>หมวดหมู่: ${service.category_name}</small>
                    <p class="price">ราคา: ${service.price} บาท</p>
                    <button onclick="addToCart(${service.sub_services_id})" class="add-to-cart-btn">
                        <i class="fas fa-cart-plus"></i> เพิ่มลงตะกร้า
                    </button>
                `;
                searchResults.appendChild(div);
            });
        } else {
            searchResults.innerHTML = '<div class="no-results">ไม่พบบริการที่ค้นหา</div>';
        }
        searchResults.style.display = 'block';
    })
    .catch(error => {
        console.error('Search error:', error);
        searchResults.innerHTML = '<div class="error">เกิดข้อผิดพลาดในการค้นหา</div>';
    });
}

// Cart Management
function addToCart(subServiceId) {
    if (!isUserLoggedIn) {
        Swal.fire({
            title: 'กรุณาเข้าสู่ระบบ',
            text: 'คุณต้องเข้าสู่ระบบก่อนเพิ่มบริการลงตะกร้า',
            icon: 'warning'
        });
        return;
    }

    const formData = new FormData();
    formData.append('action', 'add_to_cart');
    formData.append('sub_service_id', subServiceId);

    fetch('CollectService.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                title: 'เพิ่มลงตะกร้าสำเร็จ',
                icon: 'success',
                timer: 1500
            });
            updateCartCount(data.cart_count);
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'ผิดพลาด',
            text: error.message || 'เกิดข้อผิดพลาดในการเพิ่มบริการลงตะกร้า'
        });
    });
}

function updateCartCount(count) {
    const cartCountElement = document.querySelector('.cart-count');
    if (cartCountElement) {
        cartCountElement.textContent = count;
    }
}

// Sidebar Toggle
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
}

// Initialize Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.querySelector('#login-form form');
    const registerForm = document.querySelector('#register-form form');
    
    if (loginForm) loginForm.addEventListener('submit', handleLogin);
    if (registerForm) registerForm.addEventListener('submit', handleRegister);
    
    document.addEventListener('click', (e) => {
        const searchResults = document.getElementById('searchResults');
        const searchBox = document.querySelector('.search-box');
        if (!searchBox?.contains(e.target) && !searchResults?.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });

    const overlay = document.getElementById('overlay');
    if (overlay) {
        overlay.addEventListener('click', () => {
            closeLoginPopup();
            toggleSidebar();
        });
    }
});


</script>
</body>
</html>
