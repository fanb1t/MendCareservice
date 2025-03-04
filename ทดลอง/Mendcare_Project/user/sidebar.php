<?php
session_start();
require_once 'connect.php';

// Database Functions
function getCartItems($conn, $user_id) {
    $cart_items = [];
    $cart_sql = "SELECT c.*, ss.name as service_name, ss.price 
                 FROM cart c 
                 JOIN sub_services ss ON c.sub_service_id = ss.sub_services_id 
                 WHERE c.user_id = ?";
    
    $stmt = $conn->prepare($cart_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while($row = $result->fetch_assoc()) {
        $cart_items[] = [
            'cart_id' => $row['cart_id'],
            'sub_service_id' => $row['sub_service_id'],
            'service_name' => $row['service_name'],
            'price' => $row['price']
        ];
    }
    return $cart_items;
}

// Authentication Functions
function handleLogin($conn, $email, $password) {
    $sql = "SELECT user_id, name, email, profile_picture FROM users WHERE email=? AND password=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        setUserSession($user);
        
        if (isAdmin($user["email"])) {
            $_SESSION["is_admin"] = true;
            return [
                'status' => 'success',
                'message' => 'ยินดีต้อนรับ Admin ' . $user["name"],
                'redirect' => 'Addmin/ad_service_request.php',
                'user' => $user
            ];
        }
        return [
            'status' => 'success',
            'message' => 'ยินดีต้อนรับคุณ ' . $user["name"],
            'user' => $user
        ];
    }
    return [
        'status' => 'error',
        'message' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง'
    ];
}

function handleRegister($conn, $userData) {
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $userData['name'], $userData['email'], $userData['password'], $userData['phone']);

    if ($stmt->execute()) {
        setUserSession([
            'user_id' => $conn->insert_id,
            'name' => $userData['name'],
            'email' => $userData['email']
        ]);
        return ['status' => 'success', 'message' => 'สมัครสมาชิกสำเร็จ!'];
    }
    
    if ($conn->errno == 1062) {
        return ['status' => 'error', 'message' => 'อีเมลนี้ถูกใช้งานแล้ว'];
    }
    return ['status' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $stmt->error];
}

// Session Management Functions
function setUserSession($user) {
    $_SESSION["user_id"] = $user["user_id"];
    $_SESSION["name"] = $user["name"];
    $_SESSION["email"] = $user["email"];
    if (isset($user["profile_picture"])) {
        $_SESSION["profile_picture"] = $user["profile_picture"];
    }
}

function isAdmin($email) {
    return strpos($email, "@Mendcare.ac.th") !== false;
}

// Search Function
function searchServices($conn, $searchTerm) {
    $searchTerm = '%' . filter_var($searchTerm) . '%';
    
    $sql = "SELECT ss.name, sc.name as category_name, sc.page_url 
            FROM sub_services ss 
            JOIN service_categories sc ON ss.service_category_id = sc.service_category_id 
            WHERE ss.name LIKE ? OR sc.name LIKE ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Request Handler
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'login':
            echo json_encode(handleLogin($conn, trim($_POST["email"]), trim($_POST["password"])));
            break;
            
        case 'register':
            echo json_encode(handleRegister($conn, $_POST));
            break;
            
        case 'logout':
            session_destroy();
            echo json_encode(['status' => 'success', 'message' => 'ออกจากระบบสำเร็จ']);
            break;
            
        case 'search':
            if(empty($_POST['term'])) {
                echo json_encode([]);
                break;
            }
            echo json_encode(searchServices($conn, $_POST['term']));
            break;
    }
    exit();
}

// Initialize cart items
$cart_items = [];
$cart_count = 0;
if(isset($_SESSION['user_id'])) {
    $cart_items = getCartItems($conn, $_SESSION['user_id']);
    $cart_count = count($cart_items);
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

    <link rel="stylesheet" href="ind.css">
</head>
<body>
<div id="overlay" class="overlay"></div>
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
        <li><a href="ind.php"><i class="fas fa-home"></i> หน้าแรก</a></li>
        <?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
            <li><a href="Addmin/ad_service_request.php"><i class="fas fa-tasks"></i> จัดการคำขอบริการ</a></li>
        <?php endif; ?>
        <li><a href="success.php"><i class="fas fa-calendar-alt"></i> จองบริการ</a></li>
        <li><a href="status.php"><i class="fas fa-map-marker-alt"></i> ติดตามงาน</a></li>
        <li><a href="service_his.php"><i class="fas fa-history"></i> ประวัติการใช้บริการ</a></li>
        <li><a href="review.php"><i class="fas fa-star"></i> รีวิวจากลูกค้า</a></li>
        <li><a href="contact.php"><i class="fas fa-phone"></i> ติดต่อเรา</a></li>
    </ul>
</div>

<!-- Header -->
<header>
    <div class="header-container">
        <div class="nav-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </div>
        <a href="ind.php" class="logo">
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
                    <li class="user-profile">
                        <a href="profile.php" class="profile-link">
                            <img src="<?php echo $_SESSION['profile_picture'] ? 'imageeeeee/' . $_SESSION['profile_picture'] : 'assets/default-avatar.png'; ?>" 
                                 alt="Profile" 
                                 class="profile-image">
                            <span class="user-name"><?php echo $_SESSION['name']; ?></span>
                        </a>
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

// Update handleLogin function
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
            closeLoginPopup();
            
            await Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: data.message,
                timer: 3000,
                showConfirmButton: false,
                timerProgressBar: true,
                customClass: {
                    container: 'my-swal'
                }
            });

            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                document.querySelector('.auth-links').innerHTML = `
                    <span class="user-name">${data.user.name}</span>
                    <a href="javascript:void(0);" onclick="handleLogout()" class="login-btn">
                        <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
                    </a>
                `;
            }
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
</body>
</html>