<script language="JavaScript" type="text/javascript">
function checkUpdate(){
return confirm('Are you sure to update the record?');
}
</script>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
$name = $_POST["name"];
include "connectDB.php";
$sql = "SELECT * FROM STUDENT WHERE STU_Name = '$name'";
$result = mysqli_query($conn,$sql);
if (mysqli_num_rows( $result ) >0) {
$row = mysqli_fetch_assoc($result);
$name = $row['STU_Name'];
$major = $row['Major'];
$total = $row['Total_Credits'];
} else {
header("Location:update_search_id.php?message='$id is not found, Please search new id.'");
}
mysqli_close($conn);
}
?>
<html>
<form action="update_data.php" method="post">
<table width="500" border="1">
<tr>
<th width="100">Student Id:</th>
<td width="300"><input type="text" name="id" value = <?php echo $id ?> readonly></td>
</tr>
<tr>
<th width="100">Name:</th>
<td width="300"><input type="text" name="name" value = <?php echo $name ?> ></td>
</tr>
<tr>
<th width="100">Major:</th>
<td width="300"><input type="text" name="major" value = <?php echo $major ?>></td>
</tr>
<tr>
<th width="100">Total credits:</th>
<td width="300"><input type="text" name="total" value = <?php echo $total ?>></td>
</tr>
</table>
<button type="submit" onclick="return checkUpdate()">Update</button>
</form>
</html>