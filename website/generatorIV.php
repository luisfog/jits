<?php
	if( isset($_GET['con']) ){
		
		include("./server/dbinfo.php");
		
		$conn = new mysqli($databaseHost, $user, $pass, $database);
		if ($conn->connect_error) {
			header("HTTP/1.1 500 Internal Server Error");
			echo "Connection failed: " . $conn->connect_error;
			include("./server/logs.php");
			insertToLogRoot("generatorIV.php", "Connection failed: " . $conn->connect_error);
			return;
		}
		
		$sql = "SELECT * FROM clients WHERE connection_key LIKE '".$_GET['con']."'";
		$result = $conn->query($sql);

		if ($result->num_rows == 0) {
			header("HTTP/1.1 500 Internal Server Error");
			echo "No known client.";
			include("./server/logs.php");
			insertToLogRoot("generatorIV.php", "Connection key is invalid: ".$_GET['con']);
			return;
		}
		
		$iv = mcrypt_create_iv(
				mcrypt_get_iv_size(
					MCRYPT_RIJNDAEL_128, 
					MCRYPT_MODE_ECB
				), 
				MCRYPT_RAND);
		
		$ivBase =  base64_encode($iv);
		
		$sql = "UPDATE clients SET aes_iv='$ivBase', date_last_iv = NOW() WHERE connection_key LIKE '".$_GET['con']."'";
		if ($conn->query($sql) === TRUE) {
			$conn->close();
			header("HTTP/1.1 200 OK");
			echo $ivBase;
			return;
		} else {
			header("HTTP/1.1 500 Internal Server Error");
			echo "Error updating.";
			include("./server/logs.php");
			insertToLogRoot("generatorIV.php", "Error updating: " . $conn->error);
			$conn->close();
			return;
		}
	}
	header("HTTP/1.1 500 Internal Server Error");
	echo "Unknown order.";
	include("./server/logs.php");
	insertToLogRoot("generatorIV.php", "Wrong GET request parameters.");
	return;
?>