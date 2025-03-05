<?php include 'sidebar.php'; ?>
<?php 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $request_id = $_POST['request_id'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete from request_services first
        $stmt1 = $conn->prepare("DELETE FROM request_services WHERE request_id = ?");
        $stmt1->bind_param("i", $request_id);
        $stmt1->execute();
        
        // Then delete from requests
        $stmt2 = $conn->prepare("DELETE FROM requests WHERE request_id = ? AND user_id = ?");
        $stmt2->bind_param("ii", $request_id, $_SESSION['user_id']);
        $stmt2->execute();
        
        // Commit transaction
        $conn->commit();
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ดึงข้อมูลบริการ
$user_id = $_SESSION['user_id'];
$sql = "SELECT r.request_id, r.request_date, r.notes, r.status,
       u.name_last, u.address, u.phone,
       t.name as technician_name, 
       t.phone as technician_phone,
       GROUP_CONCAT(s.name) as service_names,
       GROUP_CONCAT(s.image) as service_images,
       GROUP_CONCAT(s.description) as service_descriptions,
       GROUP_CONCAT(s.price) as service_prices
FROM requests r 
JOIN request_services rs ON r.request_id = rs.request_id
JOIN sub_services s ON rs.sub_service_id = s.sub_services_id 
JOIN users u ON r.user_id = u.user_id 
LEFT JOIN technicians t ON r.technician_id = t.technician_id
WHERE r.user_id = ?
GROUP BY r.request_id
ORDER BY r.request_date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$requests = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>บันทึกข้อมูลสำเร็จ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .service-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }

        .service-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .service-header {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .service-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 20px;
        }

        .service-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .detail-item {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .success-message {
            text-align: center;
            color: #28a745;
            margin: 20px 0;
        }

        .selected-services {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .service-tag {
            background: #e9ecef;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
            color: #495057;
        }

        .action-buttons {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            grid-column: 1 / -1;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-weight: bold;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-warning {
            background: #ffc107;
            color: #000;
        }

        .btn-warning:hover {
            background: #e0a800;
        }

        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }

        .status-confirm-service-request {
            color: #28a745;
            font-weight: bold;
        }

        .status-reject-service-request {
            color: #dc3545;
            font-weight: bold;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .service-item {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
        }

        .service-price {
            color: #007bff;
            font-weight: bold;
            margin: 5px 0;
        }

        .service-description {
            font-size: 0.9em;
            color: #6c757d;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center; /* เพิ่มบรรทัดนี้ */
            gap: 5px;
            font-weight: bold;
            text-decoration: none;
            transition: background-color 0.3s;
            min-width: 150px; /* เพิ่มบรรทัดนี้ */
            text-align: center; /* เพิ่มบรรทัดนี้ */
        }

        .action-buttons {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            grid-column: 1 / -1;
        }

        </style>
</head>
<body>
    <div class="service-container">
        <div class="success-message">
            <h1><i class="fas fa-check-circle"></i> บันทึกข้อมูลสำเร็จ</h1>
            <p>รายการบริการของคุณ</p>
        </div>

        <?php foreach($requests as $request): 
            $service_names = explode(',', $request['service_names']);
            $service_images = explode(',', $request['service_images']);
            $service_descriptions = explode(',', $request['service_descriptions']);
            $service_prices = explode(',', $request['service_prices']);
        ?>
            <div class="service-card">
                <div class="service-header">
                    <h2>รายการบริการที่เลือก</h2>
                    <div class="services-grid">
                        <?php for($i = 0; $i < count($service_names); $i++): ?>
                            <div class="service-item">
                                <img src="image/<?php echo htmlspecialchars($service_images[$i]); ?>" 
                                     alt="<?php echo htmlspecialchars($service_names[$i]); ?>"
                                     class="service-image">
                                <h3><?php echo htmlspecialchars($service_names[$i]); ?></h3>
                                <div class="service-price">
                                    ฿<?php echo number_format($service_prices[$i], 2); ?>
                                </div>
                                <div class="service-description">
                                    <?php echo htmlspecialchars($service_descriptions[$i]); ?>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
                
                <div class="service-details">
                    <div class="detail-item">
                        <strong>ชื่อ-นามสกุล:</strong> 
                        <?php echo htmlspecialchars($request['name_last']); ?>
                    </div>
                    <div class="detail-item">
                        <strong>เบอร์โทร:</strong> 
                        <?php echo htmlspecialchars($request['phone']); ?>
                    </div>
                    <div class="detail-item">
                        <strong>ที่อยู่:</strong> 
                        <?php echo htmlspecialchars($request['address']); ?>
                    </div>
                    <div class="detail-item">
                        <strong>วันที่นัดหมาย:</strong> 
                        <?php echo date('d/m/Y H:i', strtotime($request['request_date'])); ?>
                    </div>
                    <div class="detail-item" style="grid-column: 1 / -1;">
                        <strong>รายละเอียด:</strong> 
                        <?php echo htmlspecialchars($request['notes']); ?>
                    </div>
                    <div class="detail-item">
                        <strong>สถานะ:</strong> 
                        <span class="status-<?php echo strtolower(str_replace(' ', '-', $request['status'])); ?>">
                            <?php echo $request['status']; ?>
                        </span>
                    </div>
                    <?php if($request['technician_name']): ?>
                        <div class="detail-item technician-info" style="grid-column: 1 / -1;">
                            <h3>ข้อมูลช่างผู้รับผิดชอบ</h3>
                            <div class="tech-details">
                                <p><i class="fas fa-user-cog"></i> ชื่อช่าง: <?php echo htmlspecialchars($request['technician_name']); ?></p>
                                <p><i class="fas fa-phone"></i> เบอร์ติดต่อ: <?php echo htmlspecialchars($request['technician_phone']); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="action-buttons">
                        <?php if($request['status'] == 'Pending' || $request['status'] == 'Reject service request'): ?>
                            <!-- ปุ่มแก้ไขข้อมูล -->
                            <a href="insertdata.php?request_id=<?php echo $request['request_id']; ?>" class="btn btn-warning">
                                <i class="fas fa-edit"></i> แก้ไขข้อมูล
                            </a>
                            <!-- ปุ่มลบบริการ -->
                            <button onclick="handleDelete(<?php echo $request['request_id']; ?>, 'ลบ')" class="btn btn-danger">
                                <i class="fas fa-trash"></i> ลบบริการ
                            </button>
                        <?php elseif($request['status'] == 'Confirm service request'): ?>
                            <!-- ปุ่มชำระบริการ -->
                            <a href="payment.php?request_id=<?php echo $request['request_id']; ?>" class="btn btn-primary">
                                <i class="fas fa-credit-card"></i> ชำระค่าบริการ
                            </a>
                            <!-- ปุ่มลบบริการ -->
                            <button onclick="handleDelete(<?php echo $request['request_id']; ?>, 'ลบ')" class="btn btn-danger">
                                <i class="fas fa-trash"></i> ลบบริการ
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
    function handleDelete(requestId, action) {
        if(confirm(`คุณต้องการ${action}บริการนี้ใช่หรือไม่?`)) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('request_id', requestId);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert('เกิดข้อผิดพลาด: ' + data.message);
                }
            });
        }
    }
    </script>
</body>
</html>

