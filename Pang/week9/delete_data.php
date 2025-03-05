<?php
include "connectDB.php";
$id = $_GET["id"];
$sql = "DELETE FROM STUDENT WHERE STU_ID = '$id'";
$result = mysqli_query($conn,$sql);
try {
mysqli_query($conn,$sql);
echo "Record deleted successfully";
} catch (Exception $e) {
echo mysqli_error($conn);
}
$conn->close();
header("Location:delete_button.php");
?>