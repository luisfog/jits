<?php
	//ini_set('display_errors', '0');
	session_start();
	if(!isset($_SESSION['name'])){
		header('Location: ../login.html' );
		return;
	}
	
	include("./dbinfo.php");

	$conn = new mysqli($databaseHost, $user, $pass, $database);
	if ($conn->connect_error) {
		header("HTTP/1.1 500 Internal Server Error");
		echo "Connection failed: " . $conn->connect_error;
		return;
	}
	
	$sql = "SELECT name, client_name, value, cond, ROUND(target,2) as target, ROUND(time_target,2) as time_target FROM alarms ORDER BY name";
	$result = $conn->query($sql);
	
	$arr = array();
	$i = 0;

	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$arr[$i]["name"] = base64_decode($row["name"]);
			$arr[$i]["client_name"] = base64_decode($row["client_name"]);
			$arr[$i]["value"] = base64_decode($row["value"]);
			
			switch($row["cond"]){
				case "1":
					$arr[$i]["cond"] = "equal";
					break;
				case "2":
					$arr[$i]["cond"] = "not equal";
					break;
				case "3":
					$arr[$i]["cond"] = "less than";
					break;
				case "4":
					$arr[$i]["cond"] = "greater than";
					break;
				case "5":
					$arr[$i]["cond"] = "less or equal";
					break;
				case "6":
					$arr[$i]["cond"] = "greater or equal";
					break;
			}
			
			$arr[$i]["target"] = $row["target"];
			$arr[$i++]["time_target"] = $row["time_target"];
		}
	}
	
	$conn->close();
	
	header("HTTP/1.1 200 OK");
	echo json_encode($arr);
	return;
?>