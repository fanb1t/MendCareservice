<?php
require 'connectDB.php';

if (isset($_GET['STU_ID'])) {
    $student_id = $_GET['STU_ID'];

    // ตรวจสอบความถูกต้องของ student_id
    if (!preg_match('/^\d+$/', $student_id)) {
        die("<p>รหัสนักศึกษาไม่ถูกต้อง</p>");
    }

    // เตรียมคำสั่ง SQL
    $sql = "SELECT * FROM STUDENT WHERE STU_ID = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<h1>ผลการค้นหาข้อมูลนักศึกษา</h1>";
            echo "<table border='1'>
                    <tr>
                        <th>ชื่อ</th>
                        <th>สาขา</th>
                        <th>หน่วยกิตรวม</th>
                    </tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . htmlspecialchars($row['STU_Name']) . "</td>
                        <td>" . htmlspecialchars($row['Major']) . "</td>
                        <td>" . htmlspecialchars($row['Total_Credits']) . "</td>
                      </tr>";
            }
            echo "</table>";
        } else {
            echo "<p>ไม่พบข้อมูลสำหรับเลขนักศึกษา: " . htmlspecialchars($student_id) . "</p>";
        }
    } else {
        echo "<p>คำสั่ง SQL มีปัญหา: " . htmlspecialchars($conn->error) . "</p>";
    }
} else {
    echo "<p>กรุณากรอกเลขนักศึกษา</p>";
}
?>
