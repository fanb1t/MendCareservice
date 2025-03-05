<?php
require_once 'connect.php';
include 'sidebar.php';

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_id = $_POST['request_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    
    $sql = "INSERT INTO reviews (request_id, rating, comment) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $request_id, $rating, $comment);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "รีวิวของคุณถูกบันทึกเรียบร้อยแล้ว";
    } else {
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการบันทึกรีวิว";
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch completed services and reviews
$user_id = $_SESSION['user_id'];
$sql = "SELECT 
    r.request_id,
    r.request_date,
    r.status,
    GROUP_CONCAT(ss.name) as service_names,
    GROUP_CONCAT(ss.image) as service_images,
    t.name as technician_name,
    rv.rating,
    rv.comment,
    rv.reviews_id
FROM requests r
JOIN request_services rs ON r.request_id = rs.request_id
JOIN sub_services ss ON rs.sub_service_id = ss.sub_services_id
LEFT JOIN technicians t ON r.technician_id = t.technician_id
LEFT JOIN reviews rv ON r.request_id = rv.request_id
WHERE r.user_id = ? 
AND r.status = 'Completed'
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
    <title>รีวิวบริการ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .service-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 25px;
            margin: 20px 0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin: 15px 0;
        }
        .service-image {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }
        .rating-stars {
            font-size: 24px;
            color: #ffd700;
            margin: 15px 0;
        }
        .review-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 15px;
        }
        .star-rating {
            display: inline-block;
            font-size: 30px;
            cursor: pointer;
        }
        .star-rating input {
            display: none;
        }
        .star-rating label {
            color: #ddd;
            padding: 5px;
        }
        .star-rating input:checked ~ label {
            color: #ffd700;
        }
        .submit-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        .review-display {
            margin-top: 15px;
            padding: 15px;
            background: #e3f2fd;
            border-radius: 8px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .completed-status {
            background: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 14px;
        }
        .status-info {
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>รีวิวบริการ</h2>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="service-card">
                    <div class="status-info">
                        <h3>รหัสคำขอ: <?php echo $row['request_id']; ?></h3>
                        <p>สถานะ: <span class="completed-status">เสร็จสิ้น</span></p>
                        <p>วันที่แจ้ง: <?php echo date('d/m/Y H:i', strtotime($row['request_date'])); ?></p>
                    </div>
                    
                    <div class="services-grid">
                        <?php 
                        $services = explode(',', $row['service_names']);
                        $images = explode(',', $row['service_images']);
                        foreach($services as $index => $service): 
                        ?>
                            <div>
                                <img src="<?php echo htmlspecialchars($images[$index]); ?>" 
                                     alt="<?php echo htmlspecialchars($service); ?>" 
                                     class="service-image">
                                <p><?php echo htmlspecialchars($service); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (!$row['reviews_id']): ?>
                        <form class="review-form" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                            <input type="hidden" name="request_id" value="<?php echo $row['request_id']; ?>">
                            <h4>ให้คะแนนบริการ</h4>
                            <div class="star-rating">
                                <?php for($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" id="star<?php echo $i; ?>-<?php echo $row['request_id']; ?>" 
                                           name="rating" value="<?php echo $i; ?>" required>
                                    <label for="star<?php echo $i; ?>-<?php echo $row['request_id']; ?>">
                                        <i class="fas fa-star"></i>
                                    </label>
                                <?php endfor; ?>
                            </div>
                            <div>
                                <textarea name="comment" rows="4" 
                                          placeholder="แสดงความคิดเห็นของคุณ" 
                                          style="width: 100%; margin: 10px 0;" required></textarea>
                            </div>
                            <button type="submit" class="submit-btn">ส่งรีวิว</button>
                        </form>
                    <?php else: ?>
                        <div class="review-display">
                            <h4>รีวิวของคุณ</h4>
                            <div class="rating-stars">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star" style="color: <?php echo $i <= $row['rating'] ? '#ffd700' : '#ddd'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p><?php echo htmlspecialchars($row['comment']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>ไม่พบบริการที่เสร็จสิ้น</p>
        <?php endif; ?>
    </div>

    <script>
        document.querySelectorAll('.star-rating input').forEach(input => {
            input.addEventListener('change', function() {
                let id = this.id.split('-')[1];
                let rating = this.value;
                let labels = document.querySelectorAll(`label[for^="star"][for$="-${id}"]`);
                labels.forEach(label => {
                    label.style.color = '#ddd';
                });
                for(let i = labels.length - 1; i >= labels.length - rating; i--) {
                    labels[i].style.color = '#ffd700';
                }
            });
        });
    </script>
</body>
</html>