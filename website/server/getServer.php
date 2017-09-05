<?php
	ini_set('display_errors', '0');
	session_start();
	if(!isset($_SESSION['name'])){
		header('Location: ../login.html' );
		return;
	}
	
	$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	
	$actual_link = substr($actual_link, 0, strrpos($actual_link, "/"));
	$actual_link = substr($actual_link, 0, strrpos($actual_link, "/"))."/publisher.php";
	
	header("HTTP/1.1 200 OK");
	echo $actual_link;
	return;
?>