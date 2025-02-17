<?php
session_start();

// Calculate total amount from selected services
$total_amount = 0;
if (!empty($_SESSION["selected_services"])) {
    // Assuming each service costs 500 baht for this example
    $total_amount = count($_SESSION["selected_services"]) * 500;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ชำระเงิน - Mendcare Service</title>
    <link rel="stylesheet" href="ind.css">
    <link rel="stylesheet" href="payment.css">
</head>
<body>
    <style>
        .payment-summary {
    background: #f5f5f5;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.service-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #ddd;
}

.total-amount {
    margin-top: 20px;
    text-align: right;
    font-size: 1.2em;
}

.payment-methods {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
    gap: 15px;
    transition: transform 0.2s;
}

.payment-option:hover {
    transform: translateY(-2px);
}

.payment-option img {
    width: 40px;
    height: 40px;
    object-fit: contain;
}

.payment-details {
    margin-top: 30px;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
}

    </style>
    <?php include 'sidebar.php'; ?>

    <div id="main-content">
        <header>
            <div class="header-container">
                <div class="nav-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </div>
                <a href="/" class="logo">
                    <i class="fas fa-wrench"></i>
                    Mendcare Service
                </a>
            </div>
        </header>

        <div class="container">
            <h1>ชำระเงิน</h1>
            <div class="payment-summary">
                <h2>สรุปรายการ</h2>
                <div class="selected-services">
                    <?php foreach ($_SESSION["selected_services"] as $service): ?>
                        <div class="service-item">
                            <span><?php echo $service; ?></span>
                            <span>฿500</span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="total-amount">
                    <strong>ยอดรวมทั้งหมด: ฿<?php echo $total_amount; ?></strong>
                </div>
            </div>

            <div class="payment-methods">
                <h2>เลือกวิธีการชำระเงิน</h2>
                
                <div class="payment-option" onclick="showPaymentDetails('credit')">
                    <img src="credit-card-icon.png" alt="Credit Card">
                    <span>บัตรเครดิต/เดบิต</span>
                </div>

                <div class="payment-option" onclick="showPaymentDetails('promptpay')">
                    <img src="promptpay-icon.png" alt="PromptPay">
                    <span>พร้อมเพย์</span>
                </div>

                <div class="payment-option" onclick="showPaymentDetails('truemoney')">
                    <img src="truemoney-icon.png" alt="TrueMoney">
                    <span>ทรูมันนี่ วอลเล็ท</span>
                </div>
            </div>

            <div id="payment-details" class="payment-details" style="display: none;">
                <!-- Payment details will be loaded here -->
            </div>
        </div>
    </div>

    <?php
require_once 'vendor/autoload.php';
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

// Your PromptPay ID (Tax ID or Phone number)
$promptpay_id = "0899999999";
$amount = isset($_GET['amount']) ? $_GET['amount'] : 0;

// Generate PromptPay payload
function generatePayload($id, $amount = 0) {
    // PromptPay payload format implementation
    // This is a simplified version - you'll need to implement the actual PromptPay format
    $payload = "00020101021229370016A000000677010111"
             . strlen($id) . $id
             . "5802TH53037646304";
    
    if ($amount > 0) {
        $payload .= str_pad(number_format($amount, 2, '.', ''), 13, '0', STR_PAD_LEFT);
    }
    
    return $payload;
}

$payload = generatePayload($promptpay_id, $amount);
$qrcode = new QRCode(new QROptions([
    'outputType' => QRCode::OUTPUT_IMAGE_PNG,
    'eccLevel' => QRCode::ECC_L
]));

header('Content-Type: image/png');
echo $qrcode->render($payload);
?>
    <script>
    
        function showPaymentDetails(method) {
    const detailsDiv = document.getElementById('payment-details');
    detailsDiv.style.display = 'block';
    
    switch(method) {
        case 'credit':
            detailsDiv.innerHTML = `
                <h3>ชำระผ่านบัตรเครดิต/เดบิต</h3>
                <form id="credit-card-form">
                    <div class="form-group">
                        <label>หมายเลขบัตร</label>
                        <input type="text" placeholder="xxxx-xxxx-xxxx-xxxx" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>วันหมดอายุ</label>
                            <input type="text" placeholder="MM/YY" required>
                        </div>
                        <div class="form-group">
                            <label>CVV</label>
                            <input type="text" placeholder="xxx" required>
                        </div>
                    </div>
                    <button type="submit">ชำระเงิน</button>
                </form>
            `;
            break;
            
        case 'promptpay':
            detailsDiv.innerHTML = `
                <h3>ชำระผ่านพร้อมเพย์</h3>
                <div class="qr-container">
                    <img src="generate_qr.php" alt="PromptPay QR Code">
                    <p>สแกนเพื่อชำระเงิน</p>
                </div>
            `;
            break;
            
        case 'truemoney':
            detailsDiv.innerHTML = `
                <h3>ชำระผ่านทรูมันนี่ วอลเล็ท</h3>
                <form id="truemoney-form">
                    <div class="form-group">
                        <label>หมายเลขโทรศัพท์</label>
                        <input type="tel" placeholder="0xxxxxxxxx" required>
                    </div>
                    <button type="submit">ดำเนินการต่อ</button>
                </form>
            `;
            break;
    }
}

    </script>
</body>
</html>
