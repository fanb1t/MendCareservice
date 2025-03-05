<?php
$servername = "172.18.111.42";
$username = "6520310138";
$password = "6520310138";
$dbname = "6520310138_mysql";

//Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
   die("Connection failed: " . $conn->connect_error);
}
?>
