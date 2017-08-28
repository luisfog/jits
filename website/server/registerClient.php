<?php
	ini_set('display_errors', '0');
	session_start();
	if(!isset($_SESSION['name'])){
		header('Location: ../login.html' );
		return;
	}
	
	if( isset($_POST['name']) && isset($_POST['aes']) && isset($_POST['type']) && isset($_POST['connection']) && isset($_POST['aes_key']) && isset($_POST['aes_iv']) ){
		$name = $_POST["name"];
		$aes = $_POST["aes"];
		$type = $_POST["type"];
		$connection = $_POST["connection"];
		$aes_key = $_POST["aes_key"];
		$aes_iv = $_POST["aes_iv"];

		include("./dbinfo.php");

		$conn = new mysqli($databaseHost, $user, $pass, $database);
		if ($conn->connect_error) {
			header("HTTP/1.1 500 Internal Server Error");
			echo "Connection failed: " . $conn->connect_error;
			return;
		}
		$sql = "INSERT INTO clients (creation, name, aes, type, connection_key, aes_key, aes_iv)
				VALUES (NOW(), '$name', '$aes', '$type', '$connection', '$aes_key', '$aes_iv')";
		if ($conn->query($sql) === TRUE) {
			header("HTTP/1.1 201 Created");
			echo "Client created successfully";
			$conn->close();
			return;
		} else {
			header("HTTP/1.1 500 Internal Server Error");
			echo "Error creating client: " . $conn->error;
			$conn->close();
			return;
		}
	}
	header("HTTP/1.1 500 Internal Server Error");
	echo "Unknown order.";
	return;
?>