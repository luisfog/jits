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
	
	<script type="text/javascript" src="js/echarts.min.js"></script>
	<script type="text/javascript" src="js/view.js"></script>
		
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
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">Clients<strong class="caret"></strong></a>
								<ul class="dropdown-menu">
	<?php
		echo "<script>var viewName = '".$_REQUEST["view"]."';</script>";
	
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
				echo "<li><a href='./client.php?client=".$row["connection_key"]."'>".$row["name"]."</a></li>";
			}
		}
	?>
								</ul>
							</li>
							<li class="dropdown active">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">Views<strong class="caret"></strong></a>
								<ul class="dropdown-menu" id="viewList">
	<?php
		$sql = "SELECT DISTINCT(name) FROM views ORDER BY name";
		$result = $conn->query($sql);

		if ($result->num_rows > 0) {
			while($row = $result->fetch_assoc()) {
				echo "<li><a href='./view.php?view=".$row["name"]."'>".$row["name"]."</a></li>";
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
		$sql = "SELECT name, connection_key, client_name, value FROM views WHERE name LIKE '".$_REQUEST["view"]."'";
		$result = $conn->query($sql);
		$name = "";
		$connectionKeys = array();
		$clients = array();
		$valuesName = array();
		$values = "";
		$totalPushes = 0;
		$i = 0;

		if ($result->num_rows > 0) {
			while($row = $result->fetch_assoc()) {
				$name = $row["name"];
				$connectionKeys[$i] = $row["connection_key"];
				$clients[$i] = $row["client_name"];
				$valuesName[$i++] = $row["value"];
			}
		}
		
		echo $name."</b></h2></div>";
		echo "<div class='col-xl-6 col-lg-6 col-md-6'>";
		
		for($j = 0; $j<$i; $j++){
			$sql = "SELECT COUNT(".$valuesName[$j].") AS num FROM client_".$connectionKeys[$j];
			$result = $conn->query($sql);

			if ($result && $result->num_rows > 0) {
				$numberRows = $result->fetch_assoc();
				$totalPushes += (int)$numberRows["num"];
			}
			
			$values .= $clients[$j]."::".$valuesName[$j].", ";
		}
		$values = substr($values, 0, -2);
		
		echo "<p><b>Total pushes:</b> ".$totalPushes."</p>";
		echo "<a onclick='document.getElementById(\"modalDelete\").style.display = \"block\";' title='Delete' style='float:right;cursor: pointer;'><span class='fa fa-trash-o'></span></a>";
		echo "<p><b>Values:</b> ".$values."</p>";
		
		if ($totalPushes > 0) {
			echo "<script>var connectionKeysList = '".implode(",", $connectionKeys)."';</script>";
			echo "<script>var clientsNamesList = '".implode(",", $clients)."';</script>";
			echo "<script>var valuesList = '".implode(",", $valuesName)."';</script>";
			echo "<script>var valuesNamesList = '$values';</script>";
			echo "<script>var viewName = '$name';</script>";
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
					<button id="modalYesButton" type="button" class="btn btn-export" onclick="deleteView()" >Yes</button>
					<button type="button" class="btn btn-default" onclick="this.parentElement.parentElement.parentElement.parentElement.style.display = 'none';">No</button>
				</div>
			</div>
		</div>
	</div>
	
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>