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
	
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.bundle.min.js"></script>
	<script type="text/javascript" src="js/echarts.min.js"></script>
	<script type="text/javascript" src="js/client.js"></script>
		
  </head>
  <body>

    <div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
					<div class="navbar-header">
						 
						<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
							 <span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
						</button> <a class="navbar-brand" href="./index.php">JITS IoT</a>
					</div>
					
					<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
						<ul class="nav navbar-nav">
							<li>
								<a href="./index.php">Home</a>
							</li>
							<li class="dropdown active">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">Clients<strong class="caret"></strong></a>
								<ul class="dropdown-menu">
	<?php
		echo "<script>var conKey = '".$_REQUEST["client"]."';</script>";
	
		include("./server/dbinfo.php");

		$conn = new mysqli($databaseHost, $user, $pass, $database);
		if ($conn->connect_error) {
			header("HTTP/1.1 500 Internal Server Error");
			echo "Connection failed: " . $conn->connect_error;
			return;
		}
		
		$sql = "SELECT * FROM clients ORDER BY name";
		$result = $conn->query($sql);

		if ($result->num_rows > 0) {
			while($row = $result->fetch_assoc()) {
				echo "<li><a href='./client.php?client=".$row["connection_key"]."'>".base64_decode($row["name"])."</a></li>";
			}
		}
	?>
								</ul>
							</li>
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">Views<strong class="caret"></strong></a>
								<ul class="dropdown-menu" id="viewList">
	<?php
		$sql = "SELECT DISTINCT(name) FROM views ORDER BY name";
		$result = $conn->query($sql);

		if ($result->num_rows > 0) {
			while($row = $result->fetch_assoc()) {
				echo "<li><a href='./view.php?view=".$row["name"]."'>".base64_decode($row["name"])."</a></li>";
			}
		}
	?>
								</ul>
							</li>
							<li>
								<a href="./alarms.php">Alarms</a>
							</li>
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">New<strong class="caret"></strong></a>
								<ul class="dropdown-menu" id="clientList">
									<li><a href="./newClient.php">New Client</a></li>
									<li><a href="./newView.php">New View</a></li>
								</ul>
							</li>
							<li>
								<a href="./settings.php" title="Settings"><span class="fa fa-sliders"></span></a>
							</li>
							<li>
								<a href="https://github.com/luisfog/jits" target="blank" title="Download Libraries"><span class="fa fa-download"></span></a>
							</li>
							<li>
								<a href="./server/logout.php" title="Logout"><span class="fa fa-sign-out"></span></a>
							</li>
						</ul>
					</div>
					
				</nav>
				
				<br/><br/><br/>
								
				<div class="alert alert-success alert-dismissable" id="errorDIV" style="display: none;">
					 
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">
						×
					</button>
					<h4>
						Alert!
					</h4> <strong>Warning!</strong> Best check yo self, you're not looking too good. <a href="#" class="alert-link">alert link</a>
				</div>
				
				<div class="row">
					<div class="col-xl-12 col-lg-12 col-md-12">
						<div class="col-xl-12 col-lg-12 col-md-12 jumbotron">
							<div class='col-xl-12 col-lg-12 col-md-12'>
								<h2><b>
	<?php
		$sql = "SELECT name, creation, connection_key, aes_key, aes_iv FROM clients WHERE connection_key LIKE '".$_REQUEST["client"]."'";
		$result = $conn->query($sql);

		if ($result->num_rows > 0) {
			$client = $result->fetch_assoc();
			
			$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			$actual_link = substr($actual_link, 0, strrpos($actual_link, "/"))."/publisher.php";
			
			echo base64_decode($client["name"])."</b></h2></div>";
			echo "<div class='col-xl-6 col-lg-6 col-md-6'>";
			echo "<p><b>Server:</b> ".$actual_link."</p>";
			echo "<p><b>Connection key:</b> ".$client["connection_key"]."</p>";
			echo "<p><b>AES key:</b> ".$client["aes_key"]."</p>";
			echo "<br/>";
			echo "</div><div class='col-xl-6 col-lg-6 col-md-6'>";
		}
		
		$sql = "SELECT COUNT(*) AS num FROM client_".$_REQUEST["client"];
		$result = $conn->query($sql);

		if ($result && $result->num_rows > 0) {
			echo "<p><b>Created on:</b> ".$client["creation"]."</p>";
			
			$numberRows = $result->fetch_assoc();
			echo "<p><b>Total pushes:</b> ".$numberRows["num"]."</p>";
			
			$sql = "SELECT * FROM client_".$_REQUEST["client"]." ORDER BY creation DESC LIMIT 1";
			$result = $conn->query($sql);
			$lastRow = $result->fetch_assoc();
			
			echo "<p><b>Last push:</b> ".$lastRow['creation']."</p>";
			
			$values = "";
			$valuesBase64 = "";
			foreach ($lastRow as $key => $value) {
				if($key <> "id" && $key <> "creation"){
					$values .= "{$key}, ";
					$key = base64_decode($key);
					$valuesBase64 .= "{$key}, ";
				}
			}
			$values = substr($values, 0, -2);
			$valuesBase64 = substr($valuesBase64, 0, -2);
			
			echo "<a onclick='document.getElementById(\"modalDelete\").style.display = \"block\";' title='Delete' style='float:right;cursor: pointer;'><span class='fa fa-trash-o'></span></a>";
			echo "<p><b>Values:</b> ".$valuesBase64."</p>";
	
			echo "<script>var valuesBase64 = '".$valuesBase64."';</script>";
			echo "<script>var values = '".$values."';</script>";
			echo "<script>var clientName = '".$client["name"]."';</script>";
			echo "<script>window.onload = function(){";
			echo "		initChart();";
			echo "		getData();";
			echo "	};</script>";
	?>
							</div>
						</div>
					</div>
				</div>
				
				<div class="row">
					<div class="col-xl-12 col-lg-12 col-md-12">
						<div class="col-xl-12 col-lg-12 col-md-12 thumbnail">
							<div class="col-xl-6 col-lg-6 col-md-6" style="margin: 10px 0 10px 0;" >
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
							<div class="col-xl-6 col-lg-6 col-md-6" style="margin: 10px 0 10px 0;" >
								<input type="button" style="width:100%;height:47px;" value="Export" class="btn-export" onclick="document.getElementById('modalExport').style.display = 'block';" />
							</div>
						</div>
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
			echo "<a onclick='document.getElementById(\"modalDelete\").style.display = \"block\";' title='Delete' style='float:right;cursor: pointer;'><span class='fa fa-trash-o'></span></a>";
			echo "<p><b>Created on:</b> ".$client["creation"]."</p>";
		}
	?>
			</div>
		</div>
	</div>
	
	<div class="modal" id="modalExport" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" onclick="this.parentElement.parentElement.parentElement.parentElement.style.display = 'none';">&times;</button>
					<h3 class="modal-title">Export Data</h3>
				</div>
				<div class="modal-body" id="modalCloseText" >
					<p>Please select the type of export:</p>
					
					<label><input type="radio" name="typeExport" value="csv" checked> CSV</label><br/>
					<label><input type="radio" name="typeExport" value="tsv"> TSV</label><br>
					<label><input type="radio" name="typeExport" value="json"> JSON</label><br>
				</div>
				<div class="modal-footer">
					<button id="modalYesButton" type="button" class="btn btn-export" onclick="exportData()" >Export</button>
					<button type="button" class="btn btn-default" onclick="this.parentElement.parentElement.parentElement.parentElement.style.display = 'none';">Cancel</button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal" id="modalDelete" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" onclick="this.parentElement.parentElement.parentElement.parentElement.style.display = 'none';">&times;</button>
					<h3 class="modal-title">Delete View</h3>
				</div>
				<div class="modal-body" id="modalCloseText" >
					<p>Are you sure you want to delete this View?</p>
				</div>
				<div class="modal-footer">
					<button id="modalYesButton" type="button" class="btn btn-export" onclick="deleteClient()" >Yes</button>
					<button type="button" class="btn btn-default" onclick="this.parentElement.parentElement.parentElement.parentElement.style.display = 'none';">No</button>
				</div>
			</div>
		</div>
	</div>
	
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>