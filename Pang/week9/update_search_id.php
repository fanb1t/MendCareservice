<?php
$message = $_GET["message"];
echo $message;
?>
<html>
<form action="update_form.php" method="post">
<table width="500" border="1">
<tr>
<th width="100">Student Id:</th>
<td width="300"><input type="text" name="id" value = <?php echo $id ?> ></td>
</tr>
</table>
<button type="submit">update</button>
</form>
</html>