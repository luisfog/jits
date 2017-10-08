<?php	
	$url = 'https://github.com/luisfog/jits/archive/master.zip';

	$fh = fopen('master.zip', 'w');

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt($ch, CURLOPT_FILE, $fh); 
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // this will follow redirects
	curl_exec($ch);

	print 'Attempting download of '.$url.'<br />';
	if(curl_error($ch) == '')
		$errors = '<u>none</u>';
	else
		$errors = '<u>'.curl_error($ch).'</u>';
	print 'cURL Errors : '.$errors;

	curl_close($ch);

	fclose($fh);
	
	$zip = new ZipArchive;
	$res = $zip->open('master.zip', ZipArchive::CREATE );
	if ($res === TRUE) {
		echo 'ok';
		$zip->extractTo('updateZIP');
		$zip->close();
	} else {
		echo 'failed, code:' . $res;
	}
	
	$src = "./updateZIP/jits-master/website";
	$dst = "./";
	recurse_copy($src,$dst);
	
	deleteDirectory("./updateZIP");
	unlink("master.zip");
	
	$handle = fopen("./update/deletedFiles.txt", "r");
	if ($handle) {
		while (($line = fgets($handle)) !== false) {
			if($line[strlen($line)-1] == "/")
				deleteDirectory($line);
			else
				unlink($line);
		}
		fclose($handle);
	}
	unlink("./update/deletedFiles.txt");
	
	require("./server/database.php");
	$conn = getConnectionFront();
	
	$handle = fopen("./update/databaseUpdates.txt", "r");
	if ($handle) {
		while (($line = fgets($handle)) !== false) {
			$conn->query($line);
		}
		fclose($handle);
	}
	unlink("./update/databaseUpdates.txt");
	
	function recurse_copy($src,$dst) { 
		$dir = opendir($src); 
		@mkdir($dst); 
		while(false !== ( $file = readdir($dir)) ) { 
			if (( $file != '.' ) && ( $file != '..' )) { 
				if ( is_dir($src . '/' . $file) ) { 
					recurse_copy($src . '/' . $file,$dst . '/' . $file); 
				} 
				else { 
					copy($src . '/' . $file,$dst . '/' . $file); 
				} 
			} 
		} 
		closedir($dir);
	}
	
	function deleteDirectory($dir) {
		if (!file_exists($dir)) {
			return true;
		}

		if (!is_dir($dir)) {
			return unlink($dir);
		}

		foreach (scandir($dir) as $item) {
			if ($item == '.' || $item == '..') {
				continue;
			}

			if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
				return false;
			}

		}

		return rmdir($dir);
	}
	
	header('Location: ./index.php');
	exit;
?>