

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บริการที่เลือก</title>
    <link rel="stylesheet" href="ind.css">
</head>

<body>
    <?php 
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $service = $_POST["service"];
    if (!isset($_SESSION["selected_services"])) {
        $_SESSION["selected_services"] = [];
    }
    $_SESSION["selected_services"][] = $service;
}
?>

<div id="main-content">
    <?php include 'sidebar.php'; ?>
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
    <h1>บริการที่คุณเลือก</h1>

    <?php if (!empty($_SESSION["selected_services"])): ?>
        <ul>
            <?php foreach ($_SESSION["selected_services"] as $index => $service): ?>
                <li>
                    <?php echo $service; ?>
                    <a href="remove_service.php?index=<?php echo $index; ?>" style="color: red;">ลบ</a>
                </li>
            <?php endforeach; ?>
        </ul>
        <a href="confirm_service.php">ยืนยันบริการ</a>
    <?php else: ?>
        <p>คุณยังไม่ได้เลือกบริการ</p>
    <?php endif; ?>
</div>

</body>
</html>
