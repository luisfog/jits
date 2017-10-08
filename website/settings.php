<?php
	//ini_set('display_errors', '0');
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
		
		$conn = getConnectionFront();
	?>
				
				<br/><br/><br/>
	<?php
		$versionGit = file_get_contents('https://raw.githubusercontent.com/luisfog/jits/master/website/update/version');
	
		$versionServer = "";
		if(file_exists('./update/version')){
			$versionServer = file_get_contents('./update/version');
		}
		
		if(compareVersions($versionGit, $versionServer)){
	?>
				<div class="alert alert-success alert-dismissable" id="errorDIV">
					 
					<button type="button" class="close" data-dismiss="alert" aria-hidden="true">
						×
					</button>
	<?php
		echo "<h4>Version ".$versionGit." available!</h4>";
		echo "<p>Download <b><a href='#modalUpdates' title='Download Updates' role='button' data-toggle='modal'>here</a></b>".
				" or in the update option below.</p>";
		if($versionServer == "")
			echo "<p>At this moment you are not use a GitHub version.</p>";
		else
			echo "<p>Your current version is $versionServer</p>";
	?>
					
				</div>
	<?php
		}
		
		function compareVersions($version1, $version2){
			if($version1 == "")
				return false;
			if($version2 == "")
				return true;
			
			$version1 = explode(".", substr($version1, 1));
			$version2 = explode(".", substr($version2, 1));
			
			$size = min(sizeof($version1), sizeof($version2));
			for($i=0; $i<$size; $i++){
				if($version1[$i] > $version2[$i])
					return true;
				if($version1[$i] < $version2[$i])
					return false;
			}
			if(sizeof($version1) > sizeof($version2))
				return true;
			return false;
		}
	?>
				<div class="login-page">
					<h2>Settings</h2>
					<div class="form">
						<h4>User</h4>
	<?php
		$sql = "SELECT * FROM webUsers LIMIT 1";
		$result = $conn->query($sql);
		
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

		if ($result && $result->num_rows > 0) {
			$lastRow = $result->fetch_assoc();
			
			echo "<input id='webUser' type='text' value='".$lastRow["user"]."' style='margin: 10px 0 10px 0;color:#000' disabled />";
			echo "<hr/>";
			echo "<input id='email' type='text' value='".$lastRow["email"]."' style='margin: 10px 0 10px 0;' />";
	?>
						<button onclick="updateMail()">Update eMail</button>
						<hr/>
						<select id="timezone" style="margin: 10px 0 10px 0;" >
	<?php
			$utc = new DateTimeZone('UTC');
			$dt = new DateTime('now', $utc);

			foreach(DateTimeZone::listIdentifiers() as $tz) {
				$current_tz = new DateTimeZone($tz);
				$offset =  $current_tz->getOffset($dt);
				$transition =  $current_tz->getTransitions($dt->getTimestamp(), $dt->getTimestamp());
				$abbr = $transition[0]['abbr'];

				if($lastRow["timezoneset"] == $tz)
					echo '<option value="' .$tz. '" selected>' .$tz. ' [' .$abbr. ' '. formatOffset($offset). ']</option>';
				else
					echo '<option value="' .$tz. '">' .$tz. ' [' .$abbr. ' '. formatOffset($offset). ']</option>';
			}
	?>
						</select>
						<button onclick="updateTimezone()">Update timezone</button>
						<hr/>
						<input id="pass" type="password" placeholder="old password" style="margin: 10px 0 10px 0;" />
						<input id="newPass" type="password" placeholder="new password" style="margin: 10px 0 10px 0;" />
						<input id="newPassCon" type="password" placeholder="confirm new password" style="margin: 10px 0 10px 0;" />
						<button onclick="updatePass()">Update Password</button>
	<?php
		}
	?>
					</div>
				</div>
				
				<div class="login-page">
					<div class="form">
						<a href="./logs.php" title="Download Libraries"><h4><span class="fa fa-exclamation-triangle"></span> Logs <span class="fa fa-exclamation-triangle"></span></h4></a>
					</div>
				</div>
				
				<div class="login-page">
					<div class="form">
						<h4>Updates</h4>
						<button onclick="$('#modalUpdates').modal('toggle');">Update JITS</button>
					</div>
				</div>
				<br/><br/><br/>
			</div>
		</div>
	</div>
	
	<?php
		$bodyModal = "<p>Your settings were updated!</p>";
		drawOkModal("modalOk", "Updates", $bodyModal, "OK");
		
		$bodyModal = "<p>Are you sure you want to update JITS?</p>";
		$bodyModal .= "<p>This action can take some time.</p>";
		drawModal("modalUpdates", "Update JITS", $bodyModal, "window.location = './updateJITS.php';", "Yes", "No");
	?>
	
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>