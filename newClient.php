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
	
	<script type="text/javascript" src="js/createClient.js"></script>

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

		if ($result->num_rows > 0) {
			while($row = $result->fetch_assoc()) {
				echo "<li><a href='./client.php?client=".$row["connection_key"]."'>".$row["name"]."</a></li>";
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
					<h2>Create Client</h2>
					<div class="form">
						<input id="name" type="text" placeholder="Name" style="margin: 10px 0 10px 0;" />
						<select id="AES" style="margin: 10px 0 10px 0;">
							<option value="128" selected>AES - 128</option>
							<option value="192" >AES - 192</option>
							<option value="256" >AES - 256</option>
						</select>
						<select id="type" style="margin: 10px 0 10px 0;">
							<option value="128" selected>Publisher</option>
							<option value="192" >Subscribe (TODO)</option>
						</select>
						<button onclick="checkName()">Create Client</button>
						<br/><hr/>
						<h5><b>Tasks</b></h5><hr/>
						<p>Check name availability<span class="ok" id="nameAvailable">OK</span></p>
						<p>Generate connection<span class="ok" id="generateConn">OK</span></p>
						<p>Generate AES key<span class="ok" id="generateAES">OK</span></p>
						<p>Generate AES iv<span class="ok" id="generateAESiv">OK</span></p>
						<p>Register client<span class="ok" id="registerClient">OK</span></p>
						<hr/>
						<h5><b>Data</b></h5><hr/>
						<p>Server:<p>
						<p class="ok" id="connServer">OK<p>
						<p>Connection key:<p>
						<p class="ok" id="connKey">OK<p>
						<p>AES key:<p>
						<p class="ok" id="aesKey">OK<p>
						<p>AES iv:<p>
						<p class="ok" id="aesIV">OK<p>
					</div>
				</div>
				
			</div>
		</div>
	</div>
	
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>