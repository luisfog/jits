<?php
	//ini_set('display_errors', '0');
	session_start();
	if(!isset($_SESSION['name'])){
		header('Location: ./login.html' );
		return;
	}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
	<link rel="icon" href="./img/favicon-cloud.ico">
	<title>JITS - JSON IoT Server</title>
		
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="css/multiple-select.css" rel="stylesheet">
	
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.bundle.min.js"></script>
	<script type="text/javascript" src="js/echarts.min.js"></script>
	<script type="text/javascript" src="js/client.js"></script>
		
  </head>
  <body>

    <div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
	<?php
		echo "<script>var conKey = '".$_REQUEST["client"]."';</script>";
	
		require("./server/UI.php");
		drawMenu("Clients");
		
		$conn = getConnectionFront();
	?>
				<br/><br/><br/>
				
				<div class="row">
					<div class="col-xl-12 col-lg-12 col-md-12">
						<div class="col-xl-12 col-lg-12 col-md-12 jumbotron">
							<div class='col-xl-12 col-lg-12 col-md-12'>
								<h2 style="margin-bottom: 20px;"><b>
	<?php
		$sql = "SELECT name, creation, connection_key, aes_key, aes_iv FROM clients WHERE connection_key LIKE '".$_REQUEST["client"]."'";
		$result = $conn->query($sql);

		if ($result->num_rows > 0) {
			$client = $result->fetch_assoc();
			
			$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			$actual_link = substr($actual_link, 0, strrpos($actual_link, "/"))."/publisher.php";
			
			echo base64_decode($client["name"])."</b> ";//</h2><br/></div>";
			//echo "<div class='col-xl-12 col-lg-12 col-md-12'>";
			
			$name = base64_decode($client["name"]);
			$created = $client["creation"];
			$server = $actual_link;
			$connKey = $client["connection_key"];
			$aesKey = $client["aes_key"];
		}
		
		$sql = "SELECT COUNT(*) AS num FROM client_".$_REQUEST["client"];
		$result = $conn->query($sql);
		
		$valuesBase64 = "";
		$totalPushes = "";
		$lastPush = "";

		if ($result && $result->num_rows > 0) {
			
			$numberRows = $result->fetch_assoc();
			//echo "<p><b>Total pushes:</b> ".$numberRows["num"]."</p>";
			$totalPushes = $numberRows["num"];
			
			$sql = "SELECT * FROM client_".$_REQUEST["client"]." ORDER BY creation DESC LIMIT 1";
			$result = $conn->query($sql);
			$lastRow = $result->fetch_assoc();
			
			//echo "<p><b>Last push:</b> ".$lastRow['creation']."</p>";
			$lastPush = $lastRow['creation'];
			
			$values = "";
			foreach ($lastRow as $key => $value) {
				if($key <> "id" && $key <> "creation"){
					$values .= "{$key}, ";
					$key = base64_decode($key);
					$valuesBase64 .= "{$key}, ";
				}
			}
			$values = substr($values, 0, -2);
			$valuesBase64 = substr($valuesBase64, 0, -2);
			
			echo "<a href='#modalDelete' title='Delete Client'  role='button' class='btn' data-toggle='modal' style='float: right;'><span class='fa fa-trash-o'></span></a>";
			echo "<a href='#modalSettings' title='Client Settings' role='button' class='btn' data-toggle='modal' style='float: right;'><span class='fa fa-sliders'></span></a>";
			echo "<a href='#modalInfo' title='More Information' role='button' class='btn' data-toggle='modal' style='float: right;'><span class='fa fa-info-circle'></span></a>";
			
			echo "<script>var valuesBase64 = '".$valuesBase64."';</script>";
			echo "<script>var values = '".$values."';</script>";
			echo "<script>var clientName = '".$client["name"]."';</script>";
			echo "<script>window.onload = function(){";
			echo "		$('#selectValues').multipleSelect();";
			echo "		initChart();";
			echo "		getData();";
			echo "	};</script>";
	?>
								</h2>
							</div>
						</div>
					</div>
				</div>
				
				<div class="row">
					<div class="col-xl-12 col-lg-12 col-md-12" style="margin: 10px 0 10px 0;" >
						<select id="dataLong" style="width:100%;padding:14px;" onchange="getData()">
							<option value="real">Real-Time (1 second)</option>
							<option value="24hours">Last 24 hours</option>
							<option value="48hours">Last 48 hours</option>
							<option value="7days">Last 7 days</option>
							<option value="60days">Last 60 days</option>
							<option value="120days">Last 120 days</option>
							<option value="180days">Last 180 days</option>
							<option value="all">All data</option>
						</select>
					</div>
				</div>
				
				<div class="row">
					<div class="col-xl-12 col-lg-12 col-md-12">
						<div class="col-xl-12 col-lg-12 col-md-12 thumbnail">
							<div class="col-12" style="margin: 10px 0 10px 0;" align="center">
								<img id="loading" src="./img/ajax-loader.gif" style="visibility: hidden;" />
							</div>
							<div class="col-xl-12 col-lg-12 col-md-12">
								<div id="chart" style=" height: 500px;"></div>
							</div>
						</div>
					</div>
				</div>
	<?php
		}else{
			echo "<a href='#modalInfo' title='More Information' role='button' class='btn' data-toggle='modal'><span class='fa fa-info-circle'></span></a>";
			echo "<a href='#modalDelete' title='Delete Client'  role='button' class='btn' data-toggle='modal'><span class='fa fa-trash-o'></span></a>";
		}
	?>
			</div>
		</div>
	</div>
	
	
	<?php
		$bodyModal = "<p>Please select the type of export:</p>".
					"<label><input type=\"radio\" name=\"typeExport\" value=\"csv\" checked> CSV</label><br/>".
					"<label><input type=\"radio\" name=\"typeExport\" value=\"tsv\"> TSV</label><br>".
					"<label><input type=\"radio\" name=\"typeExport\" value=\"json\"> JSON</label><br>";
		drawModal("modalExport", "Export Data", $bodyModal, "exportData();", "Export", "Cancel");
		
		$bodyModal = "<p>Are you sure you want to delete this Client?</p>";
		drawModal("modalDelete", "Delete Client", $bodyModal, "deleteClient();", "Yes", "No");
		
		drawInfoModal("modalInfo", $name, $created, $server, $connKey, $aesKey, $valuesBase64, $totalPushes, $lastPush);
		
		if($valuesBase64 != ""){
			$sql = "select * from configurations as cf, clients as cl ".
					"where cf.type LIKE 'client' AND cf.id_client_view = cl.id AND cl.connection_key LIKE '".$_REQUEST["client"]."'";
			$result = $conn->query($sql);

			if ($result && $result->num_rows > 0) {
				$row = $result->fetch_assoc();
				if($row["yyMin"] == "-1")
					$row["yyMin"] = "";
				if($row["yyMax"] == "-1")
					$row["yyMax"] = "";
				drawSettingsModal($name, "modalExport", "saveData();", $row["dataset"],
								$row["datasetType"], $row["yyMin"], $row["yyMax"], $row["avgOn"], $row["valuesS"], $values, $valuesBase64);
			}else{
				drawSettingsModal($name, "modalExport", "saveData();", "", "", "", "", "", $values, $values, $valuesBase64);
			}
		}
	?>
	
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
	<script src="js/multiple-select.js"></script>
  </body>
</html>