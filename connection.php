<?php
$server = "localhost";
$username = "vitolama_bot";
$password = "Joyoboyo19";
$db_name = "vitolama_bot";
$conn = mysqli_connect($server,$username,$password,$db_name);

if(mysqli_connect_error()) 
	die("Connection failed: " . mysqliconnect_error());
?>