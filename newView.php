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
	
	<script type="text/javascript" src="js/createView.js"></script>

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
								<ul class="dropdown-menu" id="clientList">
	<?php
		
		include("./server/dbinfo.php");

		$conn = new mysqli($databaseHost, $user, $pass, $database);
		if ($conn->connect_error) {
			header("HTTP/1.1 500 Internal Server Error");
			echo "Connection failed: " . $conn->connect_error;
			return;
		}
		
		$sql = "SELECT * FROM clients ORDER BY name";
		$result = $conn->query($sql);
		$optionClients = "";

		if ($result->num_rows > 0) {
			while($row = $result->fetch_assoc()) {
				echo "<li><a href='./client.php?client=".$row["connection_key"]."'>".$row["name"]."</a></li>";
				$optionClients .= "<option value='".$row["connection_key"]."'>".$row["name"]."</option>";
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
				echo "<li><a href='./view.php?view=".$row["name"]."'>".$row["name"]."</a></li>";
			}
		}
	?>
								</ul>
							</li>
							<li>
								<a href="./alarms.php">Alarms</a>
							</li>
							<li class="dropdown active">
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
				
				<div class="row login-page">
					<h2>Create View</h2>
					<div class="form">
						<input id="name" type="text" placeholder="Name" style="margin: 10px 0 10px 0;" />
						<hr/>
						<select id="client" style="margin: 10px 0 10px 0;" onChange="changeValues();">
							<option value="-" selected>Select a client</option>
	<?php
		echo $optionClients;
	?>
						</select>
						<select id="value" style="margin: 10px 0 10px 0;">
							<option value="-" selected>Select a value</option>
						</select>
						<button onclick="addValue()">Add Value</button>
						<br/><br/>
						<table id="values">
							<tr class="header">
								<th style="width: 45%">Client</th>
								<th style="width: 40%">Value</th>
								<th style="width: 15%"> </th>
							</tr>
						</table>
						<hr/>
						<button onclick="checkName()">Create View</button>
						<hr/>
						<h5><b>Tasks</b></h5><hr/>
						<p>Check name availability<span class="ok" id="nameAvailable">OK</span></p>
						<p>Register View<span class="ok" id="registerView">OK</span></p>
					</div>
				</div>
				
			</div>
		</div>
	</div>
	
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>