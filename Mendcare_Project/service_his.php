<?php 
include 'sidebar.php';

$user_id = $_SESSION['user_id'];

// Fetch completed services with all related information
$sql = "SELECT 
    r.request_id,
    r.request_date,
    r.status,
    r.notes,
    r.amount,
    r.payment_method,
    GROUP_CONCAT(DISTINCT ss.name) as service_names,
    GROUP_CONCAT(DISTINCT ss.image) as service_images,
    GROUP_CONCAT(DISTINCT ss.price) as service_prices,
    t.name as technician_name,
    t.phone as technician_phone,
    u.address,
    u.phone as user_phone,
    rv.rating,
    rv.comment,
    rv.created_at as review_date
FROM requests r
JOIN request_services rs ON r.request_id = rs.request_id
JOIN sub_services ss ON rs.sub_service_id = ss.sub_services_id
LEFT JOIN technicians t ON r.technician_id = t.technician_id
JOIN users u ON r.user_id = u.user_id
LEFT JOIN reviews rv ON r.request_id = rv.request_id
WHERE r.user_id = ? 
AND r.status = 'completed'
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
    <title>ประวัติการใช้บริการ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .history-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .service-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin: 15px 0;
        }
        .service-item {
            text-align: center;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .service-image {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .info-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        .review-section {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
        .stars {
            color: #ffd700;
            font-size: 20px;
        }
        .status-completed {
            background: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>ประวัติการใช้บริการ</h2>

        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="history-card">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3>รหัสคำขอ: <?php echo $row['request_id']; ?></h3>
                        <span class="status-completed">เสร็จสิ้น</span>
                    </div>

                    <div class="info-section">
                        <p><i class="fas fa-calendar"></i> วันที่นัด: <?php echo date('d/m/Y H:i', strtotime($row['request_date'])); ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> สถานที่: <?php echo htmlspecialchars($row['address']); ?></p>
                        <p><i class="fas fa-phone"></i> เบอร์ติดต่อ: <?php echo htmlspecialchars($row['user_phone']); ?></p>
                    </div>

                    <h4>รายการบริการ</h4>
                    <div class="service-grid">
                        <?php 
                        $services = explode(',', $row['service_names']);
                        $images = explode(',', $row['service_images']);
                        $prices = explode(',', $row['service_prices']);
                        foreach($services as $index => $service): 
                        ?>
                            <div class="service-item">
                                <img src="image/<?php echo htmlspecialchars($images[$index]); ?>" 
                                     alt="<?php echo htmlspecialchars($service); ?>" 
                                     class="service-image">
                                <h5><?php echo htmlspecialchars($service); ?></h5>
                                <p>ราคา: <?php echo number_format($prices[$index], 2); ?> บาท</p>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="info-section">
                        <h4><i class="fas fa-user-cog"></i> ข้อมูลช่าง</h4>
                        <p>ชื่อช่าง: <?php echo htmlspecialchars($row['technician_name']); ?></p>
                        <p>เบอร์ช่าง: <?php echo htmlspecialchars($row['technician_phone']); ?></p>
                    </div>

                    <?php if ($row['rating']): ?>
                        <div class="review-section">
                            <h4>รีวิวของคุณ</h4>
                            <div class="stars">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star" style="color: <?php echo $i <= $row['rating'] ? '#ffd700' : '#ddd'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p><?php echo htmlspecialchars($row['comment']); ?></p>
                            <small>รีวิวเมื่อ: <?php echo date('d/m/Y H:i', strtotime($row['review_date'])); ?></small>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>ยังไม่มีประวัติการใช้บริการที่เสร็จสิ้น</p>
        <?php endif; ?>
    </div>
</body>
</html>