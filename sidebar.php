<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'connect.php';

// Handle Search
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'search') {
    $searchTerm = '%' . $_POST['term'] . '%';
    
    $sql = "SELECT * FROM services WHERE name LIKE ? OR description LIKE ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $services = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($services);
    exit();
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

function searchServices(searchTerm) {
    const searchResults = document.getElementById('searchResults');
    
    if (searchTerm.length > 0) {
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
                    `;
                    div.onclick = () => {
                        window.location.href = `service-detail.php?id=${service.id}`;
                    };
                    searchResults.appendChild(div);
                });
                searchResults.style.display = 'block';
            } else {
                searchResults.innerHTML = '<div class="no-results">ไม่พบบริการที่ค้นหา</div>';
                searchResults.style.display = 'block';
            }
        });
    } else {
        searchResults.style.display = 'none';
    }
}

// ปิดผลการค้นหาเมื่อคลิกที่อื่น
document.addEventListener('click', (e) => {
    const searchResults = document.getElementById('searchResults');
    const searchBox = document.querySelector('.search-box');
    
    if (!searchBox.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.style.display = 'none';
    }
});
function handleLogin(event) {
    event.preventDefault();
    
    const email = document.querySelector('#login-form input[name="email"]').value;
    const password = document.querySelector('#login-form input[name="password"]').value;
    
    const formData = new FormData();
    formData.append('action', 'login');
    formData.append('email', email);
    formData.append('password', password);

    fetch('sidebar.php', {
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
</script>

</body>
</html>
