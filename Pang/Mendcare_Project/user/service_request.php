<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยืนยันข้อมูลการซ่อม | Mendcare Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div id="main-content">
        <span class="nav-toggle" onclick="toggleNav()">☰</span>
        <?php include 'ADM_sidebar.php'; ?>
        

        <div class="container">
            <h1 class="text-center my-4">ยืนยันข้อมูลการซ่อม</h1>
            <div class="confirmation-details">
                <h2>ข้อมูลจากฟอร์มการซ่อม</h2>
                <div class="confirm-item">
                    <span class="label">ชื่อ-นามสกุล:</span>
                    <span id="confirm-name"><?php echo $_POST['name']; ?></span>
                </div>
                <div class="confirm-item">
                    <span class="label">ที่อยู่:</span>
                    <span id="confirm-address"><?php echo $_POST['address']; ?></span>
                </div>
                <div class="confirm-item">
                    <span class="label">วันที่และเวลา:</span>
                    <span id="confirm-datetime"><?php echo $_POST['datetime']; ?></span>
                </div>
                <div class="confirm-item">
                    <span class="label">รายละเอียดการซ่อม:</span>
                    <span id="confirm-description"><?php echo $_POST['description']; ?></span>
                </div>
                <div class="confirm-item">
                    <span class="label">เบอร์โทรศัพท์:</span>
                    <span id="confirm-phone"><?php echo $_POST['phone']; ?></span>
                </div>
            </div>

            <h2>ข้อมูลจากตะกร้า</h2>
            <?php
            // สมมติว่าข้อมูลบริการในตะกร้าเก็บใน $_SESSION
            if (isset($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $service) {
                    echo "<div class='confirm-item'>";
                    echo "<span class='label'>" . $service['name'] . ":</span>";
                    echo "<span class='value'>" . $service['details'] . "</span>";
                    echo "</div>";
                }
            } else {
                echo "<p>ไม่มีบริการในตะกร้า</p>";
            }
            ?>

            <form action="submit_service_request.php" method="post">
                <input type="hidden" name="name" value="<?php echo $_POST['name']; ?>">
                <input type="hidden" name="address" value="<?php echo $_POST['address']; ?>">
                <input type="hidden" name="datetime" value="<?php echo $_POST['datetime']; ?>">
                <input type="hidden" name="description" value="<?php echo $_POST['description']; ?>">
                <input type="hidden" name="phone" value="<?php echo $_POST['phone']; ?>">
                <button type="submit" class="btn btn-primary">ยืนยันข้อมูล</button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
        }
    </script>
</body>
</html>