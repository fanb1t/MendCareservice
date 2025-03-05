<?php
include "connectDB.php";
$sql = "DELETE FROM STUDENT WHERE STU_ID = '3615108'";
$result = mysqli_query($conn,$sql);
try {
mysqli_query($conn,$sql);
echo "Record deleted successfully";
} catch (Exception $e) {
echo mysqli_error($conn);
}
$conn->close();
?>