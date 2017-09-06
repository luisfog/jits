<?php
	//ini_set('display_errors', '0');
	session_start();
	if(!isset($_SESSION['name'])){
		header('Location: ../login.html' );
		return;
	}
	
	if( isset($_POST['connectionKey']) && isset($_POST['columns']) && isset($_POST['dataLong'])){
		
		include("./dbinfo.php");
		
		$connectionKey = $_POST['connectionKey'];
		$columnsArr = explode(", ", $_POST['columns']);
		$dataLong = $_POST['dataLong'];

		$columns = "";
		for($i = 0; $i<sizeof($columnsArr); $i++){
			$columns .= "ROUND(".$columnsArr[$i].",2) as '".base64_decode($columnsArr[$i])."',";
		}
		$columns = substr($columns, 0, -1);
		
		$conn = new mysqli($databaseHost, $user, $pass, $database);
		if ($conn->connect_error) {
			header("HTTP/1.1 500 Internal Server Error");
			echo "Connection failed: " . $conn->connect_error;
			return;
		}
		
		if($dataLong == "real"){
			$sql = "SELECT creation, $columns FROM client_$connectionKey".
				" where creation >= DATE_SUB(NOW(), INTERVAL 2 MINUTE)";
		}else if($dataLong == "24hours"){
			$sql = "SELECT creation, $columns FROM client_$connectionKey".
				" where creation >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
		}else if($dataLong == "48hours"){
			$sql = "SELECT creation, $columns FROM client_$connectionKey".
				" where creation >= DATE_SUB(NOW(), INTERVAL 2 DAY)";
		}else if($dataLong == "7days"){
			$sql = "SELECT creation, $columns FROM client_$connectionKey".
				" where creation >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
		}else if($dataLong == "60days"){
			$sql = "SELECT creation, $columns FROM client_$connectionKey".
				" where creation >= DATE_SUB(NOW(), INTERVAL 60 DAY)";
		}else if($dataLong == "120days"){
			$sql = "SELECT creation, $columns FROM client_$connectionKey".
				" where creation >= DATE_SUB(NOW(), INTERVAL 120 DAY)";
		}else if($dataLong == "180days"){
			$sql = "SELECT creation, $columns FROM client_$connectionKey".
				" where creation >= DATE_SUB(NOW(), INTERVAL 180 DAY)";
		}else if($dataLong == "all"){
			$sql = "SELECT creation, $columns FROM client_$connectionKey";
		}else if(strtotime($dataLong) != FALSE){
			$sql = "SELECT creation, $columns FROM client_$connectionKey".
				" where creation > '$dataLong'";
		}else{
			header("HTTP/1.1 500 Internal Server Error");
			echo "Connection failed: " . $conn->connect_error;
			return;
		}
		
		$result = $conn->query($sql);
		//echo $sql;
		
		if($result){
			while($entry = $result->fetch_assoc()) {
				$rows[] = $entry;
				
				/*foreach ($entry as $key => $value) {
					if($key <> "creation")
						$rows[] = $key => $value;
					else
						$rows[$i] = "\"".$key."\" : \"".$value."\"";
				}
				$i++;*/
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
	return;
?>