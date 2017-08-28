<?php
	ini_set('display_errors', '0');
	
	$init = false;
	if (file_exists("./server/dbinfo.php"))
		$init = true;
	
	if($init){
		session_start();
		if(!isset($_SESSION['name'])){
			header('Location: ./login.html' );
			return;
		}
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
	
	<?php
		if ($init){
	?>
		<script type="text/javascript" src="js/index.js"></script>
	<?php
		}else{
	?>
		<script type="text/javascript" src="js/init.js"></script>
	<?php
		}
	?>

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
							
	<?php
		if ($init){
	?>
							<li class="active">
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
	<?php
		}else{
	?>
							<li class="active">
								<a href="">Let's start</a>
							</li>
						</ul>
	<?php
		}
	?>
						
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
	<?php
		if($init){
	?>
				<div class="row">
					<div class='col-12' style="padding:15px;">
						<h3 style="font-weight: 900;color:#003e7d;">Views</h3>
					</div>
				</div>
				<div class="row">
	<?php
		
		$i = 0;
		foreach ($views as &$view) {
			$sql = "SELECT * FROM views WHERE name LIKE '$view'";
			$result = $conn->query($sql);
			$value = array();
			$clientsKeysView = array();
			$clientsNameView = array();
			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					$clientsKeysView[] = $row["connection_key"];
					$clientsNameView[] = $row["client_name"];
					$value[] = $row["value"];
				}
			}
			echo "<div class='col-xl-2 col-lg-3 col-md-4 col-sm-4 col-4'>";
			echo "<div class='thumbnail'><div class='caption'>";
			echo "<h3>$view</h3>";
				
			$j = 0;
			$data = false;
			foreach ($clientsKeysView as &$clientKey) {
				$sql = "SELECT $value[$j] FROM client_$clientKey ORDER BY creation DESC LIMIT 1";
				$result = $conn->query($sql);
				
				if ($result && $result->num_rows > 0) {
					$lastRow = $result->fetch_assoc();
					
					$sql = "SELECT ROUND(AVG($value[$j]),2) as value FROM client_$clientKey WHERE creation > DATE_SUB(NOW(), INTERVAL 24 HOUR);";
					$result = $conn->query($sql);
					
					if ($result) {
						while($entry = $result->fetch_assoc()) {
							if($entry["value"] != null){
								if($data == false)
									echo "<script>dataArr[$i] = [];</script>";
								
								echo "<script>dataArr[$i].push({value:".round(($lastRow[$value[$j]]*100/$entry["value"]),2).", name:'".$clientsNameView[$j]."::".$value[$j]."'});</script>";
								$data = true;
							}
						}
					}
				}
				$j++;
			}
			
			if($data == true){
				echo "Last 24 hour deviation";
				echo "<div id='chart_v_$i' style='width: 100%; height: 200px;'></div>";
				
				echo "<script>createChart('chart_v_$i', dataArr[$i]);</script>";			
					
			}else{
				echo "<div style='width: 100%; height: 200px;'><br/>No data (at least in the last 24 hours)<br/><br/></div>";
			}
			
			echo "<p><input type='button' value='View' class='btn btn-primary' onclick='window.location = \"./view.php?view=$view\"' />";
			//echo "<a id='modal-723263' href='#modal-container-723263' role='button' class='btn' data-toggle='modal'<script >+Info</a></p>";
			echo "</div></div></div>";
			$i++;
		}
	?>
					
				</div>
				
				<div class="row">
					<div class='col-12' style="padding:15px;">
						<h3 style="font-weight: 900;color:#003e7d;">Clients</h3>
					</div>
				</div>
				<div class="row">
	<?php
		
		$i = 0;
		foreach ($clientsKeys as &$clientKey) {
			
			$sql = "SELECT * FROM client_$clientKey ORDER BY creation DESC LIMIT 1";
			$result = $conn->query($sql);
			
			echo "<div class='col-xl-2 col-lg-3 col-md-4 col-sm-4 col-4'>";
			echo "<div class='thumbnail'><div class='caption'>";
			echo "<h3>".$clientsName[$i]."</h3>";
			
			$data = false;
			if ($result && $result->num_rows > 0) {
				$lastRow = $result->fetch_assoc();
				
				$values = array();
				$valuesRound = "";
				$valuesNumbers[] = array();
				foreach ($lastRow as $key => $value) {
					if($key <> "id" && $key <> "creation"){
						$values[] = $key;
						$valuesRound .= "ROUND(AVG($key),2) as $key,";
						$valuesNumbers[$key] = $value;
					}
				}
				$valuesRound = substr($valuesRound, 0, -1);
				
				$sql = "SELECT $valuesRound FROM client_$clientKey WHERE creation > DATE_SUB(NOW(), INTERVAL 24 HOUR);";
				$result = $conn->query($sql);
				
				if ($result) {
					while($entry = $result->fetch_assoc()) {
						foreach ($values as &$value) {
							if($entry[$value] != null){
								if($data == false)
									echo "<script>dataArr[$i] = [];</script>";
								
								echo "<script>dataArr[$i].push({value:".round(($valuesNumbers[$value]*100/$entry[$value]),2).", name:'$value'});</script>";
								$data = true;
							}else{
								break;
							}
						}
					}
				}
			}
			
			if($data == true){
				echo "Last 24 hour deviation";
				echo "<div id='chart_c_$i' style='width: 100%; height: 200px;'></div>";
				
				echo "<script>createChart('chart_c_$i', dataArr[$i]);</script>";			
					
			}else{
				echo "<div style='width: 100%; height: 200px;'><br/>No data (at least in the last 24 hours)<br/><br/></div>";
			}
			
			echo "<p><input type='button' value='View' class='btn btn-primary' onclick='window.location = \"./client.php?client=$clientKey\"' />";
			//echo "<a id='modal-723263' href='#modal-container-723263' role='button' class='btn' data-toggle='modal'<script >+Info</a></p>";
			echo "</div></div></div>";
			$i++;
		}
	?>
					
				</div>
				
	<?php
		}else{
	?>
				<div class="login-page">
					<h2>Initial Configuration</h2>
					<div class="form">
						<h4>Your online user</h4>
						<input id="webUser" type="text" placeholder="username" style="margin: 10px 0 10px 0;" />
						<input id="email" type="text" placeholder="email" style="margin: 10px 0 10px 0;" />
						<input id="webPass" type="password" placeholder="password" style="margin: 10px 0 10px 0;" />
						<input id="webPassCon" type="password" placeholder="confirm password" style="margin: 10px 0 10px 0;" />
						<h4>MySQL</h4>
						<select id="mySQLselect" onchange="updateFields(this.value);" style="margin: 10px 0 10px 0;" >
							<option value="new" selected>New database and user</option>
							<option value="old">Existing database and user</option>
						</select>
						<input id="server" type="text" placeholder="MySQL server, e.g. localhost" style="margin: 10px 0 10px 0;" />
						<input id="database" type="text" placeholder="MySQL database, e.g. jitsDB" style="margin: 10px 0 10px 0;" disabled />
						<input id="user" type="text" placeholder="MySQL root username, e.g. root" style="margin: 10px 0 10px 0;" />
						<input id="pass" type="password" placeholder="MySQL root password" style="margin: 10px 0 10px 0;" />
						<button id="startScroll" onclick="createWorld()">Auto-Configuration</button>
						<br/><hr/>
						<h5 id="results"><b>Tasks</b></h5><hr/>
						<p>Starting<span class="ok" id="starting">OK</span></p>
						<p>Create database<span class="ok" id="createDatabase">OK</span></p>
						<p>Create DB user<span class="ok" id="createUser">OK</span></p>
						<p>Give the right permissions<span class="ok" id="givePermissions">OK</span></p>
						<p>Create clients table<span class="ok" id="createClients">OK</span></p>
						<p>Create views table<span class="ok" id="createViews">OK</span></p>
						<p>Create alarms table<span class="ok" id="createAlarms">OK</span></p>
						<p>Create website user<span class="ok" id="createWebUser">OK</span></p>
						<p>Delete init files<span class="ok" id="deleteInits">OK</span></p>
						<p>Refreshing page<span class="ok" id="refresh">10</span></p>
					</div>
				</div>
	<?php
		}
	?>
			</div>
		</div>
	</div>
	
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
	<script>
		$("#startScroll").click(function() {
			$("#starting").css("visibility","visible");
			$('html, body').animate({
				scrollTop: $("#results").offset().top
			}, 2000);
		});
	</script>
  </body>
</html>