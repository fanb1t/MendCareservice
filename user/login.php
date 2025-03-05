<?php
require_once 'connect.php';

// นับจำนวนและดึงรายละเอียดบริการในตะกร้า
$cart_items = [];
$cart_count = 0;
if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
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
    
    $cart_count = count($cart_items);
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
        
        // Check if user is admin
        if (strpos($user["email"], "@Mendcare.ac.th") !== false) {
            $_SESSION["is_admin"] = true;
            echo json_encode([
                'status' => 'success',
                'message' => 'ยินดีต้อนรับ Admin ' . $user["name"],
                'redirect' => '../Addmin/ad_service_request.php',
                'user' => $user
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'message' => 'ยินดีต้อนรับคุณ ' . $user["name"],
                'user' => $user
            ]);
        }
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