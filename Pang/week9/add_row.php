<?php
$id = $_POST["id"];
$name = $_POST["name"];
$major = $_POST["major"];
$total = $_POST["total"];
include "connectDB.php";
$sql = "INSERT INTO STUDENT (STU_ID, STU_Name, Major, Total_Credits)
VALUES ('$id','$name','$major',$total)";
try {
mysqli_query($conn,$sql);
echo "Record add successfully";
} catch (Exception $e) {
echo mysqli_error($conn);
}
mysqli_close($conn);
?>