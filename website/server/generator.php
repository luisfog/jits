<?php
	ini_set('display_errors', '0');
	session_start();
	if(!isset($_SESSION['name'])){
		header('Location: ../login.html' );
		return;
	}
	
	if( isset($_POST['length']) && isset($_POST['name']) ){
		$rawKey = hash('ripemd160', $_POST['name'].microtime()).hash('ripemd160', $_POST['name'].microtime());
		$rawKey = substr($rawKey, 0, $_POST['length']);
		
		header("HTTP/1.1 200 OK");
		echo $rawKey;
		return;
	}

	header("HTTP/1.1 500 Internal Server Error");
	echo "Unknown inputs.";
	return;
?>