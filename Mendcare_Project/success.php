<?php 
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รอการยืนยันบริการ</title>
    <link rel="stylesheet" href="ind.css">
</head>

<body>

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
        <h1>บริการของคุณกำลังรอการยืนยัน</h1>

        <?php if (!empty($_SESSION["selected_services"])): ?>
            <ul>
                <?php foreach ($_SESSION["selected_services"] as $index => $service): ?>
                    <li>
                        <?php echo $service; ?> - <span style="color: orange;">รอการยืนยัน</span>
                        <a href="remove_service.php?index=<?php echo $index; ?>" style="color: red; margin-left: 10px;">ยกเลิก</a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <form action="final_confirm.php" method="POST">
                <button type="submit">ยืนยันบริการทั้งหมด</button>
            </form>
        <?php else: ?>
            <p>คุณยังไม่ได้เลือกบริการ</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
