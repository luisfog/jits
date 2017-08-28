<?php
	ini_set('display_errors', '0');
	
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
		$client = $result->fetch_assoc();
		
		$input = file_get_contents('php://input');
		
		$inputDec = fnDecrypt($input, $client["aes_key"], $client["aes_iv"]);
		$inputDec = substr($inputDec, 0, strrpos($inputDec, "}")+1);
		$jsonArray = json_decode($inputDec, true);
		
		$createValues = "";
		$insert = "";
		$values = "";
		foreach ($jsonArray as $key => $value) {
			$createValues .= "{$key} FLOAT(15,7) NOT NULL,";
			$insert .= "{$key},";
			$values .= "'{$value}',";
		}
		$createValues = substr($createValues, 0, -1);
		$insert = substr($insert, 0, -1);
		$values = substr($values, 0, -1);
		
		$sql = "SHOW TABLES LIKE 'client_".$_GET['con']."';";
		$result = $conn->query($sql);
		if ($result->num_rows == 0) {
			$sql = "CREATE TABLE client_".$_GET['con']." (
					id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
					creation TIMESTAMP,
					$createValues
					)";
			if ($conn->query($sql) !== TRUE) {
				header("HTTP/1.1 500 Internal Server Error");
				echo "Error creating client table.";
				return;
			}
		}
		
		$sql = "SELECT name, client_name, value, cond, target, time_target FROM alarms".
				" WHERE connection_key LIKE '".$_GET['con']."';";
		$result = $conn->query($sql);
		if ($result) {
			while($entry = $result->fetch_assoc()) {
				foreach ($jsonArray as $key => $value) {
					if($key == $entry["value"]){
						switch($entry["cond"]){
							case "1":
								if($value == $entry["target"]){
									if($entry["time_target"] == "0"){
										$sqlLast = "SELECT * FROM (SELECT ".$entry["value"]." FROM client_".$_GET['con'].
													" WHERE creation > DATE_SUB(NOW(), INTERVAL 1 HOUR)) as c".
													" WHERE ".$entry["value"]." == ".$entry["target"];
										$resultLast = $conn->query($sqlLast);
										if ($resultLast && $resultLast->num_rows == 0) {
											$msg = "Was detected an alarm in client '".$entry["client_name"]."'.\n\n";
											$msg .= "'".$entry["value"]."' is equal ".$entry["target"];
											sendMail($conn, $entry["name"], $msg);
										}
									}else{
										$sqlTime = "SELECT ".$entry["value"]." FROM client_".$_GET['con']." WHERE".
												" creation >= DATE_SUB(NOW(), INTERVAL ".$entry["time_target"]." MINUTE)".
												" AND ".$entry["value"]." != ".$entry["target"];
										$resultTime = $conn->query($sqlTime);
										if ($resultTime && $resultTime->num_rows == 0) {
											$sqlLast = "SELECT * FROM (SELECT ".$entry["value"]." FROM client_".$_GET['con'].
														" WHERE creation < DATE_SUB(NOW(), INTERVAL ".$entry["time_target"]." MINUTE)".
														" ORDER BY creation DESC LIMIT 1) as c WHERE ".$entry["value"]." == ".$entry["target"];
											$resultLast = $conn->query($sqlLast);
											if ($resultLast && $resultLast->num_rows == 0) {
												$msg = "Was detected an alarm in client '".$entry["client_name"]."'.\n\n";
												$msg .= "In the last ".$entry["time_target"]." minutes, '".$entry["value"]."' was equal than ".$entry["target"];
												sendMail($conn, $entry["name"], $msg);
											}
										}
									}
								}
							break;
							case "2":
								if($value != $entry["target"]){
									if($entry["time_target"] == "0"){
										$sqlLast = "SELECT * FROM (SELECT ".$entry["value"]." FROM client_".$_GET['con'].
													" WHERE creation > DATE_SUB(NOW(), INTERVAL 1 HOUR)) as c".
													" WHERE ".$entry["value"]." != ".$entry["target"];
										$resultLast = $conn->query($sqlLast);
										if ($resultLast && $resultLast->num_rows == 0) {
											$msg = "Was detected an alarm in client '".$entry["client_name"]."'.\n\n";
											$msg .= "'".$entry["value"]."' is different than ".$entry["target"];
											sendMail($conn, $entry["name"], $msg);
										}
									}else{
										$sqlTime = "SELECT ".$entry["value"]." FROM client_".$_GET['con']." WHERE".
												" creation >= DATE_SUB(NOW(), INTERVAL ".$entry["time_target"]." MINUTE)".
												" AND ".$entry["value"]." == ".$entry["target"];
										$resultTime = $conn->query($sqlTime);
										if ($resultTime && $resultTime->num_rows == 0) {
											$sqlLast = "SELECT * FROM (SELECT ".$entry["value"]." FROM client_".$_GET['con'].
														" WHERE creation < DATE_SUB(NOW(), INTERVAL ".$entry["time_target"]." MINUTE)".
														" ORDER BY creation DESC LIMIT 1) as c WHERE ".$entry["value"]." != ".$entry["target"];
											$resultLast = $conn->query($sqlLast);
											if ($resultLast && $resultLast->num_rows == 0) {
												$msg = "Was detected an alarm in client '".$entry["client_name"]."'.\n\n";
												$msg .= "In the last ".$entry["time_target"]." minutes, '".$entry["value"]."' was different than ".$entry["target"];
												sendMail($conn, $entry["name"], $msg);
											}
										}
									}
								}
							break;
							case "3":
								if($value < $entry["target"]){
									if($entry["time_target"] == "0"){
										$sqlLast = "SELECT * FROM (SELECT ".$entry["value"]." FROM client_".$_GET['con'].
													" WHERE creation > DATE_SUB(NOW(), INTERVAL 1 HOUR)) as c".
													" WHERE ".$entry["value"]." < ".$entry["target"];
										$resultLast = $conn->query($sqlLast);
										if ($resultLast && $resultLast->num_rows == 0) {
											$msg = "Was detected an alarm in client '".$entry["client_name"]."'.\n\n";
											$msg .= "'".$entry["value"]."' is less than ".$entry["target"];
											sendMail($conn, $entry["name"], $msg);
										}
									}else{
										$sqlTime = "SELECT ".$entry["value"]." FROM client_".$_GET['con']." WHERE".
												" creation >= DATE_SUB(NOW(), INTERVAL ".$entry["time_target"]." MINUTE)".
												" AND ".$entry["value"]." >= ".$entry["target"];
										$resultTime = $conn->query($sqlTime);
										if ($resultTime && $resultTime->num_rows == 0) {
											$sqlLast = "SELECT * FROM (SELECT ".$entry["value"]." FROM client_".$_GET['con'].
														" WHERE creation < DATE_SUB(NOW(), INTERVAL ".$entry["time_target"]." MINUTE)".
														" ORDER BY creation DESC LIMIT 1) as c WHERE ".$entry["value"]." < ".$entry["target"];
											$resultLast = $conn->query($sqlLast);
											if ($resultLast && $resultLast->num_rows == 0) {
												$msg = "Was detected an alarm in client '".$entry["client_name"]."'.\n\n";
												$msg .= "In the last ".$entry["time_target"]." minutes, '".$entry["value"]."' was less than ".$entry["target"];
												sendMail($conn, $entry["name"], $msg);
											}
										}
									}
								}
							break;
							case "4":
								if($value > $entry["target"]){
									if($entry["time_target"] == "0"){
										$sqlLast = "SELECT * FROM (SELECT ".$entry["value"]." FROM client_".$_GET['con'].
													" WHERE creation > DATE_SUB(NOW(), INTERVAL 1 HOUR)) as c".
													" WHERE ".$entry["value"]." > ".$entry["target"];
										$resultLast = $conn->query($sqlLast);
										if ($resultLast && $resultLast->num_rows == 0) {
											$msg = "Was detected an alarm in client '".$entry["client_name"]."'.\n\n";
											$msg .= "'".$entry["value"]."' is greater than ".$entry["target"];
											sendMail($conn, $entry["name"], $msg);
										}
									}else{
										$sqlTime = "SELECT ".$entry["value"]." FROM client_".$_GET['con']." WHERE".
												" creation >= DATE_SUB(NOW(), INTERVAL ".$entry["time_target"]." MINUTE)".
												" AND ".$entry["value"]." <= ".$entry["target"];
										$resultTime = $conn->query($sqlTime);
										if ($resultTime && $resultTime->num_rows == 0) {
											$sqlLast = "SELECT * FROM (SELECT ".$entry["value"]." FROM client_".$_GET['con'].
														" WHERE creation < DATE_SUB(NOW(), INTERVAL ".$entry["time_target"]." MINUTE)".
														" ORDER BY creation DESC LIMIT 1) as c WHERE ".$entry["value"]." > ".$entry["target"];
											$resultLast = $conn->query($sqlLast);
											if ($resultLast && $resultLast->num_rows == 0) {
												$msg = "Was detected an alarm in client '".$entry["client_name"]."'.\n\n";
												$msg .= "In the last ".$entry["time_target"]." minutes, '".$entry["value"]."' was greater than ".$entry["target"];
												sendMail($conn, $entry["name"], $msg);
											}
										}
									}
								}
							break;
							case "5":
								if($value <= $entry["target"]){
									if($entry["time_target"] == "0"){
										$sqlLast = "SELECT * FROM (SELECT ".$entry["value"]." FROM client_".$_GET['con'].
													" WHERE creation > DATE_SUB(NOW(), INTERVAL 1 HOUR)) as c".
													" WHERE ".$entry["value"]." <= ".$entry["target"];
										$resultLast = $conn->query($sqlLast);
										if ($resultLast && $resultLast->num_rows == 0) {
											$msg = "Was detected an alarm in client '".$entry["client_name"]."'.\n\n";
											$msg .= "'".$entry["value"]."' is less or equal than ".$entry["target"];
											sendMail($conn, $entry["name"], $msg);
										}
									}else{
										$sqlTime = "SELECT ".$entry["value"]." FROM client_".$_GET['con']." WHERE".
												" creation >= DATE_SUB(NOW(), INTERVAL ".$entry["time_target"]." MINUTE)".
												" AND ".$entry["value"]." > ".$entry["target"];
										$resultTime = $conn->query($sqlTime);
										if ($resultTime && $resultTime->num_rows == 0) {
											$sqlLast = "SELECT * FROM (SELECT ".$entry["value"]." FROM client_".$_GET['con'].
														" WHERE creation < DATE_SUB(NOW(), INTERVAL ".$entry["time_target"]." MINUTE)".
														" ORDER BY creation DESC LIMIT 1) as c WHERE ".$entry["value"]." <= ".$entry["target"];
											$resultLast = $conn->query($sqlLast);
											if ($resultLast && $resultLast->num_rows == 0) {
												$msg = "Was detected an alarm in client '".$entry["client_name"]."'.\n\n";
												$msg .= "In the last ".$entry["time_target"]." minutes, '".$entry["value"]."' was less or equal than ".$entry["target"];
												sendMail($conn, $entry["name"], $msg);
											}
										}
									}
								}
							break;
							case "6":
								if($value >= $entry["target"]){
									if($entry["time_target"] == "0"){
										$sqlLast = "SELECT * FROM (SELECT ".$entry["value"]." FROM client_".$_GET['con'].
													" WHERE creation > DATE_SUB(NOW(), INTERVAL 1 HOUR)) as c".
													" WHERE ".$entry["value"]." >= ".$entry["target"];
										$resultLast = $conn->query($sqlLast);
										if ($resultLast && $resultLast->num_rows == 0) {
											$msg = "Was detected an alarm in client '".$entry["client_name"]."'.\n\n";
											$msg .= "'".$entry["value"]."' is greater or equal than ".$entry["target"];
											sendMail($conn, $entry["name"], $msg);
										}
									}else{
										$sqlTime = "SELECT ".$entry["value"]." FROM client_".$_GET['con']." WHERE".
												" creation >= DATE_SUB(NOW(), INTERVAL ".$entry["time_target"]." MINUTE)".
												" AND ".$entry["value"]." < ".$entry["target"];
										$resultTime = $conn->query($sqlTime);
										if ($resultTime && $resultTime->num_rows == 0) {
											$sqlLast = "SELECT * FROM (SELECT ".$entry["value"]." FROM client_".$_GET['con'].
														" WHERE creation < DATE_SUB(NOW(), INTERVAL ".$entry["time_target"]." MINUTE)".
														" ORDER BY creation DESC LIMIT 1) as c WHERE ".$entry["value"]." >= ".$entry["target"];
											$resultLast = $conn->query($sqlLast);
											if ($resultLast && $resultLast->num_rows == 0) {
												$msg = "Was detected an alarm in client '".$entry["client_name"]."'.\n\n";
												$msg .= "In the last ".$entry["time_target"]." minutes, '".$entry["value"]."' was greater or equal than ".$entry["target"];
												sendMail($conn, $entry["name"], $msg);
											}
										}
									}
								}
							break;
						}
					}
				}
			}
		}
		
		$sql = "INSERT INTO client_".$_GET['con']." (creation, $insert)
				VALUES (NOW(), $values)";
		if ($conn->query($sql) === TRUE) {
			header("HTTP/1.1 201 Created");
			echo "Data saved successfully";
			return;
		} else {
			header("HTTP/1.1 500 Internal Server Error");
			echo "Error saving data, invalid json.";
			return;
		}
	}
	
	header("HTTP/1.1 500 Internal Server Error");
	echo "Unknown order.";
	return;
	
	function fnDecrypt($input, $aesKey, $aesIV)
	{
		return rtrim(
			mcrypt_decrypt(
				MCRYPT_RIJNDAEL_128,
				$aesKey, 
				base64_decode($input),
				MCRYPT_MODE_CBC, 
				base64_decode($aesIV)
			), "\0"
		);
	}
	
	function sendMail($conn, $subject, $message)
	{
		$sql = "SELECT * FROM webUsers LIMIT 1";
		$result = $conn->query($sql);

		if ($result && $result->num_rows > 0) {
			$lastRow = $result->fetch_assoc();
			
			$to_address = $lastRow["email"];
			$headers = "From: JITS <no-reply@jits.com>\n";		
			
			$mailsent = mail($to_address, $subject, $message, $headers);

			return ($mailsent)?(true):(false);
		}
		return false;
	}
?>