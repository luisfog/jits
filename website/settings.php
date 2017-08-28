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
		
		include("./server/dbinfo.php");
		
		$conn = new mysqli($databaseHost, $user, $pass, $database);
		if ($conn->connect_error) {
			header("HTTP/1.1 500 Internal Server Error");
			echo "Connection failed: " . $conn->connect_error;
			return;
		}
		
		$clientsKeys = array();
		$clientsName = array();
		$views = array();
		
		$sql = "SELECT * FROM clients ORDER BY name";
		$result = $conn->query($sql);

		if ($result->num_rows > 0) {
			while($row = $result->fetch_assoc()) {
				echo "<li><a href='./client.php?client=".$row["connection_key"]."'>".$row["name"]."</a></li>";
				$clientsKeys[] = $row["connection_key"];
				$clientsName[] = $row["name"];
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
				$views[] = $row["name"];
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
							<li class="active">
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
	
				<div class="login-page">
					<h2>Settings</h2>
					<div class="form">
						<h4>User</h4>
	<?php
		$sql = "SELECT * FROM webUsers LIMIT 1";
		$result = $conn->query($sql);

		if ($result && $result->num_rows > 0) {
			$lastRow = $result->fetch_assoc();
			
			echo "<input id='webUser' type='text' value='".$lastRow["user"]."' style='margin: 10px 0 10px 0;color:#000' disabled />";
			echo "<hr/>";
			echo "<input id='email' type='text' value='".$lastRow["email"]."' style='margin: 10px 0 10px 0;' />";
		}
	?>
						<button onclick="updateMail()">Update eMail</button>
						<hr/>
						<input id="pass" type="password" placeholder="old password" style="margin: 10px 0 10px 0;" />
						<input id="newPass" type="password" placeholder="new password" style="margin: 10px 0 10px 0;" />
						<input id="newPassCon" type="password" placeholder="confirm new password" style="margin: 10px 0 10px 0;" />
						<button onclick="updatePass()">Update Password</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	
	<div class="modal" id="modalOk" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" onclick="this.parentElement.parentElement.parentElement.parentElement.style.display = 'none';">&times;</button>
					<h3 class="modal-title">Updates</h3>
				</div>
				<div class="modal-body" >
					<p>Your settings were updated!</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" onclick="this.parentElement.parentElement.parentElement.parentElement.style.display = 'none';">Ok</button>
				</div>
			</div>
		</div>
	</div>
	
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>