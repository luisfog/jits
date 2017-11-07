<?php	
	function drawMenu($selectedMenu){
?>
		<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
			<div class="navbar-header">
				 
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
					 <span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
				</button> <a class="navbar-brand" href="./index.php">JITS IoT</a>
			</div>
			
			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<ul class="nav navbar-nav">
<?php
	if($selectedMenu == "Init"){
?>
				<li class="active">
					<a href="">Let's start</a>
				</li></ul></div></nav>
<?php
		return;
	}
	
	require("./server/database.php");
	$conn = getConnectionFront();
		
	/*if($selectedMenu == "Home")
		echo "<li class=\"active\">";
	else
		echo "<li>";

						<a href="./index.php">Home</a>
					</li>*/

	if($selectedMenu == "Clients")
		echo "<li class=\"dropdown active\">";
	else
		echo "<li class=\"dropdown\">";
?>
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">Clients<strong class="caret"></strong></a>
						<ul class="dropdown-menu" id="clientList">
<?php
	$sql = "SELECT * FROM clients ORDER BY name";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			echo "<li><a href='./client.php?client=".$row["connection_key"]."'>".base64_decode($row["name"])."</a></li>";
		}
	}
?>
							<li><a href="./newClient.php" style="margin-top:15px;font-weight: bold;"><span class="fa fa-plus"></span> Add Client</a></li>
						</ul>
					</li>
<?php
	if($selectedMenu == "Views")
		echo "<li class=\"dropdown active\">";
	else
		echo "<li class=\"dropdown\">";
?>
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">Views<strong class="caret"></strong></a>
						<ul class="dropdown-menu" id="viewList">
<?php
	$sql = "SELECT DISTINCT(name) FROM views ORDER BY name";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			echo "<li><a href='./view.php?view=".$row["name"]."'>".base64_decode($row["name"])."</a></li>";
		}
	}
?>
							<li><a href="./newView.php" style="margin-top:15px;font-weight: bold;"><span class="fa fa-plus"></span> Add View</a></li>
						</ul>
					</li>

<?php
	if($selectedMenu == "Alarms")
		echo "<li class=\"active\">";
	else
		echo "<li>";
?>
						<a href="./alarms.php">Alarms</a>
					</li>
<?php
	if($selectedMenu == "Settings")
		echo "<li class=\"active\">";
	else
		echo "<li>";
?>
						<a href="./settings.php" title="Settings"><span class="fa fa-sliders"></span></a>
					</li>
<?php
	if($selectedMenu == "Libraries")
		echo "<li class=\"active\">";
	else
		echo "<li>";
?>
						<a href="https://github.com/luisfog/jits/tree/master/libraries" target="blank" title="Download Libraries"><span class="fa fa-download"></span></a>
					</li>
					<li>
						<a href="./server/logout.php" title="Logout"><span class="fa fa-sign-out"></span></a>
					</li>
				</ul>
			</div>
			
		</nav>

<?php
	}
	
	function drawModal($id, $title, $body, $okFunction, $okText, $cancelText){
		echo "<div id=\"$id\" class=\"modal fade\" role=\"dialog\">";
			echo "<div class=\"modal-dialog\">";
				echo "<div class=\"modal-content\">";
					echo "<div class=\"modal-header\">";
						echo "<button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>";
						echo "<h3 class=\"modal-title\">$title</h3>";
					echo "</div>";
					echo "<div class=\"modal-body\" >";
						echo "<p>$body</p>";
					echo "</div>";
					echo "<div class=\"modal-footer\">";
						if($okFunction != null)
							echo "<button id=\"modalYesButton\" type=\"button\" class=\"btn btn-export\" onclick=\"$okFunction\" >$okText</button>";
						
						echo "<button type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">$cancelText</button>";
					echo "</div>";
				echo "</div>";
			echo "</div>";
		echo "</div>";
	}
	
	function drawOkModal($id, $title, $body, $cancelText){
		drawModal($id, $title, $body, null, null, $cancelText);
	}
	
	function drawInfoModal($modalName, $name, $created, $server, $connKey, $aesKey, $valuesBase64, $totalPushes, $lastPush){
		$body = "<div>".
					"<p><b>Created on:</b> $created</p>".
					"<p><b>Server:</b> $server</p>".
					"<p><b>Connection key:</b> $connKey</p>".
					"<p><b>AES key:</b> $aesKey</p>".
					"<p><b>Values:</b> $valuesBase64</p>";
					
		if($totalPushes != null)
			$body .= "<p><b>Total pushes:</b> $totalPushes</p>";
		if($lastPush != null)
			$body .= "<p><b>Last push:</b> $lastPush</p>";
		
		$body .= "</div>";
		drawModal($modalName, "\"".$name."\" Information", $body, null, null, "OK");
	}
	
	function drawInfoViewModal($modalName, $name, $valuesBase64, $totalPushes){
		$body = "<div>".
					"<p><b>Values:</b> $valuesBase64</p>".
					"<p><b>Total pushes:</b> $totalPushes</p>".
				"</div>";
		drawModal($modalName, "\"".$name."\" Information", $body, null, null, "OK");
	}
	
	function drawSettingsModal($name, $modalExportTarget, $okFunction, $dataSelect,
								$dataTypeSelect, $yyMin, $yyMax, $avgSelect,
								$dltSelect, $valuesSel, $values, $valuesBase64){
					
		$body = "<div>".
				"<p><b>Dataset</b></p>".
				"<select id='dataSelect' style='width:100%;padding:14px;'>";
		if($dataSelect == "real")
			$body .= "<option value='real' selected>Real-Time (1 second)</option>";
		else
			$body .= "<option value='real'>Real-Time (1 second)</option>";
		if($dataSelect == "24hours")
			$body .= "<option value='24hours' selected>Last 24 hours</option>";
		else
			$body .= "<option value='24hours'>Last 24 hours</option>";
		if($dataSelect == "48hours")
			$body .= "<option value='48hours' selected>Last 48 hours</option>";
		else
			$body .= "<option value='48hours'>Last 48 hours</option>";
		if($dataSelect == "7days")
			$body .= "<option value='7days' selected>Last 7 days</option>";
		else
			$body .= "<option value='7days'>Last 7 days</option>";
		if($dataSelect == "60days")
			$body .= "<option value='60days' selected>Last 60 days</option>";
		else
			$body .= "<option value='60days'>Last 60 days</option>";
		if($dataSelect == "120days")
			$body .= "<option value='120days' selected>Last 120 days</option>";
		else
			$body .= "<option value='120days'>Last 120 days</option>";
		if($dataSelect == "180days")
			$body .= "<option value='180days' selected>Last 180 days</option>";
		else
			$body .= "<option value='180days'>Last 180 days</option>";
		if($dataSelect == "all")
			$body .= "<option value='all' selected>All data</option>";
		else
			$body .= "<option value='all'>All data</option>";
					
		$body .= "</select><hr/><p><b>Dataset type</b></p>".
					"<select id='dataTypeSelect' style='width:100%;padding:14px;'>";
					
		if($dataTypeSelect == "lineT")
			$body .= "<option value='lineT' selected>Line Tiled</option>";
		else
			$body .= "<option value='lineT'>Line Tiled</option>";
		if($dataTypeSelect == "lineS")
			$body .= "<option value='lineS' selected>Line Stack</option>";
		else
			$body .= "<option value='lineS'>Line Stack</option>";
		if($dataTypeSelect == "barT")
			$body .= "<option value='barT' selected>Bar Tiled</option>";
		else
			$body .= "<option value='barT'>Bar Tiled</option>";
		if($dataTypeSelect == "barS")
			$body .= "<option value='barS' selected>Bar Stack</option>";
		else
			$body .= "<option value='barS'>Bar Stack</option>";
		
		$body .= "</select><hr/><p><b>Values</b></p>";
		
		$body .= "<select id='selectValues' multiple='multiple'>";
		
		$values64 = explode(",", $valuesBase64);
		$values = explode(",", $values);
		$valuesSel = explode(",", $valuesSel);
		
		for ($i=0; $i<sizeof($values64); $i++) {
			if(in_array($values[$i], $valuesSel))
				$body .= "<option value='".$values[$i]."' selected>".$values64[$i]."</option>";
			else
				$body .= "<option value='".$values[$i]."'>".$values64[$i]."</option>";
		}
		
		$body .= "</select><hr/><p><b>yy range</b></p>";
		$body .= "<input id='yyMin' class='inputText' type='text' placeholder='Min Auto' value='$yyMin' />";
		$body .= "<input id='yyMax' class='inputText' style='float: right;' type='text' placeholder='Max Auto' value='$yyMax' />";
		
		$body .= "<hr/><p><b>Average line</b></p><select id='avgSelect' style='width:100%;padding:14px;'>";
		
		if($avgSelect == "1")
			$body .= "<option value='avgOn' selected>Show Average line</option>";
		else
			$body .= "<option value='avgOn'>Show Average line</option>";
		if($avgSelect == "0")
			$body .= "<option value='avgOff' selected>Hide Average line</option>";
		else
			$body .= "<option value='avgOff'>Hide Average line</option>";
		
		$body .= "</select><hr/><p><b>Delete data </b></p><select id='deleteSelect' style='width:100%;padding:14px;'>";
		
		if($dltSelect == "never")
			$body .= "<option value='never' selected>Never delete the data</option>";
		else
			$body .= "<option value='never'>Never delete the data</option>";
		if($dltSelect == "year")
			$body .= "<option value='year' selected>(READ THE NOTE) Delete data with more than 1 year</option>";
		else
			$body .= "<option value='year'>(READ THE NOTE) Delete data with more than 1 year</option>";
		if($dltSelect == "month")
			$body .= "<option value='month' selected>(READ THE NOTE) Delete data with more than 1 month</option>";
		else
			$body .= "<option value='month'>(READ THE NOTE) Delete data with more than 1 month</option>";
		if($dltSelect == "week")
			$body .= "<option value='week' selected>(READ THE NOTE) Delete data with more than 1 week</option>";
		else
			$body .= "<option value='week'>(READ THE NOTE) Delete data with more than 1 week</option>";
		
		$body .= "</select><br/><br/><b style='color:red'>NOTE:</b> this is a permanent delete without recovery!";
		$body .= "<hr/><p><b>Export Data</b></p>".
				"<input type='button' data-dismiss=\"modal\" style='width:100%;height:47px;' value='Export' class='btn-export' data-toggle='modal' data-target='#$modalExportTarget' />".
				"</div>";
		
		drawModal("modalSettings", "\"".$name."\" Settings", $body, $okFunction, "Save", "Cancel");
	}
?>

				