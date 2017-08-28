<?php
	ini_set('display_errors', '0');
	session_start();
	if(!isset($_SESSION['name'])){
		header('Location: ../login.html' );
		return;
	}
	
	if( isset($_POST['name']) && isset($_POST['oldPassword']) && isset($_POST['newPassword']) ){
		
		$name = $_POST['name'];
		$oldPassword = $_POST['oldPassword'];
		$newPassword = $_POST['newPassword'];
		
		include("./dbinfo.php");

		$conn = new mysqli($databaseHost, $user, $pass, $database);
		if ($conn->connect_error) {
			header("HTTP/1.1 500 Internal Server Error");
			echo "Connection failed: " . $conn->connect_error;
			return;
		}
		
		$sql = "SELECT pass FROM webUsers WHERE user LIKE '$name'";
		$result = $conn->query($sql);

		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();
			$hash = $row["pass"];
			
			/*if (!password_verify($oldPassword, $hash)) {
				header("HTTP/1.1 403 Forbidden");
				return;
			}*/
					
			$hash = password_hash($newPassword, PASSWORD_DEFAULT);
			$sql = "UPDATE webUsers SET pass='$hash' WHERE user LIKE '$name'";
			echo $sql;
			
			if ($conn->query($sql) === TRUE) {
				$conn->close();
				header("HTTP/1.1 200 OK");
				return;
			} else {
				$conn->close();
				header("HTTP/1.1 500 Internal Server Error");
				echo "Error deliting.";
				return;
			}
		}
		
		$conn->close();
				header("HTTP/1.1 500 Internal Server Error");
				echo "Error deliting.";
				return;
	}
	
	header("HTTP/1.1 500 Internal Server Error");
	echo "Unknown inputs.";
	return;
?>