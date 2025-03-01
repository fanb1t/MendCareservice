<?php 
include 'sidebar.php';

// Function to display alerts
function displayAlert($message, $type = 'success') {
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type
    ];
}

// ดึงข้อมูลบริการจากฐานข้อมูล
$sql = "SELECT * FROM sub_services WHERE service_category_id = '3'";
$result = $conn->query($sql);
$services = [];
while($row = $result->fetch_assoc()) {
    $services[] = $row;
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        displayAlert('กรุณาเข้าสู่ระบบก่อนเพิ่มสินค้าลงตะกร้า', 'error');
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }

    $sub_service_id = $_POST['sub_service_id'];
    $user_id = $_SESSION['user_id'];

    // ตรวจสอบว่ามีในตะกร้าแล้วหรือไม่
    $check_sql = "SELECT cart_id FROM cart WHERE user_id = ? AND sub_service_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ii", $user_id, $sub_service_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        displayAlert('บริการนี้อยู่ในตะกร้าแล้ว', 'error');
    } else {
        // เพิ่มลงตะกร้า
        $insert_sql = "INSERT INTO cart (user_id, sub_service_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("ii", $user_id, $sub_service_id);

        if ($stmt->execute()) {
            displayAlert('เพิ่มลงตะกร้าสำเร็จ');
        } else {
            displayAlert('เกิดข้อผิดพลาด: ' . $conn->error, 'error');
        }
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บริการซ่อมแอร์ - Mendcare Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="air.css">
</head>
<style>
    .alert {
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    border-radius: 4px;
    text-align: center;
    width: 80%;
    margin: 20px auto;
}

.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.alert-error {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}
</style>
<body>
    <main>
        <?php if (isset($_SESSION['alert'])): ?>
            <div class="alert alert-<?php echo $_SESSION['alert']['type']; ?>">
                <?php 
                echo $_SESSION['alert']['message'];
                unset($_SESSION['alert']);
                ?>
            </div>
        <?php endif; ?>

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
                        <div class="grid-item">
                            <img src="image/<?php echo $service['image']; ?>" alt="<?php echo $service['name']; ?>">
                            <h3><?php echo $service['name']; ?></h3>
                            <p>เริ่มต้น <?php echo number_format($service['price']); ?> บาท</p>
                            <div class="button-group">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="sub_service_id" value="<?php echo $service['sub_services_id']; ?>">
                                    <button type="submit" name="add_to_cart" class="cart-btn">
                                        <i class="fas fa-cart-plus"></i> เพิ่มลงตะกร้า
                                    </button>
                                </form>
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
</body>
</html>