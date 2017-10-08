<?php
	ini_set('display_errors', '0');
	session_start();
	if(!isset($_SESSION['name'])){
		header('Location: ../login.html' );
		return;
	}
	
	if(isset($_POST['name']) && isset($_POST['table'])){
		
		$name = str_replace('=', '', base64_encode($_POST["name"]));

		require("./database.php");
		$conn = getConnectionBack();
		
		$sql = "SELECT * FROM ".$_POST['table']." WHERE name LIKE '".$name."'";
		$result = $conn->query($sql);

		if ($result->num_rows > 0) {
			header("HTTP/1.1 500 Internal Server Error");
			echo "Name already taken.";
			include("./server/logs.php");
			insertToLog("checkName.php", "Name already taken.");
			$conn->close();
			return;
		}else{
			header("HTTP/1.1 202 Accepted");
			echo "Nama available.";
			$conn->close();
			return;
		}
	}
	
	header("HTTP/1.1 500 Internal Server Error");
	echo "Unknown inputs.";
	include("./server/logs.php");
	insertToLog("checkName.php", "Wrong GET request parameters.");
	return;
?>