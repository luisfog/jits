<?php
	ini_set('display_errors', '0');
	session_start();
	if(!isset($_SESSION['name'])){
		header('Location: ../login.html' );
		return;
	}
	
	if( isset($_POST['name'])){
		
		$name = str_replace('=', '', base64_encode($_POST["name"]));
		
		require("./database.php");
		$conn = getConnectionBack();
		
		$sql = "DELETE FROM alarms WHERE name like'$name'";
		
		if ($conn->query($sql) === TRUE) {
			$conn->close();
			header("HTTP/1.1 200 OK");
			return;
		} else {
			header("HTTP/1.1 500 Internal Server Error");
			echo "Error deleting.";
			include("./logs.php");
			insertToLog("deleteAlarm.php", "Error deleting: " . $conn->error);
			$conn->close();
			return;
		}
	}
	
	header("HTTP/1.1 500 Internal Server Error");
	echo "Unknown inputs.";
	include("./logs.php");
	insertToLog("deleteAlarm.php", "Wrong GET request parameters.");
	return;
?>