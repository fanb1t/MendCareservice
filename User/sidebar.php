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
    
    $sql = "SELECT ss.name, sc.name as category_name, sc.page_url 
            FROM sub_services ss 
            JOIN service_categories sc ON ss.service_category_id = sc.service_category_id 
            WHERE ss.name LIKE ? OR sc.name LIKE ?";
    
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
    <link rel="stylesheet" href="ind.css">
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



<script>
const isUserLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
// Modal and Form Management
const openLoginPopup = () => {
    const loginPopup = document.getElementById('loginPopup');
    const overlay = document.getElementById('overlay');
    loginPopup.style.display = 'flex';
    loginPopup.style.zIndex = '1000000';
    overlay.classList.add('active');
    overlay.style.zIndex = '999999';
};


const closeLoginPopup = () => {
    const loginPopup = document.getElementById('loginPopup');
    const overlay = document.getElementById('overlay');
    loginPopup.style.display = 'none';
    overlay.classList.remove('active');
};

const showLoginForm = () => {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    loginForm.classList.add('active');
    registerForm.classList.remove('active');
};

const showRegisterForm = () => {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    loginForm.classList.remove('active');
    registerForm.classList.add('active');
};

// Authentication Handlers
const handleLogin = async (event) => {
    event.preventDefault();
    
    const formData = new FormData();
    formData.append('action', 'login');
    formData.append('email', event.target.email.value);
    formData.append('password', event.target.password.value);

    try {
        const response = await fetch('sidebar.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.status === 'success') {
            await Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: data.message,
                timer: 1500,
                customClass: {
                    container: 'my-swal'
                }
            });
            window.location.reload();
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'ผิดพลาด',
            text: error.message || 'เกิดข้อผิดพลาดในการเข้าสู่ระบบ',
            customClass: {
                container: 'my-swal'
            }
        });
    }
};

const handleRegister = async (event) => {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    formData.append('action', 'register');

    try {
        const response = await fetch('sidebar.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.status === 'success') {
            await Swal.fire({
                icon: 'success',
                title: 'สมัครสมาชิกสำเร็จ!',
                text: data.message,
                timer: 1500,
                customClass: {
                    container: 'my-swal'
                }
            });
            window.location.reload();
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'ผิดพลาด',
            text: error.message || 'เกิดข้อผิดพลาดในการสมัครสมาชิก',
            customClass: {
                container: 'my-swal'
            }
        });
    }
};

const handleLogout = async () => {
    const formData = new FormData();
    formData.append('action', 'logout');

    try {
        const response = await fetch('sidebar.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.status === 'success') {
            await Swal.fire({
                icon: 'success',
                title: 'ออกจากระบบสำเร็จ',
                text: data.message,
                timer: 1500,
                customClass: {
                    container: 'my-swal'
                }
            });
            window.location.reload();
        }
    } catch (error) {
        console.error('Logout error:', error);
    }
};


// Search Functionality
const searchServices = async (searchTerm) => {
    const searchResults = document.getElementById('searchResults');
    
    if (!searchTerm) {
        searchResults.style.display = 'none';
        return;
    }

    try {
        const formData = new FormData();
        formData.append('action', 'search');
        formData.append('term', searchTerm);

        const response = await fetch('sidebar.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const data = await response.json();
        
        searchResults.innerHTML = '';
        
        if (data && data.length > 0) {
            data.forEach(service => {
                const div = document.createElement('div');
                div.className = 'search-result-item';
                div.onclick = () => {
                    window.location.href = service.page_url;
                };
                div.innerHTML = `
                    <div>
                        <h4>${service.name}</h4>
                        <small>หมวดหมู่: ${service.category_name}</small>
                    </div>
                    <i class="fas fa-arrow-right"></i>
                `;
                searchResults.appendChild(div);
            });
            searchResults.style.display = 'block';
        } else {
            searchResults.innerHTML = '<div class="no-results">ไม่พบบริการที่ค้นหา</div>';
            searchResults.style.display = 'block';
        }
    } catch (error) {
        console.error('Search error:', error);
        searchResults.innerHTML = '<div class="error">กำลังค้นหา...</div>';
        searchResults.style.display = 'block';
    }
};



// Cart Management
const addToCart = async (subServiceId) => {
    if (!isUserLoggedIn) {
        await Swal.fire({
            title: 'กรุณาเข้าสู่ระบบ',
            text: 'คุณต้องเข้าสู่ระบบก่อนเพิ่มบริการลงตะกร้า',
            icon: 'warning'
        });
        return;
    }

    const formData = new FormData();
    formData.append('action', 'add_to_cart');
    formData.append('sub_service_id', subServiceId);

    try {
        const response = await fetch('CollectService.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.status === 'success') {
            await Swal.fire({
                title: 'เพิ่มลงตะกร้าสำเร็จ',
                icon: 'success',
                timer: 1500
            });
            updateCartCount(data.cart_count);
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'ผิดพลาด',
            text: error.message || 'เกิดข้อผิดพลาดในการเพิ่มบริการลงตะกร้า'
        });
    }
};

const updateCartCount = (count) => {
    const cartCountElement = document.querySelector('.cart-count');
    if (cartCountElement) {
        cartCountElement.textContent = count;
    }
};

// Sidebar Toggle
const toggleSidebar = () => {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    if (sidebar && overlay) {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }
};


// Initialize Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.querySelector('#login-form form');
    const registerForm = document.querySelector('#register-form form');
    const overlay = document.getElementById('overlay');
    
    if (loginForm) loginForm.addEventListener('submit', handleLogin);
    if (registerForm) registerForm.addEventListener('submit', handleRegister);
    
    document.addEventListener('click', (e) => {
        const searchResults = document.getElementById('searchResults');
        const searchBox = document.querySelector('.search-box');
        if (!searchBox?.contains(e.target) && !searchResults?.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });

    if (overlay) {
        overlay.addEventListener('click', () => {
            closeLoginPopup();
            toggleSidebar();
        });
    }
});



</script>
<div id="overlay" class="overlay"></div>
</body>
</html>