<?php
include "connectDB.php";
$sql = "INSERT INTO STUDENT (STU_ID, STU_Name, Major, Total_Credits)
VALUES ('41201', 'Bulan', 'Stat', 144)";
try {
mysqli_query($conn,$sql);
echo "Record add successfully";
} catch (Exception $e) {
echo mysqli_error($conn);
}
mysqli_close($conn);
?>
<html>
<form action="add_row.php" method="post">
<table width="500" border="1">
<tr>
<th width="100">Student Id:</th>
<td width="300"><input type="text" name="id" size="5"></td>
</tr>
<tr>
<th width="100">Name:</th>
<td width="300"><input type="text" name="name" ></td>
</tr>
<tr>
<th width="100">Major:</th>
<td width="300"><input type="text" name="major" ></td>
</tr>
<tr>
<th width="100">Total credits:</th>
<td width="300"><input type="text" name="total" size="5"></td>
</tr>
</table>
<button type="submit">Add</button>
</form>
</html>