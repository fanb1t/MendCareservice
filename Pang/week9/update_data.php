<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
$id = $_POST["id"];
$name = $_POST["name"];
$major = $_POST["major"];
$total = $_POST["total"];
include "connectDB.php";
$sql = "UPDATE STUDENT SET STU_Name = '$name', Major = '$major', Total_Credits = $total
WHERE STU_ID = '$id'";
try {
mysqli_query($conn,$sql);
echo "Record updated successfully";
} catch (Exception $e) {
echo mysqli_error($conn);
}
$conn->close();
}
?>