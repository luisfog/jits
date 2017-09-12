<?php
	ini_set('display_errors', '0');
	session_start();
	if(!isset($_SESSION['name'])){
		header('Location: ../login.html' );
		return;
	}
	
	if( isset($_POST['name'])){
		
		$name = $_POST["name"];
		
		include("./dbinfo.php");

		$conn = new mysqli($databaseHost, $user, $pass, $database);
		if ($conn->connect_error) {
			header("HTTP/1.1 500 Internal Server Error");
			echo "Connection failed: " . $conn->connect_error;
			include("./server/logs.php");
			insertToLog("deleteView.php", "Connection failed: " . $conn->connect_error);
			return;
		}
		
		$sql = "DELETE FROM views WHERE name like'$name'";
		
		if ($conn->query($sql) === TRUE) {
			$conn->close();
			header("HTTP/1.1 200 OK");
			return;
		} else {
			$conn->close();
			header("HTTP/1.1 500 Internal Server Error");
			echo "Error deleting.";
			include("./server/logs.php");
			insertToLog("deleteView.php", "Error deleting: " . $conn->error);
			return;
		}
	}
	
	header("HTTP/1.1 500 Internal Server Error");
	echo "Unknown inputs.";
	include("./server/logs.php");
	insertToLog("deleteView.php", "Wrong GET request parameters.");
	return;
?>