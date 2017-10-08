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
	<?php	
		require("./server/UI.php");
		drawMenu("Views");
		
		$conn = getConnectionFront();
		
		$sql = "SELECT * FROM clients ORDER BY name";
		$result = $conn->query($sql);
		$optionClients = "";
		
		if ($result->num_rows > 0) {
			while($row = $result->fetch_assoc()) {
				$optionClients .= "<option value='".$row["connection_key"]."'>".base64_decode($row["name"])."</option>";
			}
		}
	?>
				<br/><br/><br/>
				
				<div class="row login-page" style="width: 400px">
					<h2>Create View</h2>
					<div class="form" style="max-width: 400px">
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
						<input id="volumnName" type="text" placeholder="Column Name" style="margin: 10px 0 10px 0;" />
						<button onclick="addValue()">Add Value</button>
						<br/><br/>
						<table id="values">
							<tr class="header">
								<th style="width: 35%">Client</th>
								<th style="width: 35%">Value</th>
								<th style="width: 15%">Name</th>
								<th style="width: 15%"> </th>
							</tr>
						</table>
						<hr/>
						<button id="startScroll" onclick="checkName()">Create View</button>
						<hr/>
						<h5 id="results"><b>Tasks</b></h5><hr/>
						<p>Starting<span class="ok" id="starting">OK</span></p>
						<p>Check name availability<span class="ok" id="nameAvailable">OK</span></p>
						<p>Register View<span class="ok" id="registerView">OK</span></p>
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