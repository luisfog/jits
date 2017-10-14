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
			$sql = "DROP TABLE client_$connectionKey";
		
			if ($conn->query($sql) === TRUE) {
				$conn->close();
				header("HTTP/1.1 200 OK");
				return;
			} else {
				header("HTTP/1.1 500 Internal Server Error");
				echo "Error deleting.";
				include("./logs.php");
				insertToLog("deleteClient.php", "Error deleting: " . $conn->error);
				$conn->close();
				return;
			}
		} else {
			header("HTTP/1.1 500 Internal Server Error");
			echo "Error deleting.";
			include("./logs.php");
			insertToLog("deleteClient.php", "Error deleting: " . $conn->error);
			$conn->close();
			return;
		}
	}
	
	header("HTTP/1.1 500 Internal Server Error");
	echo "Unknown inputs.";
	include("./logs.php");
	insertToLog("deleteClient.php", "Wrong GET request parameters.");
	return;
?>