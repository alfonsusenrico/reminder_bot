<?php
$server = "localhost";
$username = "";
$password = "";
$db_name = "";
$conn = mysqli_connect($server,$username,$password,$db_name);

if(mysqli_connect_error()) 
	die("Connection failed: " . mysqliconnect_error());
?>
