<?php
	if( isset($_GET['con']) ){
		
		include("./server/dbinfo.php");
		
		$conn = new mysqli($databaseHost, $user, $pass, $database);
		if ($conn->connect_error) {
			header("HTTP/1.1 500 Internal Server Error");
			echo "Connection failed: " . $conn->connect_error;
			include("./server/logs.php");
			insertToLog("publisher.php", "Connection failed: " . $conn->connect_error);
			return;
		}
		
		$sql = "SELECT *, NOW() as now FROM clients WHERE connection_key LIKE '".$_GET['con']."'";
		$result = $conn->query($sql);

		if ($result->num_rows == 0) {
			header("HTTP/1.1 500 Internal Server Error");
			echo "No known client.";
			include("./server/logs.php");
			insertToLog("publisher.php", "The connection key is not in the database.");
			return;
		}
		$client = $result->fetch_assoc();
		
		$ivDate = strtotime($client["date_last_iv"]);
		$nowDate = strtotime($client["now"]);
		if($client["date_last_iv"] == "" || round(abs($nowDate - $ivDate) / 60,2) > 5 ) {
			header("HTTP/1.1 500 Internal Server Error");
			echo "iv expired, please generate a new iv.";
			include("./server/logs.php");
			insertToLog("publisher.php", "The iv expired, please generate a new iv");
			return;
		}
		
		$input = file_get_contents('php://input');
		
		$inputDec = fnDecrypt($input, $client["aes_key"], $client["aes_iv"]);
		$inputDec = substr($inputDec, 0, strrpos($inputDec, "}")+1);
		$jsonArray = json_decode($inputDec, true);
		if($jsonArray === null) {
			header("HTTP/1.1 500 Internal Server Error");
			echo "Error decoding JSON.";
			include("./server/logs.php");
			insertToLog("publisher.php", "Error decoding JSON.");
			return;
		}
		
		$createValues = "";
		$insert = "";
		$values = "";
		foreach ($jsonArray as $key => $value) {
			$key = str_replace('=', '', base64_encode($key));
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
				include("./server/logs.php");
				insertToLog("publisher.php", "Error creating client table: " . $conn->error);
				return;
			}
		}else{
			$sql = "SHOW COLUMNS FROM  client_".$_GET['con'];
			$result = $conn->query($sql);
			$newColumns = "";
			if ($result) {
				$fields = Array();
				while($entry = $result->fetch_assoc()) {
					if($entry["Field"] != "id" && $entry["Field"] != "creation"){
						$fields[] = $entry["Field"];
					}
				}
				foreach ($jsonArray as $key => $value) {
					$key = str_replace('=', '', base64_encode($key));
					$newField = true;
					foreach ($fields as $field) {
						if($field == $key){
							$newField = false;
							break;
						}
					}
					if($newField){
						$newColumns .= "ADD COLUMN {$key} FLOAT(15,7) NOT NULL,";
					}
				}
				if($newColumns != ""){
					$newColumns = substr($newColumns, 0, -1);
					$sql = "ALTER TABLE client_".$_GET['con']." $newColumns;";
					echo $sql;
					if(!$conn->query($query)) {
						header("HTTP/1.1 500 Internal Server Error");
						echo "Error creating new columns.";
						include("./server/logs.php");
						insertToLog("publisher.php", "Error creating new columns: " . $conn->error);
						return;
					}
				}
			}
		}
		
		$sql = "SELECT name, client_name, value, cond, target, time_target FROM alarms".
				" WHERE connection_key LIKE '".$_GET['con']."';";
		$result = $conn->query($sql);
		if ($result) {
			while($entry = $result->fetch_assoc()) {
				foreach ($jsonArray as $key => $value) {
					$key = str_replace('=', '', base64_encode($key));
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
											$msg = "Was detected an alarm in client '".base64_decode($entry["client_name"])."'.\n\n";
											$msg .= "'".base64_decode($entry["value"])."' is equal ".$entry["target"];
											sendMail($conn, base64_decode($entry["name"]), $msg);
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
												$msg = "Was detected an alarm in client '".base64_decode($entry["client_name"])."'.\n\n";
												$msg .= "In the last ".$entry["time_target"]." minutes, '".base64_decode($entry["value"])."' was equal than ".$entry["target"];
												sendMail($conn, base64_decode($entry["name"]), $msg);
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
											$msg = "Was detected an alarm in client '".base64_decode($entry["client_name"])."'.\n\n";
											$msg .= "'".base64_decode($entry["value"])."' is different than ".$entry["target"];
											sendMail($conn, base64_decode($entry["name"]), $msg);
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
												$msg = "Was detected an alarm in client '".base64_decode($entry["client_name"])."'.\n\n";
												$msg .= "In the last ".$entry["time_target"]." minutes, '".base64_decode($entry["value"])."' was different than ".$entry["target"];
												sendMail($conn, base64_decode($entry["name"]), $msg);
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
											$msg = "Was detected an alarm in client '".base64_decode($entry["client_name"])."'.\n\n";
											$msg .= "'".base64_decode($entry["value"])."' is less than ".$entry["target"];
											sendMail($conn, base64_decode($entry["name"]), $msg);
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
												$msg = "Was detected an alarm in client '".base64_decode($entry["client_name"])."'.\n\n";
												$msg .= "In the last ".$entry["time_target"]." minutes, '".base64_decode($entry["value"])."' was less than ".$entry["target"];
												sendMail($conn, base64_decode($entry["name"]), $msg);
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
											$msg = "Was detected an alarm in client '".base64_decode($entry["client_name"])."'.\n\n";
											$msg .= "'".base64_decode($entry["value"])."' is greater than ".$entry["target"];
											sendMail($conn, base64_decode($entry["name"]), $msg);
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
												$msg = "Was detected an alarm in client '".base64_decode($entry["client_name"])."'.\n\n";
												$msg .= "In the last ".$entry["time_target"]." minutes, '".base64_decode($entry["value"])."' was greater than ".$entry["target"];
												sendMail($conn, base64_decode($entry["name"]), $msg);
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
											$msg = "Was detected an alarm in client '".base64_decode($entry["client_name"])."'.\n\n";
											$msg .= "'".base64_decode($entry["value"])."' is less or equal than ".$entry["target"];
											sendMail($conn, base64_decode($entry["name"]), $msg);
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
												$msg = "Was detected an alarm in client '".base64_decode($entry["client_name"])."'.\n\n";
												$msg .= "In the last ".$entry["time_target"]." minutes, '".base64_decode($entry["value"])."' was less or equal than ".$entry["target"];
												sendMail($conn, base64_decode($entry["name"]), $msg);
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
											$msg = "Was detected an alarm in client '".base64_decode($entry["client_name"])."'.\n\n";
											$msg .= "'".base64_decode($entry["value"])."' is greater or equal than ".$entry["target"];
											sendMail($conn, base64_decode($entry["name"]), $msg);
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
												$msg = "Was detected an alarm in client '".base64_decode($entry["client_name"])."'.\n\n";
												$msg .= "In the last ".$entry["time_target"]." minutes, '".base64_decode($entry["value"])."' was greater or equal than ".$entry["target"];
												sendMail($conn, base64_decode($entry["name"]), $msg);
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
		
		//
		$sql = "SELECT * FROM webUsers LIMIT 1";
		$result = $conn->query($sql);
		if ($result && $result->num_rows > 0) {
			$lastRow = $result->fetch_assoc();
			date_default_timezone_set($lastRow["timezoneset"]);
		}
		
		$sql = "INSERT INTO client_".$_GET['con']." (creation, $insert)
				VALUES ('".date('Y-m-d H:i:s')."', $values)";
				
		if ($conn->query($sql) === TRUE) {
			header("HTTP/1.1 200 OK");
			echo "Data saved successfully";
			return;
		} else {
			header("HTTP/1.1 500 Internal Server Error");
			echo "Error inserting data: " . $conn->error;
			include("./server/logs.php");
			insertToLog("publisher.php", "Error inserting data in the table client_".$_GET['con'].": " . $conn->error);
			return;
		}
	}
	
	header("HTTP/1.1 500 Internal Server Error");
	echo "Unknown order.";
	include("./server/logs.php");
	insertToLog("publisher.php", "The get parameters are not right, you need to send the connection key.");
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