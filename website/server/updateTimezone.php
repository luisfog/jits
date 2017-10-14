<?php
	ini_set('display_errors', '0');
	session_start();
	if(!isset($_SESSION['name'])){
		header('Location: ../login.html' );
		return;
	}
	
	if( isset($_POST['timezone']) ){
		
		$name = $_SESSION['name'];
		$timezone = $_POST['timezone'];
		
		require("./database.php");
		$conn = getConnectionBack();
		
		$sql = "UPDATE webUsers SET timezoneset='$timezone' WHERE user LIKE '$name'";
		
		if ($conn->query($sql) === TRUE) {
			$conn->close();
			header("HTTP/1.1 200 OK");
			return;
		} else {
			header("HTTP/1.1 500 Internal Server Error");
			echo "Error updating.";
			include("./logs.php");
			insertToLog("updateTimezone.php", "Error updating: " . $conn->error);
			$conn->close();
			return;
		}
	}
	
	header("HTTP/1.1 500 Internal Server Error");
	echo "Unknown inputs.";
	include("./logs.php");
	insertToLog("updateTimezone.php", "Wrong GET request parameters.");
	return;
?>