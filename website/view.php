<?php
	ini_set('display_errors', '0');
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
	<script type="text/javascript" src="js/view.js"></script>
		
  </head>
  <body>

    <div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
	<?php	
		require("./server/UI.php");
		drawMenu("Views");
		
		$conn = getConnectionFront();
	?>
				<br/><br/><br/>
				
				<div class="row">
					<div class="col-xl-12 col-lg-12 col-md-12">
						<div class="col-xl-12 col-lg-12 col-md-12 jumbotron">
							<div class='col-xl-12 col-lg-12 col-md-12'>
								<h2 style="margin-bottom: 20px;"><b>
	<?php
		$sql = "SELECT name, connection_key, client_name, value, column_name FROM views WHERE name LIKE '".$_REQUEST["view"]."'";
		$result = $conn->query($sql);
		$name = "";
		$connectionKeys = array();
		$clients = array();
		$valuesName = array();
		$columnsNameArrBase64 = array();
		$columnsNameArr = array();
		$valuesBase64 = "";
		$valuesBase64Simple = "";
		$valuesSimple = "";
		$totalPushes = 0;
		$i = 0;

		if ($result->num_rows > 0) {
			while($row = $result->fetch_assoc()) {
				$name = $row["name"];
				$connectionKeys[$i] = $row["connection_key"];
				$clients[$i] = $row["client_name"];
				$valuesName[$i] = $row["value"];
				$columnsNameArr[$i] = $row["column_name"];
				$columnsNameArrBase64[$i++] = base64_decode($row["column_name"]);
			}
		}
		
		echo base64_decode($name)."</b>";//</h2></div>";
		//echo "<div class='col-xl-6 col-lg-6 col-md-6'>";
		
		for($j = 0; $j<$i; $j++){
			$sql = "SELECT COUNT(".$valuesName[$j].") AS num FROM client_".$connectionKeys[$j];
			$result = $conn->query($sql);

			if ($result && $result->num_rows > 0) {
				$numberRows = $result->fetch_assoc();
				$totalPushes += (int)$numberRows["num"];
			}
			
			$valuesBase64 .= base64_decode($clients[$j])."::".base64_decode($valuesName[$j])." (".base64_decode($columnsNameArr[$j])."), ";
			
			$valuesBase64Simple .= base64_decode($columnsNameArr[$j]).", ";
			$valuesSimple .= $columnsNameArr[$j].", ";
		}
		$valuesBase64 = substr($valuesBase64, 0, -2);
		$valuesBase64Simple = substr($valuesBase64Simple, 0, -2);
		$valuesSimple = substr($valuesSimple, 0, -2);
		
		//echo "<p><b>Total pushes:</b> ".$totalPushes."</p>";
		//echo "<a href='#modalDelete'  role='button' class='btn' data-toggle='modal' style='float:right;cursor: pointer;'><span class='fa fa-trash-o'></span></a>";
		//echo "<p><b>Values:</b> ".$valuesBase64."</p>";
		
		echo "<a href='#modalDelete' title='Delete Client'  role='button' class='btn' data-toggle='modal' style='float: right;'><span class='fa fa-trash-o'></span></a>";
		echo "<a href='#modalSettings' title='Client Settings' role='button' class='btn' data-toggle='modal' style='float: right;'><span class='fa fa-sliders'></span></a>";
		echo "<a href='#modalInfo' title='More Information' role='button' class='btn' data-toggle='modal' style='float: right;'><span class='fa fa-info-circle'></span></a>";
			
		if ($totalPushes > 0) {
			echo "<script>var connectionKeysList = '".implode(",", $connectionKeys)."';</script>";
			echo "<script>var columnsNamesList = '".implode(",", $columnsNameArrBase64)."';</script>";
			echo "<script>var valuesList = '".implode(",", $valuesName)."';</script>";
			echo "<script>var valuesFull64 = '".$valuesBase64."';</script>";
			echo "<script>var viewName = '$name';</script>";
			echo "<script>var valuesBase64 = '".$valuesBase64Simple."';</script>";
			echo "<script>var values = '".$valuesSimple."';</script>";
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
		
		$bodyModal = "<p>Are you sure you want to delete this View?</p>";
		drawModal("modalDelete", "Delete View", $bodyModal, "deleteView();", "Yes", "No");
		
		drawInfoViewModal("modalInfo", base64_decode($name), $valuesBase64, $totalPushes);
		
		$sql = "select * from configurations as cf, views as vw ".
				"where type LIKE 'view' AND cf.id_client_view = vw.id AND vw.name LIKE '".$_REQUEST["view"]."'";
		$result = $conn->query($sql);

		if ($result && $result->num_rows > 0) {
			$row = $result->fetch_assoc();
			if($row["yyMin"] == "-1")
				$row["yyMin"] = "";
			if($row["yyMax"] == "-1")
				$row["yyMax"] = "";
			drawSettingsModal($name, "modalExport", "saveData();", $row["dataset"],
							$row["datasetType"], $row["yyMin"], $row["yyMax"], $row["avgOn"], $row["valuesS"], $valuesSimple, $valuesBase64Simple);
		}else{
			drawSettingsModal($name, "modalExport", "saveData();", "", "", "", "", "", $valuesSimple, $valuesSimple, $valuesBase64Simple);
		}
	?>
	
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
	<script src="js/multiple-select.js"></script>
  </body>
</html>