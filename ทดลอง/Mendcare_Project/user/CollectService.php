<?php
include 'sidebar.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ind.php');
    exit();
}

// ดึงข้อมูลตะกร้า
$user_id = $_SESSION['user_id'];
$sql = "SELECT c.*, s.name, s.price, s.image, s.sub_services_id 
        FROM cart c 
        JOIN sub_services s ON c.sub_service_id = s.sub_services_id 
        WHERE c.user_id = ?";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_items = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// จัดการการลบรายการ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'remove_item') {
        $cart_id = $_POST['cart_id'];
        $delete_sql = "DELETE FROM cart WHERE cart_id = ? AND user_id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("ii", $cart_id, $user_id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
            exit();
        }
    }
}

// ดึงรายการที่ถูกเลือกจากฟอร์ม (ถ้ามี)
$selected_items = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_items'])) {
    $selected_items = $_POST['selected_items'];
}

// คำนวณยอดรวม
$total_result = calculateTotal($cart_items, $selected_items);

// ฟังก์ชันการคำนวณจากบริการที่ถูกเลือก
function calculateTotal($cart_items, $selected_items) {
    $subtotal = 0;
    $service_fee = 50; // ค่าบริการคงที่ 50 บาท
    $total = 0;

    // วนลูปเพื่อคำนวณยอดรวมย่อย
    foreach ($cart_items as $item) {
        if (in_array($item['sub_services_id'], $selected_items)) {
            $subtotal += $item['price'];
        }
    }

    // คำนวณยอดรวมทั้งหมด
    $total = $subtotal + $service_fee;

    // ส่งกลับผลลัพธ์เป็นอาร์เรย์
    return [
        'subtotal' => $subtotal,
        'service_fee' => $service_fee,
        'total' => $total
    ];
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตะกร้าบริการ - Mendcare Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .header-container {
    display: flex;
    align-items: center;
    padding: 20px;
    position: relative;
}

        .cart-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .empty-cart {
            grid-column: 1 / -1;
            text-align: center;
            padding: 50px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .empty-cart i {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 20px;
        }

        .continue-shopping {
            display: inline-block;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }

        .cart-items {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
            gap: 15px;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
        }

        .item-details {
            flex: 1;
        }

        .item-details h3 {
            margin: 0 0 5px 0;
        }

        .price {
            color: #4CAF50;
            font-weight: bold;
        }

        .remove-btn {
        width: 35px;
        height: 35px;
        color: #ff4444;
        background: rgba(255, 68, 68, 0.1);
        border: none;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        }

        .remove-btn:hover {
        background: #ff4444;
        color: white;
        transform: scale(1.1);
        }

        .remove-btn i {
        font-size: 16px;
        }


        .cart-summary {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            height: fit-content;
        }

        .summary-details {
            margin: 20px 0;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
        }

        .total {
            font-weight: bold;
            font-size: 1.2em;
            border-top: 2px solid #eee;
            padding-top: 10px;
            margin-top: 10px;
        }

        .checkout-btn {
            width: 100%;
            padding: 15px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1.1em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .checkout-btn:disabled {
            background: #cccccc;
            cursor: not-allowed;
        }

        .checkout-btn:hover:not(:disabled) {
            background: #45a049;
        }

        .back-button {
            text-decoration: none;
            color: #333;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .item-checkbox {
            margin-right: 10px;
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .cart-container {
                grid-template-columns: 1fr;
            }
        }
        #main-content {
            margin-left: 250px; /* ปรับตามความกว้างของ sidebar ของคุณ */
            padding: 20px;
            min-height: 100vh;
            background: #f5f5f5;
}

        @media (max-width: 768px) {
            #main-content {
            margin-left: 0;
    }
}
        /* Add these styles in your existing <style> section */
        .back-button {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background-color:rgb(205, 219, 255);
            border-radius: 4px;
            text-decoration: none;
            color: #333;
            transition: background-color 0.2s;
            margin-bottom: 20px;
}

        .back-button:hover {
            background-color:rgb(105, 78, 255);
}

        .back-button i {
            font-size: 16px;
}

    </style>
</head>
<body>
    <main class="cart-container">
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h2>ตะกร้าว่างเปล่า</h2>
                <p>กรุณาเลือกบริการที่ต้องการ</p>
                <a href="ind.php" class="continue-shopping">เลือกซื้อบริการ</a>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <h2>รายการบริการที่เลือก</h2>
                <form method="POST" action="">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item" data-id="<?php echo $item['cart_id']; ?>">
                            <input type="checkbox" 
                                class="item-checkbox" 
                                name="selected_items[]" 
                                value="<?php echo $item['sub_services_id']; ?>"
                                <?php echo in_array($item['sub_services_id'], $selected_items) ? 'checked' : ''; ?>>
                            <img src="image/<?php echo htmlspecialchars($item['image']); ?>" 
                                alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p class="price">฿<?php echo number_format($item['price'], 2); ?></p>
                            </div>
                            <button class="remove-btn" onclick="removeItem(<?php echo $item['cart_id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit" style="display: none;" id="submit-form"></button>
                </form>
            </div>

            <div class="cart-summary">
                <h2>สรุปรายการ</h2>
                <div class="summary-details">
                    <div class="summary-row">
                        <span>ราคารวม</span>
                        <span id="subtotal">฿<?php echo number_format($total_result['subtotal'], 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>ค่าบริการ</span>
                        <span id="service-fee">฿<?php echo number_format($total_result['service_fee'], 2); ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>ยอดรวมทั้งหมด</span>
                        <span id="total">฿<?php echo number_format($total_result['total'], 2); ?></span>
                    </div>
                </div>
                <button class="checkout-btn" onclick="proceedToCheckout()" <?php echo empty($selected_items) ? 'disabled' : ''; ?> id="checkout-btn">
                    ดำเนินการชำระเงิน <i class="fas fa-arrow-right"></i>
                </button>
            </div>
            <div class="header-container">
                <a href="javascript:history.back()" class="back-button">
                    <i class="fas fa-arrow-left"></i>
                    <span>ย้อนกลับ</span>
                </a>
            </div>
        <?php endif; ?>
    </main>

    <script>
        // จัดการการลบรายการ
        function removeItem(cartId) {
            Swal.fire({
                title: 'ยืนยันการลบ',
                text: "คุณต้องการลบรายการนี้ใช่หรือไม่?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ใช่, ลบรายการ',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', 'remove_item');
                    formData.append('cart_id', cartId);

                    fetch('CollectService.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            document.querySelector(`.cart-item[data-id="${cartId}"]`).remove();
                            if (document.querySelectorAll('.cart-item').length === 0) {
                                location.reload();
                            }
                        }
                    });
                }
            });
        }

        // ดำเนินการชำระเงิน
        function proceedToCheckout() {
            const selectedItems = [];
            document.querySelectorAll('.item-checkbox:checked').forEach(checkbox => {
                selectedItems.push(checkbox.value); // ใช้ value ของ checkbox เป็น sub_service_id
            });

            if (selectedItems.length > 0) {
                window.location.href = `insertdata.php?items=${selectedItems.join(',')}`;
            }
        }

        // ส่งฟอร์มเมื่อผู้ใช้ติ้ก checkbox
        document.querySelectorAll('.item-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                document.getElementById('submit-form').click();
            });
        });
    </script>
</body>
</html>