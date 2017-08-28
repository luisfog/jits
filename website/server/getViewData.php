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
		include("./dbinfo.php");
		
		$name = $_POST['name'];
		$connectionKeys = explode(",", $_POST['connectionKeys']);
		$columnsNames = explode(",", $_POST['columnsNames']);
		$values = explode(",", $_POST['values']);
		$dataLong = $_POST['dataLong'];
		
		$conn = new mysqli($databaseHost, $user, $pass, $database);
		if ($conn->connect_error) {
			header("HTTP/1.1 500 Internal Server Error");
			echo "Connection failed: " . $conn->connect_error;
			return;
		}
		
		$rows = array();
		$rowIndex = 0;
		
		for($i = 0; $i<sizeof($connectionKeys); $i++){
			if($dataLong == "real"){
				$sql = "SELECT creation, ROUND(".$values[$i].",2) as ".$values[$i]." FROM client_".$connectionKeys[$i].
					" where creation >= DATE_SUB(NOW(), INTERVAL 2 MINUTE)";
			}else if($dataLong == "24hours"){
				$sql = "SELECT creation, ROUND(".$values[$i].",2) as ".$values[$i]." FROM client_".$connectionKeys[$i].
					" where creation >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
			}else if($dataLong == "48hours"){
				$sql = "SELECT creation, ROUND(".$values[$i].",2) as ".$values[$i]." FROM client_".$connectionKeys[$i].
					" where creation >= DATE_SUB(NOW(), INTERVAL 2 DAY)";
			}else if($dataLong == "7days"){
				$sql = "SELECT creation, ROUND(".$values[$i].",2) as ".$values[$i]." FROM client_".$connectionKeys[$i].
					" where creation >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
			}else if($dataLong == "60days"){
				$sql = "SELECT creation, ROUND(".$values[$i].",2) as ".$values[$i]." FROM client_".$connectionKeys[$i].
					" where creation >= DATE_SUB(NOW(), INTERVAL 60 DAY)";
			}else if($dataLong == "120days"){
				$sql = "SELECT creation, ROUND(".$values[$i].",2) as ".$values[$i]." FROM client_".$connectionKeys[$i].
					" where creation >= DATE_SUB(NOW(), INTERVAL 120 DAY)";
			}else if($dataLong == "180days"){
				$sql = "SELECT creation, ROUND(".$values[$i].",2) as ".$values[$i]." FROM client_".$connectionKeys[$i].
					" where creation >= DATE_SUB(NOW(), INTERVAL 180 DAY)";
			}else if($dataLong == "all"){
				$sql = "SELECT creation, ROUND(".$values[$i].",2) as ".$values[$i]." FROM client_".$connectionKeys[$i];
			}else if(strtotime($dataLong) != FALSE){
				$sql = "SELECT creation, ROUND(".$values[$i].",2) as ".$values[$i]." FROM client_".$connectionKeys[$i].
					" where creation > '$dataLong'";
			}else{
				header("HTTP/1.1 500 Internal Server Error");
				echo "Connection failed: " . $conn->connect_error;
				return;
			};
			
			$result = $conn->query($sql);
		
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
	return;
?>