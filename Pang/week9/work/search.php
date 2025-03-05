<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="search.css" type="text/css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลนักศึกษา</title>
    <link rel="stylesheet" href="styles.css">
    <script>
    function toggleDelete(courseId) {
        var deleteButton = document.getElementById('delete-button-' + courseId);
        var cancelButton = document.getElementById('cancel-button-' + courseId);
        var withdrawButton = document.getElementById('withdraw-button-' + courseId);
        if (deleteButton.style.display === 'none') {
            deleteButton.style.display = 'inline-block';
            cancelButton.style.display = 'inline-block';
            withdrawButton.style.display = 'none';
        } else {
            deleteButton.style.display = 'none';
            cancelButton.style.display = 'none';
            withdrawButton.style.display = 'inline-block';
        }
    }
    </script>
</head>
<body>
<?php
require 'connectDB.php';
session_start();
$ีuser = $_SESSION['user'];

$student_sql = "SELECT STU_ID, STU_Name, Major, Total_Credits FROM STUDENT WHERE username = ?";
$stmt = $conn->prepare($student_sql);
if ($stmt) {
    $stmt->bind_param("s", $ีuser);
    $stmt->execute();
    $student_result = $stmt->get_result();
    if ($student_result->num_rows > 0) {
        $student_data = $student_result->fetch_assoc();
        echo "<h2 class='section-title'>ข้อมูลนักศึกษา</h2><br>";
        echo "<table class='student-info-table'>
            <tr><th>ชื่อ</th><td>" . htmlspecialchars($student_data['STU_Name']) . "</td></tr>
            <tr><th>สาขา</th><td>" . htmlspecialchars($student_data['Major']) . "</td></tr>
            <tr><th>หน่วยกิตรวม</th><td>" . htmlspecialchars($student_data['Total_Credits']) . "</td></tr>
        </table>";
        $student_id = $student_data['STU_ID'];
    } else {
        echo "<p>ไม่พบข้อมูลนักศึกษา</p>";
    }
    $stmt->close();
} else {
    echo "<p>คำสั่ง SQL มีปัญหา: " . htmlspecialchars($conn->error) . "</p>";
}
?>

<div class="results-table">
    <h2 class="section-title">ผลการศึกษา</h2><br>
    <table>
        <tr>
            <th>รหัสวิชา</th>
            <th>ชื่อวิชา</th>
            <th>เกรด</th>
            <th>ถอนรายวิชา</th>
        </tr>
        <?php
        if (isset($_GET['delete_course_id'])) {
            $delete_course_id = $_GET['delete_course_id'];
            $delete_sql = "DELETE FROM ENROLLMENT WHERE Course_ID = ? AND STU_ID = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            if ($delete_stmt) {
                $delete_stmt->bind_param("ss", $delete_course_id, $student_id);
                $delete_stmt->execute();
                $delete_stmt->close();
                echo "<div class='notification success'>
                    <i class='fas fa-check-circle'></i> ลบรายวิชา " . htmlspecialchars($delete_course_id) . " เรียบร้อยแล้ว
                </div>";
            } else {
                echo "<div class='notification error'>
                    <i class='fas fa-exclamation-circle'></i> เกิดข้อผิดพลาดในการลบรายวิชา: " . htmlspecialchars($conn->error) . "
                </div>";
            }
        }

        $grade_sql = "SELECT e.Course_ID, c.Course_Name, e.Grade 
                      FROM ENROLLMENT e 
                      JOIN CLASS c ON e.Course_ID = c.Course_ID 
                      WHERE e.STU_ID = ?";
        $stmt = $conn->prepare($grade_sql);
        if ($stmt) {
            $stmt->bind_param("s", $student_id);
            $stmt->execute();
            $grade_result = $stmt->get_result();
            if ($grade_result->num_rows > 0) {
                while ($grade_row = $grade_result->fetch_assoc()) {
                    $course_id = htmlspecialchars($grade_row['Course_ID']);
                    echo "<tr>
                        <td>" . $course_id . "</td>
                        <td>" . htmlspecialchars($grade_row['Course_Name']) . "</td>
                        <td>" . htmlspecialchars($grade_row['Grade']) . "</td>
                        <td>
                            <button id='withdraw-button-" . $course_id . "' class='btn-withdraw' onclick='toggleDelete(\"" . $course_id . "\")'>ถอนรายวิชา</button>
                            <button id='delete-button-" . $course_id . "' class='btn-delete' style='display:none;' onclick='window.location.href=\"?delete_course_id=" . urlencode($course_id) . "\"'>ลบ</button>
                            <button id='cancel-button-" . $course_id . "' class='btn-cancel' style='display:none;' onclick='toggleDelete(\"" . $course_id . "\")'>ยกเลิก</button>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='4'>ไม่พบข้อมูลผลการศึกษา</td></tr>";
            }
            $stmt->close();
        } else {
            echo "<p>คำสั่ง SQL มีปัญหา: " . htmlspecialchars($conn->error) . "</p>";
        }
        ?>
    </table>
</div>

<div class="add-course-button">
    <a href="add_course.php" class="btn">เพิ่มรายวิชา</a>
</div>

<style>
.add-course-button {
    margin: 20px 0;
    text-align: center;
}
.btn {
    display: inline-block;
    padding: 10px 20px;
    font-size: 16px;
    color: #fff;
    background-color: #007BFF;
    text-decoration: none;
    border-radius: 5px;
    transition: background-color 0.3s ease;
}
.btn:hover {
    background-color: #0056b3;
}
.btn-withdraw {
    display: inline-block;
    padding: 5px 10px;
    font-size: 14px;
    color: #fff;
    background-color: #FF5733;
    text-decoration: none;
    border-radius: 5px;
    text-align: center;
}
.btn-withdraw:hover {
    background-color: #C0392B;
}
.btn-delete {
    display: inline-block;
    padding: 5px 10px;
    font-size: 14px;
    color: #fff;
    background-color: #28A745;
    text-decoration: none;
    border-radius: 5px;
    text-align: center;
}
.btn-delete:hover {
    background-color: #218838;
}
.btn-cancel {
    display: inline-block;
    padding: 5px 10px;
    font-size: 14px;
    background-color: #f0ad4e;
    color: #fff;
    border-radius: 5px;
}
.btn-cancel:hover {
    background-color: #ec971f;
}
</style>

<a href="login.php" class="logout-button">
    <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
</a>

</body>
</html>
