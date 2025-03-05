<?php
include "connectDB.php";
$sql = "SELECT * FROM STUDENT";
$result = mysqli_query($conn,$sql);
?>
<script language="JavaScript" type="text/javascript">
function checkDelete(){
return confirm('Are you sure to delete the record?');
}
</script>
<html>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<style>
    i {
        position: center;
    }
</style>
<form action="test.php" method="post">
<table width="1000" border="1">
<tr>
<th width="100">Student Id</th>
<td width="300">Student Name</td>
<td width="300">Major</td>
<td width="300">Total Credits</td>
<td width="300">Delete</td>
</tr>
<?php
while ($row = mysqli_fetch_assoc($result)) { ?>
<tr>
<th width="100"> <?php echo $row['STU_ID']?> </th>
<td width="300"> <?php echo $row['STU_Name']?> </td>
<td width="300"> <?php echo $row['Major']?> </td>
<td width="300"> <?php echo $row['Total_Credits']?> </td>
<td> <a href="delete_data.php?id=<?php echo $row['STU_ID']; ?>" onclick="return checkDelete()">         
    <i class="fas fa-trash-alt" style="color:red;"></i> <!-- ใช้ไอคอนถังขยะ --></a> </td>
</tr>
<?php } ?>
</table>
</form>
</html>