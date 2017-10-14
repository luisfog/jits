<?php
	function insertToLogRoot($file, $error){
		$maxLogs = 1000;
		
		$lines = file("./server/jits_logs.php"); 
		$last = sizeof($lines) - 1 ;
		
		$i = 1;
		while($i <= $last-$maxLogs)
			unset($lines[$i++]);
		unset($lines[$last]);
		
		$fp = fopen('./server/jits_logs.php', 'w'); 
		fwrite($fp, implode('', $lines)."\$errors[] = \"".date("Y-m-d H:i:s")." - $file - $error\";\n?>"); 
		fclose($fp); 
	}
	
	function insertToLog($file, $error){
		$maxLogs = 1000;
		
		$lines = file("jits_logs.php"); 
		$last = sizeof($lines) - 1 ;
		
		$i = 1;
		while($i <= $last-$maxLogs)
			unset($lines[$i++]);
		unset($lines[$last]);
		
		$fp = fopen('jits_logs.php', 'w'); 
		fwrite($fp, implode('', $lines)."\$errors[] = \"".date("Y-m-d H:i:s")." - $file - $error\";\n?>"); 
		fclose($fp); 
	}
	
?>