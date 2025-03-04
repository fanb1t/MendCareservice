<?php
session_start();
require_once 'connect.php';

$request_id = $_GET['request_id'];

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
$service_data = $result->fetch_assoc();

$service_names = explode(',', $service_data['service_names']);
$service_prices = explode(',', $service_data['service_prices']);
$total_amount = $service_data['total_amount'];
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
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="payment-container">
        <h1>ชำระเงิน</h1>
        
        <div class="payment-summary">
            <h2>สรุปรายการ</h2>
            <div class="selected-services">
                <?php for($i = 0; $i < count($service_names); $i++): ?>
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
                <img src="images/promptpay.png" alt="PromptPay">
                <span>พร้อมเพย์</span>
            </div>
            <div class="payment-option" onclick="showPaymentDetails('bank')">
                <img src="images/bank.png" alt="Bank Transfer">
                <span>บัญชีธนาคาร</span>
            </div>
        </div>

        <div id="payment-details" class="payment-details"></div>
    </div>

    <script>
    function showPaymentDetails(method) {
        const detailsDiv = document.getElementById('payment-details');
        detailsDiv.style.display = 'block';
        
        if(method === 'promptpay') {
            detailsDiv.innerHTML = `
                <h3>ชำระผ่านพร้อมเพย์</h3>
                <div class="qr-container">
                    <img src="generate_qr.php?amount=<?php echo $total_amount; ?>" alt="PromptPay QR">
                    <p>สแกน QR Code เพื่อชำระเงิน</p>
                </div>
            `;
        } else if(method === 'bank') {
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
