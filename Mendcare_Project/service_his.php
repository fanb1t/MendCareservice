<?php include 'header.php'; ?> 
<?php 
// session_start();

// // ตรวจสอบว่ามีประวัติบริการหรือไม่
// if (!isset($_SESSION["service_history"])) {
//     $_SESSION["service_history"] = [];
// }

// // ตัวอย่าง: ถ้ามีการยืนยันบริการจาก `final_confirm.php` ให้บันทึกลงประวัติ
// if (isset($_SESSION["confirmed_services"])) {
//     $_SESSION["service_history"][] = [
//         "services" => $_SESSION["confirmed_services"],
//         "date" => date("Y-m-d H:i:s"),
//         "status" => "รอการอนุมัติ" // สถานะเริ่มต้น
//     ];
//     unset($_SESSION["confirmed_services"]); // ล้างค่าหลังบันทึก
// }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการขอบริการ</title>
    <link rel="stylesheet" href="ind.css">
</head>

<body>

<?php include 'sidebar.php'; ?> 

    <div class="container">
        <h1>ประวัติการขอบริการ</h1>

        <?php if (!empty($_SESSION["service_history"])): ?>
            <table border="1">
                <thead>
                    <tr>
                        <th>วันที่ขอบริการ</th>
                        <th>รายการบริการ</th>
                        <th>สถานะ</th>
                        <th>เพิ่มเติม</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION["service_history"] as $index => $history): ?>
                        <tr>
                            <td><?php echo $history["date"]; ?></td>
                            <td>
                                <ul>
                                    <?php foreach ($history["services"] as $service): ?>
                                        <li><?php echo $service; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                            <td style="color: <?php echo ($history["status"] === 'เสร็จสิ้น') ? 'green' : 'orange'; ?>">
                                <?php echo $history["status"]; ?>
                            </td>
                            <td>
                                <a href="service_detail.php?id=<?php echo $index; ?>">ดูรายละเอียด</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>ยังไม่มีประวัติการขอบริการ</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
