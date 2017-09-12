<?php
	function insertToLog($file, $error){
		$maxLogs = 1000;
		
		$lines = file("jits_logs.php"); 
		$last = sizeof($lines) - 1 ;
		
		$i = 1;
		while($i <= $last-$maxLogs)
			unset($lines[$i++]);
		unset($lines[$last]);
		
		$fp = fopen('jits_logs', 'w'); 
		fwrite($fp, implode('', $lines)."\$errors[] = \"".date("Y-m-d H:i:s")." - $file.php - $error\";\n?>"); 
		fclose($fp); 
	}
	
?>