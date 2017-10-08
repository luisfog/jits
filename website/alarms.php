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
	<script type="text/javascript" src="js/echarts.min.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.bundle.min.js"></script>
	<script type="text/javascript" src="js/alarm.js"></script>

  </head>
  <body>

    <div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
	<?php
		require("./server/UI.php");
		drawMenu("Alarms");
	?>
				<br/><br/><br/>
								
				<div class="row">
					<div class="col-xl-12 col-lg-12 col-md-12">
						<div class="col-xl-12 col-lg-12 col-md-12 thumbnail">
							<div class="col-xl-6 col-lg-6 col-md-6" style="margin: 10px 0 10px 0;" >
								<input id="name" type="text" style="width:100%;height:47px;padding-left:20px;" placeholder="Name" class="btn-export" />
							</div>
							<div class="col-xl-6 col-lg-6 col-md-6" style="margin: 10px 0 10px 0;" >
								<select id="clientsList" style="width:100%;padding:14px;" onchange="changeValues();">
									<option value="-">Select a client</option>
	<?php
		echo $clientOptions;
	?>
								</select>
							</div>
							<div class="col-xl-6 col-lg-6 col-md-6" style="margin: 10px 0 10px 0;" >
								<select id="valuesList" style="width:100%;padding:14px;">
									<option value="-">Select a value</option>
								</select>
							</div>
							<div class="col-xl-6 col-lg-6 col-md-6" style="margin: 10px 0 10px 0;" >
								<select id="conditionsList" style="width:100%;padding:14px;">
									<option value="-">Select a condition</option>
									<option value="equal">Equal to: ==</option>
									<option value="notEqual">Not equal to: !=</option>
									<option value="less">Less than: &lt;</option>
									<option value="greater">Greater than: &gt;</option>
									<option value="lessEqual">Less than or equal to: &lt;=</option>
									<option value="greaterEqual">Greater than or equal to: &gt;=</option>
								</select>
							</div>
							<div class="col-xl-6 col-lg-6 col-md-6" style="margin: 10px 0 10px 0;" >
								<input id="target" type="number" style="width:100%;height:47px;padding-left:20px;" placeholder="target (float)" class="btn-export" />
							</div>
							<div class="col-xl-6 col-lg-6 col-md-6" style="margin: 10px 0 10px 0;" >
								<select id="timeExecutionList" style="width:100%;padding:14px;" onchange="changeTimeExecutionList()">
									<option value="instantaneous">Instantaneous (real-time)</option>
									<option value="after">After period of</option>
								</select>
							</div>
							<div class="col-xl-6 col-lg-6 col-md-6" style="margin: 10px 0 10px 0;" >
								<input id="timeExecution" type="number" style="width:100%;height:47px;padding-left:20px;" placeholder="number of minutes" class="btn-export" disabled />
							</div>
							<div class="col-xl-6 col-lg-6 col-md-6" style="margin: 10px 0 10px 0;" >
								<button class="buttonCreate" onclick="checkName()">Create Alarm</button>
							</div>
							<div class="col-xl-12 col-lg-12 col-md-12" style="margin: 10px 0 10px 0;" >
								<p><b>NOTE 1 - </b><i>target</i> is the number that activates the alarm.<p>
								<p><b>NOTE 2 - </b><i>Instantaneous (real-time)</i> means that the alarm will be executed when the condition occurs.<p>
								<p><b>NOTE 3 - </b><i>After period of</i> means that the alarm will be executed when the condition is true during a period bigger than the specified.<p>
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div id="alarms" class="col-xl-12 col-lg-12 col-md-12">
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<?php
		$bodyModal = "<p>Are you sure you want to delete this Alarm?</p>";
		drawModal("modalDelete", "Delete Alarm", $bodyModal, "deleteClient();", "Yes", "No");
	?>
	
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>