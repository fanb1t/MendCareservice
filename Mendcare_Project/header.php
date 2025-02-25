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
?>
<head>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
// ฟังก์ชันสำหรับ Modal
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

// ฟังก์ชันจัดการ Login
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

// ฟังก์ชันจัดการ Register
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
                location.reload(); // รีเฟรชหน้าเว็บก่อน
                setTimeout(() => {
                    window.location.href = 'ind.php'; // หลังจากรีเฟรชเสร็จให้ไปที่หน้า ind.php
                }, 100);
            });
        }
    });
}

</script>
