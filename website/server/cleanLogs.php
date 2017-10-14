<?php
	ini_set('display_errors', '0');
	session_start();
	if(!isset($_SESSION['name'])){
		header('Location: ../login.html' );
		return;
	}
	header('Location: ../logs.php');
	
	$lines = file("./jits_logs.php"); 
	$last = sizeof($lines) - 1;

	echo $last;
	$i = 1;
	while($i < $last)
		unset($lines[$i++]);
	unset($lines[$last]);

	$fp = fopen('./jits_logs.php', 'w'); 
	fwrite($fp, implode('', $lines)."?>"); 
	fclose($fp);
?>