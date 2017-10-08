<?php
	//ini_set('display_errors', '0');
	session_start();
	if(!isset($_SESSION['name'])){
		header('Location: ../login.html' );
		return;
	}
	
	if( isset($_POST['type']) && isset($_POST['target']) &&
		isset($_POST['dataSelect']) && isset($_POST['dataTypeSelect']) &&
		isset($_POST['valuesS']) && isset($_POST['yyMin']) &&
		isset($_POST['yyMax']) && isset($_POST['avgSelect']) ){
		
		$type = $_POST['type'];
		$target = $_POST['target'];
		$dataSelect = $_POST['dataSelect'];
		$dataTypeSelect = $_POST['dataTypeSelect'];
		$values = $_POST['valuesS'];
		$yyMin = $_POST['yyMin'];
		if($yyMin == "")
			$yyMin = -1;
		$yyMax = $_POST['yyMax'];
		if($yyMax == "")
			$yyMax = -1;
		$avgSelect = $_POST['avgSelect'];
		if($avgSelect == "avgOn")
			$avgSelect = "1";
		else
			$avgSelect = "0";
		
		require("./database.php");
		$conn = getConnectionBack();
		
		if($type == "client")
			$sql = "select cf.id from configurations as cf, clients as cl ".
					"where cf.id_client_view = cl.id AND cl.connection_key LIKE '$target'";
		else
			$sql = "select cf.id from configurations as cf, views as vw ".
					"where cf.id_client_view = vw.id AND vw.name LIKE '$target'";
			
		$result = $conn->query($sql);

		if ($result && $result->num_rows > 0) {
			$row = $result->fetch_assoc();
			$sql = "UPDATE configurations SET dataset='$dataSelect', datasetType='$dataTypeSelect',
					valuesS='$values', yyMin=$yyMin, yyMax=$yyMax, avgOn=$avgSelect
					WHERE id='".$row["id"]."'";
			
			if ($conn->query($sql) === TRUE) {
				$conn->close();
				header("HTTP/1.1 200 OK");
				echo "Settings updated";
				return;
			} else {
				$conn->close();
				header("HTTP/1.1 500 Internal Server Error");
				echo "Error updating.";
				include("./logs.php");
				insertToLog("updateSettings.php", "Error updating: " . $conn->error);
				return;
			}
		}else{
			if($type == "client"){
				$sql = "INSERT INTO configurations (type, id_client_view, dataset, datasetType, ".
													"valuesS, yyMin, yyMax, avgOn) ".
						"VALUES ('$type', (SELECT id FROM clients where connection_key LIKE '$target'), ".
								"'$dataSelect', '$dataTypeSelect', '$values', $yyMin, $yyMax, $avgSelect)";
			}else{
				$sql = "INSERT INTO configurations (type, id_client_view, dataset, datasetType, ".
													"valuesS, yyMin, yyMax, avgOn) ".
						"VALUES ('$type', (SELECT id FROM views where name LIKE '$target' LIMIT 1), ".
								"'$dataSelect', '$dataTypeSelect', '$values', $yyMin, $yyMax, $avgSelect)";
			}
			
			if ($conn->query($sql) === TRUE) {
				header("HTTP/1.1 200 OK");
				echo "Settings updated";
				$conn->close();
				return;
			} else {
				header("HTTP/1.1 500 Internal Server Error");
				echo "Error updating.";
				include("./logs.php");
				insertToLog("updateSettings.php", "Error updating: " . $conn->error . " :: " . $sql);
				$conn->close();
				return;
			}
		}
	}
	
	header("HTTP/1.1 500 Internal Server Error");
	echo "Error updating.";
	include("./logs.php");
	insertToLog("updateSettings.php", "Error updating: " . $conn->error);
	
	return;
?>