<?php
	ini_set('display_errors', '0');
	session_start();
	if(!isset($_SESSION['name'])){
		header('Location: ../login.html' );
		return;
	}
	
	require("./database.php");
	$conn = getConnectionBack();
	
	$sql = "SELECT DISTINCT(name) FROM views";
	$result = $conn->query($sql);
	
	$arr = array();
	$i = 0;

	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$arr[$i++]["name"] = base64_decode($row["name"]);
		}
	}
	
	$conn->close();
	
	header("HTTP/1.1 200 OK");
	echo json_encode($arr);
	return;
?>