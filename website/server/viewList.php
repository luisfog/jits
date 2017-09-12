<?php
	ini_set('display_errors', '0');
	session_start();
	if(!isset($_SESSION['name'])){
		header('Location: ../login.html' );
		return;
	}
	
	include("./dbinfo.php");

	$conn = new mysqli($databaseHost, $user, $pass, $database);
	if ($conn->connect_error) {
		header("HTTP/1.1 500 Internal Server Error");
		echo "Connection failed: " . $conn->connect_error;
		include("./server/logs.php");
		insertToLog("viewList.php", "Connection failed: " . $conn->connect_error);
		return;
	}
	
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