<?php
	ini_set('display_errors', '0');
	session_start();
	if(!isset($_SESSION['name'])){
		header('Location: ../login.html' );
		return;
	}
	
	if( isset($_POST['name']) && isset($_POST['list'])){
		$name = str_replace('=', '', base64_encode($_POST["name"]));
		$list = $_POST["list"];

		include("./dbinfo.php");

		$conn = new mysqli($databaseHost, $user, $pass, $database);
		if ($conn->connect_error) {
			header("HTTP/1.1 500 Internal Server Error");
			echo "Connection failed: " . $conn->connect_error;
			include("./server/logs.php");
			insertToLog("registerView.php", "Connection failed: " . $conn->connect_error);
			return;
		}
		
		$arr = json_decode($list);
		for($i = 0; $i < count($arr); $i++){
			$obj = (Array)$arr[$i];
			
			$obj["clientName"] = str_replace('=', '', base64_encode($obj["clientName"]));
			$obj["value"] = str_replace('=', '', base64_encode($obj["value"]));
			$obj["columnName"] = str_replace('=', '', base64_encode($obj["columnName"]));
			
			$sql = "INSERT INTO views (name, connection_key, client_name, value, column_name)".
				"VALUES ('$name', '".$obj["connectionKey"]."', '".$obj["clientName"]."', '".$obj["value"]."', '".$obj["columnName"]."')";
			
			if ($conn->query($sql) !== TRUE) {
				header("HTTP/1.1 500 Internal Server Error");
				echo "Error creating view: " . $conn->error;
				include("./server/logs.php");
				insertToLog("registerView.php", "Error creating alarm: " . $conn->error);
				$conn->close();
				return;
			}
		}
		
		header("HTTP/1.1 200 OK");
		echo "View created successfully";
		$conn->close();
		return;
	}
	header("HTTP/1.1 500 Internal Server Error");
	echo "Unknown order.";
	include("./server/logs.php");
	insertToLog("registerView.php", "Wrong GET request parameters.");
	return;
?>