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
	
	<script type="text/javascript" src="js/settings.js"></script>

  </head>
  <body>

    <div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
	<?php
		require("./server/UI.php");
		drawMenu("Settings");
	?>
				<br/><br/><br/>
				
				<div class="row">
					<div class="col-xl-12 col-lg-12 col-md-12">
						<div class="col-xl-12 col-lg-12 col-md-12 jumbotron">
							<div class='col-xl-12 col-lg-12 col-md-12'>
							
								<h2>
									<b>Logs </b>
									<a href='#modalDelete' title='Delete Client'  role='button' class='btn' data-toggle='modal'><span class='fa fa-trash-o'></span></a>
								</h2>
								
							</div>
							<br/>
							<div class='col-xl-12 col-lg-12 col-md-12'>
	<?php
		include("./server/jits_logs.php");
		foreach(array_reverse($errors) as $error){
			echo "<p>$error</p>";
		}
	?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<?php
		$bodyModal = "<p>Are you sure you want to clean all the logs?</p>";
		drawModal("modalDelete", "Clean Logs", $bodyModal, "window.location = './server/cleanLogs.php';", "Yes", "No");
	?>
	
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>