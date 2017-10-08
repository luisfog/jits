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
	<?php
		require("./server/UI.php");
		drawMenu("Clients");
	?>
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
						<button id="startScroll" onclick="checkName()">Create Client</button>
						<br/><hr/>
						<h5 id="results"><b>Tasks</b></h5><hr/>
						<p>Starting<span class="ok" id="starting">OK</span></p>
						<p>Check name availability<span class="ok" id="nameAvailable">OK</span></p>
						<p>Generate connection<span class="ok" id="generateConn">OK</span></p>
						<p>Generate AES key<span class="ok" id="generateAES">OK</span></p>
						<p>Register client<span class="ok" id="registerClient">OK</span></p>
						<hr/>
						<h5><b>Data</b></h5><hr/>
						<p>Server:<p>
						<p class="ok" id="connServer">OK<p>
						<p>Connection key:<p>
						<p class="ok" id="connKey">OK<p>
						<p>AES key:<p>
						<p class="ok" id="aesKey">OK<p>
					</div>
				</div>
				
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