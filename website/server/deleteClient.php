<?php
	ini_set('display_errors', '0');
	session_start();
	if(!isset($_SESSION['name'])){
		header('Location: ../login.html' );
		return;
	}
	
	if( isset($_POST['connectionKey'])){
		
		$connectionKey = $_POST['connectionKey'];
		
		require("./database.php");
		$conn = getConnectionBack();
		
		$sql = "DELETE FROM clients WHERE connection_key like'$connectionKey'";
		
		if ($conn->query($sql) === TRUE) {
			$conn->close();
			header("HTTP/1.1 200 OK");
			return;
		} else {
			$conn->close();
			header("HTTP/1.1 500 Internal Server Error");
			echo "Error deleting.";
			include("./server/logs.php");
			insertToLog("deleteClient.php", "Error deleting: " . $conn->error);
			return;
		}
	}
	
	header("HTTP/1.1 500 Internal Server Error");
	echo "Unknown inputs.";
	include("./server/logs.php");
	insertToLog("deleteClient.php", "Wrong GET request parameters.");
	return;
?>