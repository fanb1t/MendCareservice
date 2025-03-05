<?php
session_start();
require 'connectDB.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับข้อมูลที่ผู้ใช้กรอก
    $course_code = trim($_POST['course_code']); // รหัสวิชาที่จะเพิ่ม

    // ตรวจสอบว่า STU_ID ถูกเก็บใน session หรือไม่
    if (isset($_SESSION['stu_id'])) {
        $stu_id = $_SESSION['stu_id'];

        // สร้างคำสั่ง SQL เพื่อเพิ่มรายวิชาใน ENROLLMENT
        $sql = "INSERT INTO ENROLLMENT (STU_ID, Course_ID) VALUES (?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            // ผูกค่ากับตัวแปร SQL
            $stmt->bind_param("ss", $stu_id, $course_code);

            // รันคำสั่ง SQL
            if ($stmt->execute()) {
                echo "<script>alert('Course added successfully.'); window.location.href='search.php';</script>";
            } else {
                echo "<script>alert('Failed to add course.');</script>";
            }

            // ปิดคำสั่ง
            $stmt->close();
        } else {
            echo "<script>alert('Something went wrong. Please try again later.');</script>";
        }
    } else {
        echo "<script>alert('You must be logged in to add a course.'); window.location.href='login.php';</script>";
    }

    // ปิดการเชื่อมต่อฐานข้อมูล
    $conn->close();
}
?>

<!-- ฟอร์มให้ผู้ใช้กรอกรหัสวิชา -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Course</title>
    <link rel="stylesheet" href="add_course.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<style>
    body {
    font-family: 'Roboto', sans-serif;
    background-color: #f9f9f9;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
    padding: 20px;
}

form {
    background-color: white;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 400px;
}

.form-header {
    text-align: center;
    margin-bottom: 30px;
}

.form-header h2 {
    color: #4CAF50;
    font-size: 24px;
    margin-bottom: 10px;
}

.form-group {
    margin-bottom: 25px;
}

label {
    display: block;
    margin-bottom: 8px;
    color: #333;
    font-weight: 500;
}

input[type="text"] {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 25px;
    font-size: 16px;
    transition: all 0.3s ease;
    outline: none;
}

input[type="text"]:focus {
    border-color: #4CAF50;
    box-shadow: 0 0 5px rgba(76, 175, 80, 0.2);
}

button[type="submit"] {
    width: 100%;
    padding: 12px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 25px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

button[type="submit"]:hover {
    background-color: #45a049;
    transform: translateY(-2px);
}

.back-link {
    display: block;
    text-align: center;
    margin-top: 20px;
    color: #4CAF50;
    text-decoration: none;
    font-size: 14px;
}

.back-link:hover {
    text-decoration: underline;
}

</style>
<body>
    <form action="add_course.php" method="POST">
        <div class="form-header">
            <h2>เพิ่มรายวิชา</h2>
            <p>กรุณากรอกรหัสวิชาที่ต้องการลงทะเบียน</p>
        </div>
        
        <div class="form-group">
            <label for="course_code">รหัสวิชา:</label>
            <input type="text" name="course_code" id="course_code" required 
                   placeholder="กรอกรหัสวิชา เช่น CS101">
        </div>

        <button type="submit">เพิ่มรายวิชา</button>
        <a href="search.php" class="back-link">กลับไปหน้าหลัก</a>
    </form>
</body>
</html>

