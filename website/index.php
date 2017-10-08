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
	<?php
		require("./server/UI.php");
		if($init){
			drawMenu("Home");
			
			$conn = getConnectionFront();
			
			$clientsKeys = array();
			$clientsName = array();
			$created = array();
			$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			$actual_link = substr($actual_link, 0, strrpos($actual_link, "/"))."/publisher.php";
			$server = $actual_link;
			$connKey = array();
			$aesKey = array();
			$views = array();
			
			$sql = "SELECT * FROM clients ORDER BY name";
			$result = $conn->query($sql);

			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					$clientsKeys[] = $row["connection_key"];
					$clientsName[] = base64_decode($row["name"]);
					$created[] = $row["creation"];
					$connKey[] = $row["connection_key"];
					$aesKey[] = $row["aes_key"];
				}
			}
			$sql = "SELECT DISTINCT(name) FROM views ORDER BY name";
			$result = $conn->query($sql);

			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					$views[] = $row["name"];
				}
			}
		}else{
			drawMenu("Init");
		}
	?>
				<br/><br/><br/>
				
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
			echo "<h3>".base64_decode($view)."</h3>";
				
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
								
								echo "<script>dataArr[$i].push({value:".round(($lastRow[$value[$j]]*100/$entry["value"]),2).", name:'".base64_decode($clientsNameView[$j])."::".base64_decode($value[$j])."'});</script>";
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
			
			$valuesBase64 = "";
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
						$valuesBase64 .= base64_decode($key).", ";
					}
				}
				$valuesRound = substr($valuesRound, 0, -1);
				$valuesBase64 = substr($valuesBase64, 0, -2);
				
				$sql = "SELECT $valuesRound FROM client_$clientKey WHERE creation > DATE_SUB(NOW(), INTERVAL 24 HOUR);";
				$result = $conn->query($sql);
				
				if ($result) {
					while($entry = $result->fetch_assoc()) {
						foreach ($values as &$value) {
							if($entry[$value] != null){
								if($data == false)
									echo "<script>dataArr[$i] = [];</script>";
								
								echo "<script>dataArr[$i].push({value:".round(($valuesNumbers[$value]*100/$entry[$value]),2).", name:'".base64_decode($value)."'});</script>";
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
			
			echo "<a href='#modalInfor-$clientKey' title='More Information' role='button' class='btn' data-toggle='modal'<script >+Info</a>";
			echo "<input type='button' value='View' class='btn btn-primary' onclick='window.location = \"./client.php?client=$clientKey\"' />";
			drawInfoModal("modalInfor-$clientKey", $clientsName[$i], $created[$i], $server, $connKey[$i], $aesKey[$i], $valuesBase64);
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
						<select id="timezone" style="margin: 10px 0 10px 0;" >
							<option value="selectTime">Select a timezone</option>
	<?php
		function formatOffset($offset) {
				$hours = $offset / 3600;
				$remainder = $offset % 3600;
				$sign = $hours > 0 ? '+' : '-';
				$hour = (int) abs($hours);
				$minutes = (int) abs($remainder / 60);

				if ($hour == 0 AND $minutes == 0) {
					$sign = ' ';
				}
				return $sign . str_pad($hour, 2, '0', STR_PAD_LEFT) .':'. str_pad($minutes,2, '0');

		}

		$utc = new DateTimeZone('UTC');
		$dt = new DateTime('now', $utc);

		foreach(DateTimeZone::listIdentifiers() as $tz) {
			$current_tz = new DateTimeZone($tz);
			$offset =  $current_tz->getOffset($dt);
			$transition =  $current_tz->getTransitions($dt->getTimestamp(), $dt->getTimestamp());
			$abbr = $transition[0]['abbr'];

			echo '<option value="' .$tz. '">' .$tz. ' [' .$abbr. ' '. formatOffset($offset). ']</option>';
		}
	?>
						</select>
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
						<p>Create configuration table<span class="ok" id="createConfig">OK</span></p>
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