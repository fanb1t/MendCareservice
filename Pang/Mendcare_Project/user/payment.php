<?php
ob_start()
include 'sidebar.php';
require_once 'connect.php';

// ตรวจสอบว่ามี request_id ถูกส่งมาหรือไม่
if (!isset($_GET['request_id'])) {
    die("Request ID is missing.");
}

$request_id = $_GET['request_id'];

// ตรวจสอบสิทธิ์ผู้ใช้ (สมมติว่าคุณเก็บ user_id ใน session)
if (!isset($_SESSION['user_id'])) {
    die("คุณยังไม่ได้เข้าสู่ระบบ");
}

$user_id = $_SESSION['user_id'];

// ตรวจสอบว่า request_id นี้เป็นของผู้ใช้ที่กำลังใช้งานอยู่หรือไม่
$sql = "SELECT r.request_id 
        FROM requests r 
        WHERE r.request_id = ? AND r.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $request_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("คุณไม่มีสิทธิ์เข้าถึงคำขอนี้");
}

// ดึงข้อมูลบริการ
$sql = "SELECT r.request_id, 
               GROUP_CONCAT(s.name) as service_names,
               GROUP_CONCAT(s.price) as service_prices,
               SUM(s.price) as total_amount
        FROM requests r 
        JOIN request_services rs ON r.request_id = rs.request_id
        JOIN sub_services s ON rs.sub_service_id = s.sub_services_id 
        WHERE r.request_id = ?
        GROUP BY r.request_id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("ไม่พบข้อมูลคำขอ");
}

$service_data = $result->fetch_assoc();
$service_names = explode(',', $service_data['service_names']);
$service_prices = explode(',', $service_data['service_prices']);
$total_amount = $service_data['total_amount'];

// จัดการอัปโหลดไฟล์
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_id = $_POST['request_id'];

    // ตรวจสอบว่ามีไฟล์ถูกอัปโหลดหรือไม่
    if (!isset($_FILES["payment_slip"]) || $_FILES["payment_slip"]["error"] != UPLOAD_ERR_OK) {
        die("เกิดข้อผิดพลาดในการอัปโหลดไฟล์");
    }

    // ตรวจสอบประเภทไฟล์
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $file_extension = strtolower(pathinfo($_FILES["payment_slip"]["name"], PATHINFO_EXTENSION));

    if (!in_array($file_extension, $allowed_extensions)) {
        die("ประเภทไฟล์ไม่ถูกต้อง (อนุญาตเฉพาะ JPG, JPEG, PNG, GIF)");
    }

    // ตรวจสอบขนาดไฟล์ (สูงสุด 5MB)
    $max_file_size = 5 * 1024 * 1024; // 5MB
    if ($_FILES["payment_slip"]["size"] > $max_file_size) {
        die("ไฟล์มีขนาดใหญ่เกินไป (สูงสุด 5MB)");
    }

    // สร้างโฟลเดอร์ถ้ายังไม่มี
    $target_dir = "image/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true); // สร้างโฟลเดอร์ด้วยสิทธิ์ 0755
    }

    // สร้างชื่อไฟล์ใหม่
    $new_filename = "slip_" . $request_id . "_" . uniqid() . "." . $file_extension;
    $target_file = $target_dir . $new_filename;

    // ย้ายไฟล์ไปยังโฟลเดอร์ปลายทาง
    if (move_uploaded_file($_FILES["payment_slip"]["tmp_name"], $target_file)) {
        // อัปเดตฐานข้อมูล
        $sql = "UPDATE requests SET payment_slip = ?, payment_status = 'paid' WHERE request_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $new_filename, $request_id);

        if ($stmt->execute()) {
            header("Location: status.php");
            exit();
        } else {
            die("เกิดข้อผิดพลาดในการอัปเดตฐานข้อมูล: " . $stmt->error);
        }
    } else {
        die("เกิดข้อผิดพลาดในการย้ายไฟล์");
    }
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ชำระเงิน - Mendcare Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .payment-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }

        .payment-summary {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 30px;
        }

        .service-item {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .total-amount {
            margin-top: 20px;
            padding: 15px;
            text-align: right;
            font-size: 1.2em;
            background: #f8f9fa;
            border-radius: 5px;
            font-weight: bold;
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 30px;
        }

        .payment-option {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            transition: transform 0.2s;
        }

        .payment-option:hover {
            transform: translateY(-2px);
            background: #f8f9fa;
        }

        .payment-option img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }

        .payment-details {
            margin-top: 30px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: none;
        }

        .qr-container {
            text-align: center;
            padding: 20px;
        }

        .bank-details {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            margin-top: 15px;
        }
        .upload-slip-form {
            margin-top: 30px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .upload-slip-form input[type="file"] {
            margin: 15px 0;
            padding: 10px;
            width: 100%;
        }

        .btn-primary {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-primary:hover {
            background: #0056b3;
        }
        </style>
</head>
<body>
    <div class="payment-container">
        <h1>ชำระเงิน</h1>
        
        <div class="payment-summary">
            <h2>สรุปรายการ</h2>
            <div class="selected-services">
                <?php for ($i = 0; $i < count($service_names); $i++): ?>
                    <div class="service-item">
                        <span><?php echo htmlspecialchars($service_names[$i]); ?></span>
                        <span>฿<?php echo number_format($service_prices[$i], 2); ?></span>
                    </div>
                <?php endfor; ?>
            </div>
            <div class="total-amount">
                ยอดรวมทั้งหมด: ฿<?php echo number_format($total_amount, 2); ?>
            </div>
        </div>

        <div class="payment-methods">
            <div class="payment-option" onclick="showPaymentDetails('promptpay')">
                <img src="image/scb.png" alt="PromptPay">
                <span>พร้อมเพย์</span>
            </div>
            <div class="payment-option" onclick="showPaymentDetails('bank')">
                <img src="image/tb.jpg" alt="Bank Transfer">
                <span>บัญชีธนาคาร</span>
            </div>
        </div>

        <div id="payment-details" class="payment-details"></div>
        
        <div class="upload-slip-form">
            <h3>อัพโหลดสลิปการโอนเงิน</h3>
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="request_id" value="<?php echo $request_id; ?>">
                <input type="file" name="payment_slip" accept="image/*" required>
                <button type="submit" class="btn btn-primary">บันทึกสลิปการโอนเงิน</button>
            </form>
        </div>
    </div>

    <script>
    function showPaymentDetails(method) {
        const detailsDiv = document.getElementById('payment-details');
        detailsDiv.style.display = 'block';
        
        if (method === 'promptpay') {
            detailsDiv.innerHTML = `
                <h3>ชำระผ่านพร้อมเพย์</h3>
                <div class="qr-container">
                    <img src="image/สื่อ.jpg" alt="PromptPay QR" width="200" height="400">
                    <p>สแกน QR Code เพื่อชำระเงิน</p>
                </div>
            `;
        } else if (method === 'bank') {
            detailsDiv.innerHTML = `
                <h3>ชำระผ่านบัญชีธนาคาร</h3>
                <div class="bank-details">
                    <p><strong>ธนาคาร:</strong> กสิกรไทย</p>
                    <p><strong>ชื่อบัญชี:</strong> บริษัท เมนด์แคร์ เซอร์วิส จำกัด</p>
                    <p><strong>เลขที่บัญชี:</strong> xxx-x-xxxxx-x</p>
                </div>
            `;
        }
    }
    </script>
</body>
</html>