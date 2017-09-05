<?php
	if( isset($_GET['con']) ){
		
		include("./server/dbinfo.php");
		
		$conn = new mysqli($databaseHost, $user, $pass, $database);
		if ($conn->connect_error) {
			header("HTTP/1.1 500 Internal Server Error");
			echo "Connection failed: " . $conn->connect_error;
			return;
		}
		
		$sql = "SELECT * FROM clients WHERE connection_key LIKE '".$_GET['con']."'";
		$result = $conn->query($sql);

		if ($result->num_rows == 0) {
			header("HTTP/1.1 500 Internal Server Error");
			echo "No known client.";
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
			$conn->close();
			header("HTTP/1.1 500 Internal Server Error");
			echo "Error updating.";
			return;
		}
		
		
		
		
	}
?>