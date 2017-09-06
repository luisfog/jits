<?php
	ini_set('display_errors', '0');
	session_start();
	if(!isset($_SESSION['name'])){
		header('Location: ../login.html' );
		return;
	}
	
	if( isset($_POST['name']) && isset($_POST['connectionKey']) && isset($_POST['clientName']) && isset($_POST['value'])
		&& isset($_POST['condition']) && isset($_POST['target']) && isset($_POST['timeExecution'])){
			
		$name = str_replace('=', '', base64_encode($_POST["name"]));
		$connectionKey = $_POST["connectionKey"];
		$clientName = str_replace('=', '', base64_encode($_POST["clientName"]));
		$value = str_replace('=', '', base64_encode($_POST["value"]));
		$condition = $_POST["condition"];
		$target = $_POST["target"];
		$timeExecution = $_POST["timeExecution"];
		
		if($timeExecution == "")
			$timeExecution = "0";
		
		include("./dbinfo.php");

		$conn = new mysqli($databaseHost, $user, $pass, $database);
		if ($conn->connect_error) {
			header("HTTP/1.1 500 Internal Server Error");
			echo "Connection failed: " . $conn->connect_error;
			return;
		}
		$sql = "INSERT INTO alarms (name, connection_key, client_name, value, cond, target, time_target)
				VALUES ('$name', '$connectionKey', '$clientName', '$value', $condition, $target, $timeExecution)";
				
		if ($conn->query($sql) === TRUE) {
			header("HTTP/1.1 200 OK");
			echo "Alarm created successfully";
			$conn->close();
			return;
		} else {
			header("HTTP/1.1 500 Internal Server Error");
			echo "Error creating alarm: " . $conn->error;
			$conn->close();
			return;
		}
	}
	header("HTTP/1.1 500 Internal Server Error");
	echo "Unknown order.";
	return;
?>