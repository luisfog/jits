<?php
	ini_set('display_errors', '0');
	session_start();
	if(!isset($_SESSION['name'])){
		header('Location: ../login.html' );
		return;
	}
	
	if( isset($_POST['connectionKey']) && isset($_POST['columns']) && isset($_POST['dataLong'])){
		
		$connectionKey = $_POST['connectionKey'];
		$columnsArr = explode(", ", $_POST['columns']);
		$dataLong = $_POST['dataLong'];

		$columns = "";
		for($i = 0; $i<sizeof($columnsArr); $i++){
			$columns .= "ROUND(".$columnsArr[$i].",2) as '".base64_decode($columnsArr[$i])."',";
		}
		$columns = substr($columns, 0, -1);
		
		require("./database.php");
		$conn = getConnectionBack();
		
		date_default_timezone_set("Greenwich");
		$datetimeGMT = date("Y-m-d H:i:s");
		$hours = 0;
		$sql = "SELECT * FROM webUsers LIMIT 1";
		$result = $conn->query($sql);
		if ($result && $result->num_rows > 0) {
			$lastRow = $result->fetch_assoc();
			date_default_timezone_set($lastRow["timezoneset"]);
			$datetime1 =  new DateTime($datetimeGMT);
			$datetime2 =  new DateTime();
			$dif = $datetime1->diff($datetime2);
			$hours = $dif->h;
			$hours = $hours + ($dif->days*24);
			if($datetime1 > $datetime2)
				$hours *= -1;
		}
		
		if($dataLong == "real"){
			$sql = "SELECT DATE_ADD(creation, INTERVAL $hours HOUR) as creation, $columns FROM client_$connectionKey".
				" where creation >= DATE_SUB('$datetimeGMT', INTERVAL 2 MINUTE)";
		}else if($dataLong == "24hours"){
			$sql = "SELECT DATE_ADD(creation, INTERVAL $hours HOUR) as creation, $columns FROM client_$connectionKey".
				" where creation >= DATE_SUB('$datetimeGMT', INTERVAL 1 DAY)";
		}else if($dataLong == "48hours"){
			$sql = "SELECT DATE_ADD(creation, INTERVAL $hours HOUR) as creation, $columns FROM client_$connectionKey".
				" where creation >= DATE_SUB('$datetimeGMT', INTERVAL 2 DAY)";
		}else if($dataLong == "7days"){
			$sql = "SELECT DATE_ADD(creation, INTERVAL $hours HOUR) as creation, $columns FROM client_$connectionKey".
				" where creation >= DATE_SUB('$datetimeGMT', INTERVAL 7 DAY)";
		}else if($dataLong == "60days"){
			$sql = "SELECT DATE_ADD(creation, INTERVAL $hours HOUR) as creation, $columns FROM client_$connectionKey".
				" where creation >= DATE_SUB('$datetimeGMT', INTERVAL 60 DAY)";
		}else if($dataLong == "120days"){
			$sql = "SELECT DATE_ADD(creation, INTERVAL $hours HOUR) as creation, $columns FROM client_$connectionKey".
				" where creation >= DATE_SUB('$datetimeGMT', INTERVAL 120 DAY)";
		}else if($dataLong == "180days"){
			$sql = "SELECT DATE_ADD(creation, INTERVAL $hours HOUR) as creation, $columns FROM client_$connectionKey".
				" where creation >= DATE_SUB('$datetimeGMT', INTERVAL 180 DAY)";
		}else if($dataLong == "all"){
			$sql = "SELECT DATE_ADD(creation, INTERVAL $hours HOUR) as creation, $columns FROM client_$connectionKey";
		}else if(strtotime($dataLong) != FALSE){
			$dataLong = str_replace("\n", " ", $dataLong);
			$sql = "SELECT DATE_ADD(creation, INTERVAL $hours HOUR) as creation, $columns FROM client_$connectionKey".
				" where DATE_ADD(creation, INTERVAL $hours HOUR) > '$dataLong'";
		}else{
			header("HTTP/1.1 500 Internal Server Error");
			echo "'dataLong' parameter has a invalid value.";
			include("./logs.php");
			insertToLog("getData.php", "'dataLong' parameter has a invalid value.");
			return;
		}
		
		$result = $conn->query($sql." ORDER BY creation");
		
		if($result){
			while($entry = $result->fetch_assoc()) {
				$rows[] = $entry;
			}
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
	include("./logs.php");
	insertToLog("getData.php", "Wrong GET request parameters.");
	return;
?>