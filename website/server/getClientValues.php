<?php
	ini_set('display_errors', '0');
	session_start();
	if(!isset($_SESSION['name'])){
		header('Location: ../login.html' );
		return;
	}
	
	if( isset($_REQUEST['connectionKey'])){

		$connectionKey = $_REQUEST['connectionKey'];

		require("./database.php");
		$conn = getConnectionBack();
		
		$sql = "SELECT * FROM client_$connectionKey LIMIT 1";
		$result = $conn->query($sql);
		if($result == null){
			header("HTTP/1.1 200 OK");
			echo "{}";
			return;
		}
		$lastRow = $result->fetch_assoc();
		
		foreach ($lastRow as $key => $value) {
			if($key <> "id" && $key <> "creation")
				$rows[] = base64_decode($key);
		}
		
		header("HTTP/1.1 200 OK");
		if(isset($rows))
			print json_encode($rows);
		else
			echo "{}";
		$conn->close();
		return;
	}
	
	header("HTTP/1.1 500 Internal Server Error");
	echo "Unknown inputs.";
	include("./server/logs.php");
	insertToLog("getClientValues.php", "Wrong GET request parameters.");
	return;
?>