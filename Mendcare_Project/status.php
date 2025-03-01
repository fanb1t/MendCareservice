<?php
include 'sidebar.php';

$user_id = $_SESSION['user_id'];

$sql = "SELECT 
    r.request_id,
    r.request_date,
    r.payment_date,
    r.receipt_number,
    r.payment_method,
    r.amount,
    r.notes,
    r.status,
    GROUP_CONCAT(ss.name) as service_names,
    GROUP_CONCAT(ss.image) as service_images,
    t.name as technician_name,
    t.phone as technician_phone,
    u.address,
    u.phone as user_phone
FROM requests r
JOIN request_services rs ON r.request_id = rs.request_id
JOIN sub_services ss ON rs.sub_service_id = ss.sub_services_id
LEFT JOIN technicians t ON r.technician_id = t.technician_id
JOIN users u ON r.user_id = u.user_id
WHERE r.user_id = ? 
AND r.payment_status = 'paid'
AND r.status = 'Confirm service request'
GROUP BY r.request_id
ORDER BY r.request_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>งานบริการที่กำลังดำเนินการ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .service-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 25px;
            margin: 20px 0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .status-badge {
            background: #ffc107;
            color: #000;
            padding: 8px 15px;
            border-radius: 20px;
            display: inline-block;
            font-weight: bold;
        }
        .details-section {
            margin: 15px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin: 15px 0;
        }
        .service-item {
            background: #fff;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .service-image {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .technician-info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>งานบริการที่กำลังดำเนินการ</h2>
        
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="service-card">
                    <div class="request-header">
                        <h3>รหัสคำขอ: <?php echo $row['request_id']; ?></h3>
                        <span class="status-badge">กำลังดำเนินการ</span>
                    </div>

                    <div class="services-grid">
                        <?php 
                        $services = explode(',', $row['service_names']);
                        $images = explode(',', $row['service_images']);
                        for($i = 0; $i < count($services); $i++): 
                        ?>
                            <div class="service-item">
                                <img src="<?php echo htmlspecialchars($images[$i]); ?>" 
                                     alt="<?php echo htmlspecialchars($services[$i]); ?>" 
                                     class="service-image">
                                <h4><?php echo htmlspecialchars($services[$i]); ?></h4>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <div class="details-section">
                        <p><i class="fas fa-calendar"></i> <strong>วันที่นัด:</strong> 
                           <?php echo date('d/m/Y H:i', strtotime($row['request_date'])); ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> <strong>ที่อยู่:</strong> 
                           <?php echo htmlspecialchars($row['address']); ?></p>
                        <p><i class="fas fa-phone"></i> <strong>เบอร์โทรศัพท์:</strong> 
                           <?php echo htmlspecialchars($row['user_phone']); ?></p>
                    </div>

                    <div class="technician-info">
                        <h4><i class="fas fa-user-cog"></i> ข้อมูลช่าง</h4>
                        <p><strong>ชื่อช่าง:</strong> <?php echo htmlspecialchars($row['technician_name']); ?></p>
                        <p><strong>เบอร์ช่าง:</strong> <?php echo htmlspecialchars($row['technician_phone']); ?></p>
                    </div>

                    <?php if($row['notes']): ?>
                        <div class="details-section">
                            <p><i class="fas fa-clipboard-list"></i> <strong>รายละเอียดเพิ่มเติม:</strong> 
                               <?php echo htmlspecialchars($row['notes']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>ไม่พบรายการที่กำลังดำเนินการ</p>
        <?php endif; ?>
    </div>
</body>
</html>