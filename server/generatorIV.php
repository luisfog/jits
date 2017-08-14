<?php
	ini_set('display_errors', '0');
	session_start();
	if(!isset($_SESSION['name'])){
		header('Location: ../login.html' );
		return;
	}
	
	$iv = mcrypt_create_iv(
				mcrypt_get_iv_size(
					MCRYPT_RIJNDAEL_128, 
					MCRYPT_MODE_ECB
				), 
				MCRYPT_RAND);
		
	header("HTTP/1.1 201 Created");
	echo base64_encode($iv);
?>