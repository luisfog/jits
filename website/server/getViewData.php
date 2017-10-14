<?php
	ini_set('display_errors', '0');
	session_start();
	if(!isset($_SESSION['name'])){
		header('Location: ../login.html' );
		return;
	}
	
	function cmp($a, $b)
	{
		if ($a['creation'] == $b['creation']) {
			return 0;
		}
		return ($a['creation'] < $b['creation']) ? -1 : 1;
	}

	if( isset($_POST['name']) && isset($_POST['connectionKeys']) && isset($_POST['columnsNames']) && isset($_POST['values']) && isset($_POST['dataLong'])){
		
		$name = $_POST['name'];
		$connectionKeys = explode(",", $_POST['connectionKeys']);
		$columnsNames = explode(",", $_POST['columnsNames']);
		$values = explode(",", $_POST['values']);
		$dataLong = $_POST['dataLong'];
		
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
		
		$rows = array();
		$rowIndex = 0;
		
		for($i = 0; $i<sizeof($connectionKeys); $i++){
			if($dataLong == "real"){
				$sql = "SELECT DATE_ADD(creation, INTERVAL $hours HOUR) as creation, ROUND(".$values[$i].",2) as ".$values[$i]." FROM client_".$connectionKeys[$i].
					" where creation >= DATE_SUB('$datetimeGMT', INTERVAL 2 MINUTE)";
			}else if($dataLong == "24hours"){
				$sql = "SELECT DATE_ADD(creation, INTERVAL $hours HOUR) as creation, ROUND(".$values[$i].",2) as ".$values[$i]." FROM client_".$connectionKeys[$i].
					" where creation >= DATE_SUB('$datetimeGMT', INTERVAL 1 DAY)";
			}else if($dataLong == "48hours"){
				$sql = "SELECT DATE_ADD(creation, INTERVAL $hours HOUR) as creation, ROUND(".$values[$i].",2) as ".$values[$i]." FROM client_".$connectionKeys[$i].
					" where creation >= DATE_SUB('$datetimeGMT', INTERVAL 2 DAY)";
			}else if($dataLong == "7days"){
				$sql = "SELECT DATE_ADD(creation, INTERVAL $hours HOUR) as creation, ROUND(".$values[$i].",2) as ".$values[$i]." FROM client_".$connectionKeys[$i].
					" where creation >= DATE_SUB('$datetimeGMT', INTERVAL 7 DAY)";
			}else if($dataLong == "60days"){
				$sql = "SELECT DATE_ADD(creation, INTERVAL $hours HOUR) as creation, ROUND(".$values[$i].",2) as ".$values[$i]." FROM client_".$connectionKeys[$i].
					" where creation >= DATE_SUB('$datetimeGMT', INTERVAL 60 DAY)";
			}else if($dataLong == "120days"){
				$sql = "SELECT DATE_ADD(creation, INTERVAL $hours HOUR) as creation, ROUND(".$values[$i].",2) as ".$values[$i]." FROM client_".$connectionKeys[$i].
					" where creation >= DATE_SUB('$datetimeGMT', INTERVAL 120 DAY)";
			}else if($dataLong == "180days"){
				$sql = "SELECT DATE_ADD(creation, INTERVAL $hours HOUR) as creation, ROUND(".$values[$i].",2) as ".$values[$i]." FROM client_".$connectionKeys[$i].
					" where creation >= DATE_SUB('$datetimeGMT', INTERVAL 180 DAY)";
			}else if($dataLong == "all"){
				$sql = "SELECT DATE_ADD(creation, INTERVAL $hours HOUR) as creation, ROUND(".$values[$i].",2) as ".$values[$i]." FROM client_".$connectionKeys[$i];
			}else if(strtotime($dataLong) != FALSE){
				$sql = "SELECT DATE_ADD(creation, INTERVAL $hours HOUR) as creation, ROUND(".$values[$i].",2) as ".$values[$i]." FROM client_".$connectionKeys[$i].
					" where DATE_ADD(creation, INTERVAL $hours HOUR) > '$dataLong'";
			}else{
				header("HTTP/1.1 500 Internal Server Error");
				echo "'dataLong' parameter has a invalid value.";
				include("./logs.php");
				insertToLog("getViewData.php", "'dataLong' parameter has a invalid value.");
				return;
			};
			
			$result = $conn->query($sql." ORDER BY creation");
		
			if($result){
				while($entry = $result->fetch_assoc()) {
					$rows[$rowIndex]["creation"] = $entry["creation"];
					$rows[$rowIndex++][$columnsNames[$i]] = $entry[$values[$i]];
				}
			}
		}
		
		usort($rows, "cmp");
		
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
	insertToLog("getViewData.php", "Wrong GET request parameters.");
	return;
?>