<?php
session_start();
require_once 'connect.php';

// เพิ่มการตรวจสอบการเชื่อมต่อฐานข้อมูล
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check login status
if (!isset($_SESSION['user_id'])) {
    header('Location: ind.php');
    exit();
}

// ดึงข้อมูลตะกร้า
$user_id = $_SESSION['user_id'];
$sql = "SELECT r.*, s.name, s.price, s.image 
        FROM requests r 
        JOIN sub_services s ON r.sub_service_id = s.sub_services_id 
        WHERE r.user_id = ? AND r.status = 'pending'";

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
        $request_id = $_POST['request_id'];
        $delete_sql = "DELETE FROM requests WHERE request_id = ? AND user_id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("ii", $request_id, $user_id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
            exit();
        }
    }
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

    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

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
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item" data-id="<?php echo $item['request_id']; ?>">
                            <input type="checkbox" class="item-checkbox" 
                                   data-price="<?php echo $item['price']; ?>"
                                   onchange="updateTotal()">
                            <img src="image/<?php echo htmlspecialchars($item['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p class="price">฿<?php echo number_format($item['price'], 2); ?></p>
                            </div>
                            <button class="remove-btn" onclick="removeItem(<?php echo $item['request_id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <h2>สรุปรายการ</h2>
                    <div class="summary-details">
                        <div class="summary-row">
                            <span>ราคารวม</span>
                            <span id="subtotal">฿0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>ค่าบริการ</span>
                            <span id="service-fee">฿0.00</span>
                        </div>
                        <div class="summary-row total">
                            <span>ยอดรวมทั้งหมด</span>
                            <span id="total">฿0.00</span>
                        </div>
                    </div>
                    <button class="checkout-btn" onclick="proceedToCheckout()" disabled id="checkout-btn">
                        ดำเนินการชำระเงิน <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        function removeItem(requestId) {
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
                    formData.append('request_id', requestId);

                    fetch('CollectService.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            document.querySelector(`.cart-item[data-id="${requestId}"]`).remove();
                            if (document.querySelectorAll('.cart-item').length === 0) {
                                location.reload();
                            }
                            updateTotal();
                        }
                    });
                }
            });
        }

        function updateTotal() {
            const checkboxes = document.querySelectorAll('.item-checkbox');
            let subtotal = 0;
            const serviceFee = 50;
            let hasSelectedItems = false;

            checkboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    subtotal += parseFloat(checkbox.dataset.price);
                    hasSelectedItems = true;
                }
            });

            const total = subtotal + (hasSelectedItems ? serviceFee : 0);

            // Update display
            document.getElementById('subtotal').textContent = `฿${subtotal.toFixed(2)}`;
            document.getElementById('service-fee').textContent = hasSelectedItems ? `฿${serviceFee.toFixed(2)}` : '฿0.00';
            document.getElementById('total').textContent = `฿${total.toFixed(2)}`;

            // Enable/disable checkout button
            document.getElementById('checkout-btn').disabled = !hasSelectedItems;
        }

        function proceedToCheckout() {
            const selectedItems = [];
            document.querySelectorAll('.item-checkbox:checked').forEach(checkbox => {
                selectedItems.push(checkbox.closest('.cart-item').dataset.id);
            });

            if (selectedItems.length > 0) {
                window.location.href = `checkout.php?items=${selectedItems.join(',')}`;
            }
        }
    </script>
</body>
</html>
