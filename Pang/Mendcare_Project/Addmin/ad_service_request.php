<?php
session_start();
require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $request_id = $_POST['request_id'];
        
        if ($_POST['action'] === 'confirm' && !empty($_POST['technician_id'])) {
            $technician_id = $_POST['technician_id'];
            $stmt = $conn->prepare("UPDATE requests SET status = 'Confirm service request', technician_id = ? WHERE request_id = ?");
            $stmt->bind_param("ii", $technician_id, $request_id);
            $stmt->execute();
        } elseif ($_POST['action'] === 'reject') {
            $stmt = $conn->prepare("UPDATE requests SET status = 'Reject service request', notes = CONCAT(notes, ' - กรุณาเลือกวันและเวลาใหม่') WHERE request_id = ?");
            $stmt->bind_param("i", $request_id);
            $stmt->execute();
        }
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

$sql = "SELECT r.*, u.name_last, u.phone, u.address, 
        GROUP_CONCAT(s.name) as service_names,
        GROUP_CONCAT(s.price) as service_prices,
        t.name as tech_name, t.phone as tech_phone
        FROM requests r
        JOIN users u ON r.user_id = u.user_id
        JOIN request_services rs ON r.request_id = rs.request_id
        JOIN sub_services s ON rs.sub_service_id = s.sub_services_id
        LEFT JOIN technicians t ON r.technician_id = t.technician_id
        WHERE r.status = 'pending'
        GROUP BY r.request_id
        ORDER BY r.request_date DESC";

$result = $conn->query($sql);
$requests = $result->fetch_all(MYSQLI_ASSOC);

$tech_sql = "SELECT t.*
            FROM technicians t
            WHERE t.technician_id NOT IN (
                SELECT DISTINCT technician_id 
                FROM requests 
                WHERE status IN ('pending', 'accepted') 
                AND technician_id IS NOT NULL
            )";

$tech_result = $conn->query($tech_sql);
$technicians = $tech_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>คำขอบริการ - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #E3F2FD;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .content {
            margin: 100px auto;
            padding: 20px;
            max-width: 1200px;
            background-color: #F8F9FA;
        }

        .page-title {
            color: #1565C0;
            text-align: center;
            margin-bottom: 30px;
            padding: 15px;
        }

        .request-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            color: white;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .status-pending {
            background-color: #FF0000;
        }

        h3 {
            color: #FF0000;
            margin: 15px 0 10px 0;
            border-bottom: 2px solid #FF0000;
            padding-bottom: 5px;
        }

        p {
            margin: 8px 0;
            line-height: 1.5;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
            font-weight: bold;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-confirm {
            background-color: #28a745;
        }

        .btn-confirm:hover {
            background-color: #218838;
        }

        .btn-reject {
            background-color: #dc3545;
        }

        .btn-reject:hover {
            background-color: #c82333;
        }

        .technician-select select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid #ddd;
            font-size: 16px;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            color: #856404;
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
        }
    </style>
</head>
<body>
    <?php include 'addmin_sidebar.php'; ?>
    <div class="content">
        <h2 class="page-title">
            <i class="fas fa-clipboard-list"></i>
            คำขอบริการที่รอดำเนินการ
        </h2>

        <?php if(empty($requests)): ?>
            <div class="alert">ไม่มีคำขอบริการที่รอดำเนินการ</div>
        <?php endif; ?>
        
        <?php foreach($requests as $request): ?>
            <div class="request-card">
                <div class="status-badge status-<?php echo strtolower($request['status']); ?>">
                    <?php echo $request['status']; ?>
                </div>
                
                <h3>ข้อมูลลูกค้า</h3>
                <p><strong>ชื่อ:</strong> <?php echo $request['name_last']; ?></p>
                <p><strong>เบอร์โทร:</strong> <?php echo $request['phone']; ?></p>
                <p><strong>ที่อยู่:</strong> <?php echo $request['address']; ?></p>
                
                <h3>รายละเอียดบริการ</h3>
                <p><strong>บริการ:</strong> <?php echo $request['service_names']; ?></p>
                <?php
                $prices = explode(',', $request['service_prices']);
                $total_price = array_sum($prices);
                ?>
                <p><strong>ราคารวม:</strong> <?php echo number_format($total_price, 2); ?> บาท</p>
                <p><strong>วันที่นัด:</strong> <?php echo date('d/m/Y H:i', strtotime($request['request_date'])); ?></p>
                <p><strong>หมายเหตุ:</strong> <?php echo $request['notes']; ?></p>

                <div class="action-buttons">
                    <form method="POST" class="action-form">
                        <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                        
                        <?php if(!empty($technicians)): ?>
                            <div class="technician-select">
                                <select name="technician_id" required>
                                    <option value="">กรุณาเลือกช่าง</option>
                                    <?php foreach($technicians as $tech): ?>
                                        <option value="<?php echo $tech['technician_id']; ?>">
                                            <?php echo $tech['name']; ?> (<?php echo $tech['phone']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="button-group">
                                <button type="submit" name="action" value="confirm" class="btn btn-confirm">
                                    <i class="fas fa-check"></i> ยืนยันคำขอและมอบหมายช่าง
                                </button>
                                <button type="submit" name="action" value="reject" class="btn btn-reject">
                                    <i class="fas fa-clock"></i> ขอเปลี่ยนวันเวลา
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="alert">ไม่มีช่างว่างในขณะนี้</div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>