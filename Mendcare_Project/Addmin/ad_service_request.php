<?php include 'addmin_sidebar.php'; ?>
<?php
session_start();
require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $request_id = $_POST['request_id'];
        
        if ($_POST['action'] === 'confirm') {
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

// Query for pending requests only
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

// Query for available technicians only
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
            background-color: rgba(255, 204, 0, 0.17);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }

        .content {
            padding: 20px;
            margin-left: 250px;
            transition: margin-left 0.5s;
        }

        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.2em;
        }

        .request-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .request-card:hover {
            transform: translateY(-5px);
        }

        .status-badge {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .status-pending { 
            background: #ffd700;
            color: #000;
        }

        .status-accepted { 
            background: #90EE90;
            color: #006400;
        }

        .status-completed { 
            background: #87CEEB;
            color: #00008B;
        }

        .status-canceled { 
            background: #FFB6C1;
            color: #8B0000;
        }

        h3 {
            color: #444;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-top: 20px;
        }

        p {
            color: #666;
            margin: 10px 0;
            line-height: 1.6;
        }

        .action-form {
            width: 100%;
        }

        .technician-select {
            margin-bottom: 20px;
        }

        .technician-select select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s;
        }

        .technician-select select:focus {
            border-color: #4CAF50;
            outline: none;
        }

        .button-group {
            display: flex;
            gap: 15px;
        }

        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            flex: 1;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            border: none;
            color: white;
            font-weight: bold;
            transition: all 0.3s ease;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn i {
            font-size: 1.1em;
        }

        .btn-confirm { 
            background: #4CAF50;
        }

        .btn-confirm:hover {
            background: #45a049;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.2);
        }

        .btn-reject { 
            background: #f44336;
        }

        .btn-reject:hover {
            background: #da190b;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(244, 67, 54, 0.2);
        }

        .technician-info {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 10px;
            }

            .button-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
        
    </style>
</head>
<body>
    
    <div class="content">
        <h1>คำขอบริการ</h1>
        
        <?php foreach($requests as $request): ?>
            <div class="request-card">
                <div class="status-badge status-<?php echo strtolower($request['status']); ?>">
                    <?php echo $request['status']; ?>
                </div>
                
                <h3>ข้อมูลลูกค้า</h3>
                <p>ชื่อ: <?php echo $request['name_last']; ?></p>
                <p>เบอร์โทร: <?php echo $request['phone']; ?></p>
                <p>ที่อยู่: <?php echo $request['address']; ?></p>
                
                <h3>รายละเอียดบริการ</h3>
                <p>บริการ: <?php echo $request['service_names']; ?></p>
                <p>ราคารวม: <?php echo $request['service_prices']; ?> บาท</p>
                <p>วันที่นัด: <?php echo date('d/m/Y H:i', strtotime($request['request_date'])); ?></p>
                <p>หมายเหตุ: <?php echo $request['notes']; ?></p>

                <div class="action-buttons">
                    <form method="POST" class="action-form">
                        <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                        
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
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
