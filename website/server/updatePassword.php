<?php
	ini_set('display_errors', '0');
	session_start();
	if(!isset($_SESSION['name'])){
		header('Location: ../login.html' );
		return;
	}
	
	if( isset($_POST['oldPassword']) && isset($_POST['newPassword']) ){
		
		$name = $_SESSION['name'];
		$oldPassword = $_POST['oldPassword'];
		$newPassword = $_POST['newPassword'];
		
		require("./database.php");
		$conn = getConnectionBack();
		
		$sql = "SELECT pass FROM webUsers WHERE user LIKE '$name'";
		$result = $conn->query($sql);

		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();
			$hash = $row["pass"];
				
			$hash = password_hash($newPassword, PASSWORD_DEFAULT);
			$sql = "UPDATE webUsers SET pass='$hash' WHERE user LIKE '$name'";
			echo $sql;
			
			if ($conn->query($sql) === TRUE) {
				$conn->close();
				header("HTTP/1.1 200 OK");
				return;
			} else {
				header("HTTP/1.1 500 Internal Server Error");
				echo "Error updating.";
				include("./logs.php");
				insertToLog("updatePassword.php", "Error updating: " . $conn->error);
				$conn->close();
				return;
			}
		}
		
		header("HTTP/1.1 500 Internal Server Error");
		echo "Error updating.";
		include("./logs.php");
		insertToLog("updatePassword.php", "No user with that name");
		$conn->close();
		return;
	}
	
	header("HTTP/1.1 500 Internal Server Error");
	echo "Unknown inputs.";
	include("./logs.php");
	insertToLog("updatePassword.php", "Wrong GET request parameters.");
	return;
?>